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

    /**
     * Summarize text.
     */
    public function summarize(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }

    /**
     * Translate text.
     */
    public function translate(string $text, ?string $targetLanguage = null, array $parameters = []): array
    {
        if ($targetLanguage) {
            $parameters['target_language'] = $targetLanguage;
        }
        return $this->run($text, $parameters);
    }

    /**
     * Answer a question based on context.
     */
    public function answerQuestion(string $question, ?string $context = null, array $parameters = []): array
    {
        $input = $context ? ['question' => $question, 'context' => $context] : $question;
        return $this->run($input, $parameters);
    }

    /**
     * Extract features/embeddings.
     */
    public function extractFeatures($input, array $parameters = []): array
    {
        return $this->run($input, $parameters);
    }

    /**
     * Calculate sentence similarity.
     */
    public function sentenceSimilarity(string $sentence1, string $sentence2, array $parameters = []): array
    {
        $input = ['source_sentence' => $sentence1, 'sentences' => [$sentence2]];
        return $this->run($input, $parameters);
    }

    /**
     * Zero-shot text classification.
     */
    public function zeroShotClassify(string $text, array $candidateLabels, array $parameters = []): array
    {
        $input = ['sequences' => $text, 'candidate_labels' => $candidateLabels];
        return $this->run($input, $parameters);
    }

    /**
     * Classify text.
     */
    public function classifyText(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }

    /**
     * Classify tokens (NER, POS tagging, etc.).
     */
    public function classifyTokens(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }

    /**
     * Fill mask in text.
     */
    public function fillMask(string $text, array $parameters = []): array
    {
        return $this->run($text, $parameters);
    }
}
