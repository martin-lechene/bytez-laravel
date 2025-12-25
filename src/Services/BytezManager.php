<?php

namespace Bytez\LaravelBytez\Services;

use Bytez\LaravelBytez\Builders\ChatCompletionsBuilder;
use Bytez\LaravelBytez\Builders\CompletionsBuilder;
use Bytez\LaravelBytez\Builders\ModelBuilder;
use Bytez\LaravelBytez\Builders\TaskBuilder;
use Bytez\LaravelBytez\Models\AudioModel;
use Bytez\LaravelBytez\Models\ChatModel;
use Bytez\LaravelBytez\Models\ImageModel;
use Bytez\LaravelBytez\Models\LanguageModel;
use Bytez\LaravelBytez\Models\MultimodalModel;
use Bytez\LaravelBytez\Models\TextToImageModel;
use Bytez\LaravelBytez\Models\VideoModel;
use Bytez\LaravelBytez\Services\OpenAIClient;

class BytezManager
{
    protected BytezClient $client;
    protected ?OpenAIClient $openAIClient = null;

    public function __construct(BytezClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get OpenAI-compatible client instance.
     */
    protected function getOpenAIClient(): OpenAIClient
    {
        if ($this->openAIClient === null) {
            $this->openAIClient = new OpenAIClient(
                config('bytez.api_key'),
                config('bytez.base_url', 'https://api.bytez.com'),
                config('bytez.timeout', 30),
                config('bytez.retry_attempts', 3),
                config('bytez.retry_delay', 1000)
            );
        }

        return $this->openAIClient;
    }

    /**
     * Get provider key for a model (if needed).
     */
    protected function getProviderKeyForModel(string $model): ?string
    {
        // Free providers don't need a key
        $freeProviders = ['openai', 'mistral', 'google'];
        
        // Extract provider from model (e.g., "openai/gpt-4" -> "openai")
        if (strpos($model, '/') !== false) {
            $provider = explode('/', $model)[0];
            
            // If it's a free provider, return null
            if (in_array($provider, $freeProviders)) {
                return null;
            }
            
            // For paid providers, get key from config
            $providerKeys = config('bytez.provider_keys', []);
            return $providerKeys[$provider] ?? null;
        }
        
        return null;
    }

    /**
     * Get a chat model instance.
     */
    public function chat(string $modelId): ChatModel
    {
        return new ChatModel($this->client, $modelId);
    }

    /**
     * Get an image model instance.
     */
    public function image(string $modelId): ImageModel
    {
        return new ImageModel($this->client, $modelId);
    }

    /**
     * Get a video model instance.
     */
    public function video(string $modelId): VideoModel
    {
        return new VideoModel($this->client, $modelId);
    }

    /**
     * Get an audio model instance.
     */
    public function audio(string $modelId): AudioModel
    {
        return new AudioModel($this->client, $modelId);
    }

    /**
     * Get a multimodal model instance.
     */
    public function multimodal(string $modelId): MultimodalModel
    {
        return new MultimodalModel($this->client, $modelId);
    }

    /**
     * Get a language model instance.
     */
    public function language(string $modelId): LanguageModel
    {
        return new LanguageModel($this->client, $modelId);
    }

    /**
     * Get a text-to-image model instance.
     */
    public function textToImage(string $modelId): TextToImageModel
    {
        return new TextToImageModel($this->client, $modelId);
    }

    /**
     * Get a model builder instance.
     */
    public function model(string $modelId): ModelBuilder
    {
        return new ModelBuilder($this->client, $modelId);
    }

    /**
     * Get a task builder instance.
     */
    public function task(string $taskName): TaskBuilder
    {
        return new TaskBuilder($this->client, $taskName);
    }

    /**
     * List all available models.
     */
    public function listModels(array $filters = []): array
    {
        return $this->client->get('/models/v2/models', $filters);
    }

    /**
     * List all available tasks.
     */
    public function listTasks(): array
    {
        return $this->client->get('/models/v2/tasks');
    }

    /**
     * List all running models.
     */
    public function listRunningModels(): array
    {
        return $this->client->get('/models/v2/running');
    }

    /**
     * Get the underlying client instance.
     */
    public function getClient(): BytezClient
    {
        return $this->client;
    }

    /**
     * Create a chat completions builder (OpenAI-compatible).
     */
    public function chatCompletions(string $model): ChatCompletionsBuilder
    {
        $builder = new ChatCompletionsBuilder($this->getOpenAIClient(), $model);
        
        // Auto-detect and set provider key if needed
        $providerKey = $this->getProviderKeyForModel($model);
        if ($providerKey) {
            $builder->withProviderKey($providerKey);
        }
        
        return $builder;
    }

    /**
     * Create a completions builder (OpenAI-compatible, for text completions).
     */
    public function completions(string $model, string $prompt): CompletionsBuilder
    {
        $builder = new CompletionsBuilder($this->getOpenAIClient(), $model, $prompt);
        
        // Auto-detect and set provider key if needed
        $providerKey = $this->getProviderKeyForModel($model);
        if ($providerKey) {
            $builder->withProviderKey($providerKey);
        }
        
        return $builder;
    }
}
