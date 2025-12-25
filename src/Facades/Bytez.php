<?php

namespace Bytez\LaravelBytez\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Bytez\LaravelBytez\Models\ChatModel chat(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\ImageModel image(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\VideoModel video(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\AudioModel audio(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\MultimodalModel multimodal(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\LanguageModel language(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\TextToImageModel textToImage(string $modelId)
 * @method static \Bytez\LaravelBytez\Builders\ModelBuilder model(string $modelId)
 * @method static \Bytez\LaravelBytez\Builders\TaskBuilder task(string $taskName)
 * @method static \Bytez\LaravelBytez\Builders\ChatCompletionsBuilder chatCompletions(string $model)
 * @method static \Bytez\LaravelBytez\Builders\CompletionsBuilder completions(string $model, string $prompt)
 * @method static array listModels(array $filters = [])
 * @method static array listTasks()
 * @method static array listRunningModels()
 * @method static \Bytez\LaravelBytez\Services\BytezClient getClient()
 *
 * @see \Bytez\LaravelBytez\Services\BytezManager
 */
class Bytez extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Bytez\LaravelBytez\Services\BytezManager::class;
    }
}
