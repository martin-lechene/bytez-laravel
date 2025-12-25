<?php

namespace Bytez\LaravelBytez\Tests\Unit\Builders;

use Bytez\LaravelBytez\Builders\ModelBuilder;
use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ModelBuilderTest extends TestCase
{
    public function test_model_builder_can_set_provider_key()
    {
        $client = new BytezClient('test-key');
        $builder = new ModelBuilder($client, 'test-model');

        $builder->withProviderKey('provider-key-123');
        $this->assertInstanceOf(ModelBuilder::class, $builder);
    }

    public function test_model_builder_can_set_parameters()
    {
        $client = new BytezClient('test-key');
        $builder = new ModelBuilder($client, 'test-model');

        $builder->withParameters(['temperature' => 0.7, 'max_tokens' => 100]);
        $this->assertInstanceOf(ModelBuilder::class, $builder);
    }

    public function test_model_builder_can_run_model()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'result'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $builder = new ModelBuilder($client, 'test-model');
        $result = $builder->run('test input');

        $this->assertArrayHasKey('output', $result);
        $this->assertEquals('result', $result['output']);
    }
}
