<?php

namespace Bytez\LaravelBytez\Tests\Unit\Builders;

use Bytez\LaravelBytez\Builders\ChatCompletionsBuilder;
use Bytez\LaravelBytez\Services\OpenAIClient;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ChatCompletionsBuilderTest extends TestCase
{
    public function test_chat_completions_builder_can_set_messages()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['choices' => [['message' => ['content' => 'Hello!']]]])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new OpenAIClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $builder = new ChatCompletionsBuilder($client, 'test-model');
        $builder->system('You are helpful')
            ->user('Hello')
            ->assistant('Hi there')
            ->user('How are you?');

        $result = $builder->create();
        $this->assertArrayHasKey('choices', $result);
    }

    public function test_chat_completions_builder_can_set_parameters()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['choices' => [['message' => ['content' => 'Response']]]])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new OpenAIClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $builder = new ChatCompletionsBuilder($client, 'test-model');
        $builder->user('Hello')
            ->maxTokens(150)
            ->temperature(0.7)
            ->topP(0.9)
            ->frequencyPenalty(0.5)
            ->presencePenalty(0.5)
            ->logprobs(5);

        $result = $builder->create();
        $this->assertArrayHasKey('choices', $result);
    }

    public function test_chat_completions_builder_validates_messages()
    {
        $client = new OpenAIClient('test-key');
        $builder = new ChatCompletionsBuilder($client, 'test-model');

        $this->expectException(\InvalidArgumentException::class);
        $builder->messages([
            ['role' => 'invalid', 'content' => 'test']
        ]);
    }

    public function test_chat_completions_builder_requires_at_least_one_message()
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

        $builder = new ChatCompletionsBuilder($client, 'test-model');

        $this->expectException(\InvalidArgumentException::class);
        $builder->create();
    }
}
