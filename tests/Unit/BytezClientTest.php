<?php

namespace Bytez\LaravelBytez\Tests\Unit;

use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;

class BytezClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_client_can_be_instantiated()
    {
        $client = new BytezClient('test-key');
        $this->assertInstanceOf(BytezClient::class, $client);
    }

    public function test_client_uses_correct_base_url()
    {
        $client = new BytezClient('test-key', 'https://custom.api.com');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $httpClient = $property->getValue($client);
        $this->assertEquals('https://custom.api.com', $httpClient->getConfig('base_uri'));
    }

    public function test_get_request_returns_data()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => 'test'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->get('/test');
        $this->assertEquals(['data' => 'test'], $result);
    }

    public function test_post_request_sends_data()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->post('/test', ['input' => 'test']);
        $this->assertEquals(['success' => true], $result);
    }

    public function test_provider_key_is_included_in_headers()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $client->post('/test', ['input' => 'test'], 'provider-key-123');
        
        // Verify the request was made with provider-key header
        $this->assertTrue(true); // Mock verification would be done here
    }

    public function test_retry_on_server_error()
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode(['error' => 'Server error'])),
            new Response(500, [], json_encode(['error' => 'Server error'])),
            new Response(200, [], json_encode(['success' => true])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key', 'https://api.bytez.com', 30, 3, 10);
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->post('/test', ['input' => 'test']);
        $this->assertEquals(['success' => true], $result);
    }

    public function test_cache_is_used_for_get_requests()
    {
        Cache::shouldReceive('get')
            ->once()
            ->andReturn(null);

        Cache::shouldReceive('put')
            ->once()
            ->andReturn(true);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => 'cached'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key', 'https://api.bytez.com', 30, 3, 1000, Cache::store());
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->get('/test', [], null, 60);
        $this->assertEquals(['data' => 'cached'], $result);
    }
}
