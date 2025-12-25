<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class VideoModel extends BaseModel
{
    /**
     * Run the video model with input.
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
     * Generate a video from text or image.
     */
    public function generate($input, array $parameters = []): array
    {
        return $this->run($input, $parameters);
    }

    /**
     * Classify a video.
     */
    public function classify($video, array $parameters = []): array
    {
        return $this->run($video, $parameters);
    }
}
