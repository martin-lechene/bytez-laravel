<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

abstract class BaseModel
{
    protected BytezClient $client;
    protected string $modelId;
    protected ?string $providerKey = null;
    protected array $defaultParameters = [];

    public function __construct(BytezClient $client, string $modelId)
    {
        $this->client = $client;
        $this->modelId = $modelId;
    }

    /**
     * Set the provider key for closed-source models.
     */
    public function withProviderKey(string $providerKey): self
    {
        $this->providerKey = $providerKey;
        return $this;
    }

    /**
     * Set default parameters for this model.
     */
    public function withDefaultParameters(array $parameters): self
    {
        $this->defaultParameters = array_merge($this->defaultParameters, $parameters);
        return $this;
    }

    /**
     * Run the model with the given input.
     */
    abstract public function run($input, array $parameters = []): array;

    /**
     * Get model information.
     */
    public function info(): array
    {
        return $this->client->get("/models/v2/{$this->modelId}");
    }

    /**
     * Get the model ID.
     */
    public function getModelId(): string
    {
        return $this->modelId;
    }

    /**
     * Merge default parameters with provided parameters.
     */
    protected function mergeParameters(array $parameters): array
    {
        return array_merge($this->defaultParameters, $parameters);
    }
}
