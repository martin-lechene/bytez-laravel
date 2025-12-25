<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class LanguageModel extends BaseModel
{
    /**
     * Run the language model with text input.
     */
    public function run($input, array $parameters = []): array
    {
        $parameters = $this->mergeParameters($parameters);

        $data = [
            'input' => $input,
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        return $this->client->post(
            "/models/v2/{$this->modelId}/run",
            $data,
            $this->providerKey
        );
    }

    /**
     * Complete text.
     */
    public function complete(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }

    /**
     * Generate embeddings.
     */
    public function embed($input, array $parameters = []): array
    {
        return $this->run($input, $parameters);
    }
}
