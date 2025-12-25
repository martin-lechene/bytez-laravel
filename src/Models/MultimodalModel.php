<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class MultimodalModel extends BaseModel
{
    /**
     * Run the multimodal model with input (can be text, image, audio, video, etc.).
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
     * Process multiple types of input together.
     */
    public function process(array $inputs, array $parameters = []): array
    {
        return $this->run($inputs, $parameters);
    }

    /**
     * Answer question about a document.
     */
    public function answerDocumentQuestion(string $question, $documentInput, array $parameters = []): array
    {
        $input = ['question' => $question, 'document' => $documentInput];
        return $this->run($input, $parameters);
    }

    /**
     * Answer question about an image (visual question answering).
     */
    public function answerVisualQuestion(string $question, $imageInput, array $parameters = []): array
    {
        $input = ['question' => $question, 'image' => $imageInput];
        return $this->run($input, $parameters);
    }
}
