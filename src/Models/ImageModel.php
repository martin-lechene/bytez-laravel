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

    /**
     * Convert image to text.
     */
    public function toText($image, ?string $prompt = null, array $parameters = []): array
    {
        $input = $prompt ? ['image' => $image, 'text' => $prompt] : $image;
        return $this->run($input, $parameters);
    }

    /**
     * Zero-shot object detection.
     */
    public function zeroShotDetectObjects($image, array $candidateLabels, array $parameters = []): array
    {
        $input = ['image' => $image, 'candidate_labels' => $candidateLabels];
        return $this->run($input, $parameters);
    }

    /**
     * Zero-shot image classification.
     */
    public function zeroShotClassify($image, array $candidateLabels, array $parameters = []): array
    {
        $input = ['image' => $image, 'candidate_labels' => $candidateLabels];
        return $this->run($input, $parameters);
    }

    /**
     * Extract image features/embeddings.
     */
    public function extractFeatures($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }

    /**
     * Segment image.
     */
    public function segment($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }

    /**
     * Estimate depth in image.
     */
    public function estimateDepth($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }

    /**
     * Generate mask for image.
     */
    public function generateMask($image, array $parameters = []): array
    {
        return $this->run($image, $parameters);
    }
}
