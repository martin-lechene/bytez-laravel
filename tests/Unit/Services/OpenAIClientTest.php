<?php

namespace Bytez\LaravelBytez\Tests\Unit\Services;

use Bytez\LaravelBytez\Services\OpenAIClient;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class OpenAIClientTest extends TestCase
{
    public function test_openai_client_can_be_instantiated()
    {
        $client = new OpenAIClient('test-key');
        $this->assertInstanceOf(OpenAIClient::class, $client);
    }

    public function test_openai_client_uses_correct_base_url()
    {
        $client = new OpenAIClient('test-key', 'https://api.bytez.com');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $httpClient = $property->getValue($client);
        $this->assertStringContainsString('/models/v2/openai/v1', $httpClient->getConfig('base_uri'));
    }

    public function test_openai_client_can_make_chat_completions_request()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Hello!'
                        ]
                    ]
                ]
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new OpenAIClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->request('POST', '/chat/completions', [
            'model' => 'test-model',
            'messages' => [['role' => 'user', 'content' => 'Hello']]
        ]);

        $this->assertArrayHasKey('choices', $result);
        $this->assertEquals('Hello!', $result['choices'][0]['message']['content']);
    }

    public function test_openai_client_supports_provider_key()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['choices' => []])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new OpenAIClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $result = $client->request('POST', '/chat/completions', [
            'model' => 'openai/gpt-4',
            'messages' => [['role' => 'user', 'content' => 'Hello']]
        ], 'provider-key-123');

        $this->assertIsArray($result);
    }
}
