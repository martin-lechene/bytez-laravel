<?php

namespace Bytez\LaravelBytez\Services;

use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Exceptions\BytezRateLimitException;
use Bytez\LaravelBytez\Exceptions\BytezTimeoutException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class OpenAIClient
{
    protected Client $httpClient;
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryAttempts;
    protected int $retryDelay;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.bytez.com',
        int $timeout = 30,
        int $retryAttempts = 3,
        int $retryDelay = 1000
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;
        $this->retryDelay = $retryDelay;

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl . '/models/v2/openai/v1',
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a request to the OpenAI-compatible endpoint.
     */
    public function request(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $providerKey = null,
        bool $stream = false
    ) {
        $headers = [];
        if ($providerKey) {
            $headers['provider-key'] = $providerKey;
        }

        if ($stream) {
            $headers['Accept'] = 'text/event-stream';
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $options = [
                    'headers' => $headers,
                    'json' => $data,
                ];

                if ($stream) {
                    $options['stream'] = true;
                }

                $response = $this->httpClient->request($method, $endpoint, $options);

                if ($stream) {
                    return $this->handleStreamResponse($response);
                }

                return json_decode($response->getBody()->getContents(), true);
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
     * Handle streaming response.
     */
    protected function handleStreamResponse($response): \Generator
    {
        $body = $response->getBody();
        $buffer = '';
        
        while (!$body->eof()) {
            $chunk = $body->read(8192);
            if ($chunk === '') {
                break;
            }
            
            $buffer .= $chunk;
            $lines = explode("\n", $buffer);
            
            // Keep the last incomplete line in buffer
            $buffer = array_pop($lines);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line)) {
                    continue;
                }

                if (Str::startsWith($line, 'data: ')) {
                    $data = substr($line, 6);
                    
                    if (trim($data) === '[DONE]') {
                        return;
                    }

                    $decoded = json_decode($data, true);
                    if ($decoded !== null) {
                        yield $decoded;
                    }
                }
            }
        }
        
        // Process remaining buffer
        if (!empty(trim($buffer))) {
            if (Str::startsWith($buffer, 'data: ')) {
                $data = substr($buffer, 6);
                if (trim($data) !== '[DONE]') {
                    $decoded = json_decode($data, true);
                    if ($decoded !== null) {
                        yield $decoded;
                    }
                }
            }
        }
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
}
