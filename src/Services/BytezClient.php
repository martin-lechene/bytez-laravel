<?php

namespace Bytez\LaravelBytez\Services;

use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Exceptions\BytezRateLimitException;
use Bytez\LaravelBytez\Exceptions\BytezTimeoutException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;

class BytezClient
{
    protected Client $httpClient;
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryAttempts;
    protected int $retryDelay;
    protected ?CacheRepository $cache;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.bytez.com',
        int $timeout = 30,
        int $retryAttempts = 3,
        int $retryDelay = 1000,
        ?CacheRepository $cache = null
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;
        $this->retryDelay = $retryDelay;
        $this->cache = $cache;

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a request to the Bytez API with retry logic.
     */
    public function request(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $providerKey = null,
        bool $useCache = false,
        ?int $cacheTtl = null
    ): array {
        $cacheKey = null;
        if ($useCache && $this->cache && $method === 'GET') {
            $cacheKey = $this->getCacheKey($method, $endpoint, $data, $providerKey);
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $headers = [];
        if ($providerKey) {
            $headers['provider-key'] = $providerKey;
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $options = ['headers' => $headers];
                
                if ($method === 'GET') {
                    $options['query'] = $data;
                } else {
                    $options['json'] = $data;
                }

                $response = $this->httpClient->request($method, $endpoint, $options);
                $result = json_decode($response->getBody()->getContents(), true);

                if ($useCache && $this->cache && $cacheKey && $cacheTtl) {
                    $this->cache->put($cacheKey, $result, $cacheTtl);
                }

                return $result;
            } catch (RequestException $e) {
                $lastException = $e;
                $statusCode = $e->getResponse()?->getStatusCode();

                // Don't retry on client errors (4xx) except rate limits
                if ($statusCode >= 400 && $statusCode < 500 && $statusCode !== 429) {
                    throw $this->handleException($e);
                }

                // Retry on rate limits and server errors
                if ($statusCode === 429) {
                    $retryAfter = $e->getResponse()?->getHeader('Retry-After')[0] ?? $this->retryDelay;
                    usleep($retryAfter * 1000);
                } elseif ($statusCode >= 500) {
                    $attempt++;
                    if ($attempt < $this->retryAttempts) {
                        usleep($this->retryDelay * 1000 * $attempt);
                        continue;
                    }
                }

                throw $this->handleException($e);
            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    usleep($this->retryDelay * 1000 * $attempt);
                    continue;
                }

                throw new BytezException(
                    'Request failed after ' . $this->retryAttempts . ' attempts: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw $this->handleException($lastException);
    }

    /**
     * Get a cached response or make a request.
     */
    public function get(string $endpoint, array $query = [], ?string $providerKey = null, ?int $cacheTtl = 60): array
    {
        return $this->request('GET', $endpoint, $query, $providerKey, true, $cacheTtl);
    }

    /**
     * Post a request.
     */
    public function post(string $endpoint, array $data = [], ?string $providerKey = null): array
    {
        return $this->request('POST', $endpoint, $data, $providerKey);
    }

    /**
     * Handle exceptions and convert them to Bytez exceptions.
     */
    protected function handleException(\Exception $e): BytezException
    {
        if ($e instanceof RequestException && $e->hasResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($body, true);

            $message = $errorData['error']['message'] ?? $errorData['message'] ?? $e->getMessage();

            if ($statusCode === 429) {
                return new BytezRateLimitException($message, $statusCode, $e);
            }

            if ($statusCode >= 500) {
                return new BytezException('Server error: ' . $message, $statusCode, $e);
            }

            return new BytezException($message, $statusCode, $e);
        }

        if (Str::contains($e->getMessage(), 'timeout')) {
            return new BytezTimeoutException('Request timeout: ' . $e->getMessage(), 0, $e);
        }

        return new BytezException($e->getMessage(), 0, $e);
    }

    /**
     * Generate a cache key for the request.
     */
    protected function getCacheKey(string $method, string $endpoint, array $data, ?string $providerKey): string
    {
        return 'bytez:' . md5($method . $endpoint . serialize($data) . ($providerKey ?? ''));
    }

    /**
     * Clear cache for a specific pattern.
     */
    public function clearCache(?string $pattern = null): void
    {
        if (!$this->cache) {
            return;
        }

        if ($pattern) {
            // Implementation depends on cache driver
            // This is a simplified version
            $this->cache->forget($pattern);
        } else {
            // Clear all bytez cache
            // Note: This requires cache tags support (Redis, Memcached)
            if (method_exists($this->cache->getStore(), 'tags')) {
                $this->cache->tags(['bytez'])->flush();
            }
        }
    }
}
