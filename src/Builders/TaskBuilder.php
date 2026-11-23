<?php

namespace Bytez\LaravelBytez\Builders;

use Bytez\LaravelBytez\Services\BytezClient;

class TaskBuilder
{
    protected BytezClient $client;
    protected string $taskName;
    protected ?string $modelId = null;
    protected ?string $providerKey = null;

    public function __construct(BytezClient $client, string $taskName)
    {
        $this->client = $client;
        $this->taskName = $taskName;
    }

    /**
     * Specify a model to use for this task.
     */
    public function withModel(string $modelId): self
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     * Set the provider key for closed-source models.
     */
    public function withProviderKey(?string $providerKey): self
    {
        $this->providerKey = $providerKey;
        return $this;
    }

    /**
     * Get available models for this task.
     */
    public function getModels(): array
    {
        $filters = ['task' => $this->taskName];
        
        if ($this->modelId) {
            $filters['model_id'] = $this->modelId;
        }

        return $this->client->get('/models/v2/models', $filters);
    }

    /**
     * Run the task with the given input.
     */
    public function run($input, ?string $modelId = null): array
    {
        $modelId = $modelId ?? $this->modelId;
        
        if (!$modelId) {
            throw new \InvalidArgumentException('Model ID is required to run a task');
        }

        $builder = new ModelBuilder($this->client, $modelId);
        
        if ($this->providerKey) {
            $builder->withProviderKey($this->providerKey);
        }

        return $builder->run($input);
    }
}
