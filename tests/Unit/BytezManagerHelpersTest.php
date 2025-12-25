<?php

namespace Bytez\LaravelBytez\Tests\Unit;

use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Services\BytezManager;
use Bytez\LaravelBytez\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class BytezManagerHelpersTest extends TestCase
{
    public function test_manager_can_summarize()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Summary text'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $manager = new BytezManager($client);
        $result = $manager->summarize('model-id', 'Long text to summarize');

        $this->assertArrayHasKey('output', $result);
    }

    public function test_manager_can_translate()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Bonjour'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $manager = new BytezManager($client);
        $result = $manager->translate('model-id', 'Hello', 'fr');

        $this->assertArrayHasKey('output', $result);
    }

    public function test_manager_can_answer_question()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => 'Paris'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $manager = new BytezManager($client);
        $result = $manager->answerQuestion(
            'model-id',
            'What is the capital of France?',
            'France is a country in Europe'
        );

        $this->assertArrayHasKey('output', $result);
    }

    public function test_manager_can_extract_features()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => [0.1, 0.2, 0.3]])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $manager = new BytezManager($client);
        $result = $manager->extractFeatures('model-id', 'Text to embed');

        $this->assertArrayHasKey('output', $result);
    }

    public function test_manager_can_zero_shot_classify()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['output' => [['label' => 'positive', 'score' => 0.9]]])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $client = new BytezClient('test-key');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        $manager = new BytezManager($client);
        $result = $manager->zeroShotClassify('model-id', 'I love this!', ['positive', 'negative']);

        $this->assertArrayHasKey('output', $result);
    }
}
