<?php

namespace Bytez\LaravelBytez\Builders;

use Bytez\LaravelBytez\Services\BytezClient;

class ModelBuilder
{
    protected BytezClient $client;
    protected string $modelId;
    protected ?string $providerKey = null;
    protected array $parameters = [];
    protected array $options = [];

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
     * Set model parameters.
     */
    public function withParameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    /**
     * Set a specific parameter.
     */
    public function withParameter(string $key, $value): self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Set request options.
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Run the model with the given input.
     */
    public function run($input): array
    {
        $data = [
            'input' => $input,
        ];

        if (!empty($this->parameters)) {
            $data['parameters'] = $this->parameters;
        }

        if (!empty($this->options)) {
            $data = array_merge($data, $this->options);
        }

        return $this->client->post(
            "/models/v2/{$this->modelId}/run",
            $data,
            $this->providerKey
        );
    }

    /**
     * Get model information.
     */
    public function info(): array
    {
        return $this->client->get("/models/v2/{$this->modelId}");
    }
}
