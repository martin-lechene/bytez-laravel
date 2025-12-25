<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class ImageModel extends BaseModel
{
    /**
     * Run the image model with input.
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
     * Classify an image.
     */
    public function classify($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }

    /**
     * Detect objects in an image.
     */
    public function detectObjects($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }

    /**
     * Generate an image from text.
     */
    public function generate(string $prompt, array $parameters = []): array
    {
        return $this->run($prompt, $parameters);
    }
}
