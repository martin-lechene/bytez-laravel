<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class TextToImageModel extends BaseModel
{
    /**
     * Run the text-to-image model with a prompt.
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
     * Generate an image from a text prompt.
     */
    public function generate(string $prompt, array $parameters = []): array
    {
        return $this->run($prompt, $parameters);
    }
}
