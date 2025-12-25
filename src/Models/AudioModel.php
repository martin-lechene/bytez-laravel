<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class AudioModel extends BaseModel
{
    /**
     * Run the audio model with input.
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
     * Transcribe audio to text.
     */
    public function transcribe($audio, array $parameters = []): array
    {
        return $this->run($audio, $parameters);
    }

    /**
     * Generate audio from text.
     */
    public function generate(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }

    /**
     * Classify audio.
     */
    public function classify($audio, array $parameters = []): array
    {
        return $this->run($audio, $parameters);
    }
}
