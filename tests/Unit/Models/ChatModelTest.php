<?php

namespace Bytez\LaravelBytez\Tests\Unit\Models;

use Bytez\LaravelBytez\Models\ChatModel;
use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ChatModelTest extends TestCase
{
    public function test_chat_model_can_send_message()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Hello!'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $chatModel = new ChatModel($client, 'openai-community/gpt-2');
        $result = $chatModel->message('Hello');

        $this->assertArrayHasKey('output', $result);
        $this->assertEquals('Hello!', $result['output']);
    }

    public function test_chat_model_can_handle_conversation()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Response'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $chatModel = new ChatModel($client, 'openai-community/gpt-2');
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ];

        $result = $chatModel->conversation($messages);
        $this->assertArrayHasKey('output', $result);
    }

    public function test_chat_model_supports_provider_key()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Response'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $chatModel = new ChatModel($client, 'openai/gpt-4');
        $chatModel->withProviderKey('openai-key-123');
        
        $result = $chatModel->message('Hello');
        $this->assertArrayHasKey('output', $result);
    }
}
