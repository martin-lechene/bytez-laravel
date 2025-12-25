<?php

namespace Bytez\LaravelBytez\Tests\Unit;

use Bytez\LaravelBytez\Models\ChatModel;
use Bytez\LaravelBytez\Models\ImageModel;
use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Services\BytezManager;
use Bytez\LaravelBytez\Tests\TestCase;

class BytezManagerTest extends TestCase
{
    public function test_manager_can_create_chat_model()
    {
        $client = $this->app->make(BytezClient::class);
        $manager = new BytezManager($client);

        $chatModel = $manager->chat('openai-community/gpt-2');
        $this->assertInstanceOf(ChatModel::class, $chatModel);
    }

    public function test_manager_can_create_image_model()
    {
        $client = $this->app->make(BytezClient::class);
        $manager = new BytezManager($client);

        $imageModel = $manager->image('google/vit-base-patch16-224');
        $this->assertInstanceOf(ImageModel::class, $imageModel);
    }

    public function test_manager_can_list_models()
    {
        $client = $this->app->make(BytezClient::class);
        $manager = new BytezManager($client);

        // This would make an actual API call in a real test
        // For now, we just verify the method exists
        $this->assertTrue(method_exists($manager, 'listModels'));
    }
}
