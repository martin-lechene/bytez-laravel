<?php

namespace Bytez\LaravelBytez\Services;

use Bytez\LaravelBytez\Builders\ChatCompletionsBuilder;
use Bytez\LaravelBytez\Builders\CompletionsBuilder;
use Bytez\LaravelBytez\Builders\ModelBuilder;
use Bytez\LaravelBytez\Builders\TaskBuilder;
use Bytez\LaravelBytez\Models\AudioModel;
use Bytez\LaravelBytez\Models\ChatModel;
use Bytez\LaravelBytez\Models\ImageModel;
use Bytez\LaravelBytez\Models\LanguageModel;
use Bytez\LaravelBytez\Models\MultimodalModel;
use Bytez\LaravelBytez\Models\TextToImageModel;
use Bytez\LaravelBytez\Models\VideoModel;
use Bytez\LaravelBytez\Services\OpenAIClient;

class BytezManager
{
    protected BytezClient $client;
    protected ?OpenAIClient $openAIClient = null;

    public function __construct(BytezClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get OpenAI-compatible client instance.
     */
    protected function getOpenAIClient(): OpenAIClient
    {
        if ($this->openAIClient === null) {
            $this->openAIClient = new OpenAIClient(
                config('bytez.api_key'),
                config('bytez.base_url', 'https://api.bytez.com'),
                config('bytez.timeout', 30),
                config('bytez.retry_attempts', 3),
                config('bytez.retry_delay', 1000)
            );
        }

        return $this->openAIClient;
    }

    /**
     * Get provider key for a model (if needed).
     */
    protected function getProviderKeyForModel(string $model): ?string
    {
        // Free providers don't need a key
        $freeProviders = ['openai', 'mistral', 'google'];
        
        // Extract provider from model (e.g., "openai/gpt-4" -> "openai")
        if (strpos($model, '/') !== false) {
            $provider = explode('/', $model)[0];
            
            // If it's a free provider, return null
            if (in_array($provider, $freeProviders)) {
                return null;
            }
            
            // For paid providers, get key from config
            $providerKeys = config('bytez.provider_keys', []);
            return $providerKeys[$provider] ?? null;
        }
        
        return null;
    }

    /**
     * Get a chat model instance.
     */
    public function chat(string $modelId): ChatModel
    {
        return new ChatModel($this->client, $modelId);
    }

    /**
     * Get an image model instance.
     */
    public function image(string $modelId): ImageModel
    {
        return new ImageModel($this->client, $modelId);
    }

    /**
     * Get a video model instance.
     */
    public function video(string $modelId): VideoModel
    {
        return new VideoModel($this->client, $modelId);
    }

    /**
     * Get an audio model instance.
     */
    public function audio(string $modelId): AudioModel
    {
        return new AudioModel($this->client, $modelId);
    }

    /**
     * Get a multimodal model instance.
     */
    public function multimodal(string $modelId): MultimodalModel
    {
        return new MultimodalModel($this->client, $modelId);
    }

    /**
     * Get a language model instance.
     */
    public function language(string $modelId): LanguageModel
    {
        return new LanguageModel($this->client, $modelId);
    }

    /**
     * Get a text-to-image model instance.
     */
    public function textToImage(string $modelId): TextToImageModel
    {
        return new TextToImageModel($this->client, $modelId);
    }

    /**
     * Get a model builder instance.
     */
    public function model(string $modelId): ModelBuilder
    {
        return new ModelBuilder($this->client, $modelId);
    }

    /**
     * Get a task builder instance.
     */
    public function task(string $taskName): TaskBuilder
    {
        return new TaskBuilder($this->client, $taskName);
    }

    /**
     * List all available models.
     */
    public function listModels(array $filters = []): array
    {
        return $this->client->get('/models/v2/models', $filters);
    }

    /**
     * List all available tasks.
     */
    public function listTasks(): array
    {
        return $this->client->get('/models/v2/tasks');
    }

    /**
     * List all running models.
     */
    public function listRunningModels(): array
    {
        return $this->client->get('/models/v2/running');
    }

    /**
     * Get the underlying client instance.
     */
    public function getClient(): BytezClient
    {
        return $this->client;
    }

    /**
     * Create a chat completions builder (OpenAI-compatible).
     */
    public function chatCompletions(string $model): ChatCompletionsBuilder
    {
        $builder = new ChatCompletionsBuilder($this->getOpenAIClient(), $model);
        
        // Auto-detect and set provider key if needed
        $providerKey = $this->getProviderKeyForModel($model);
        if ($providerKey) {
            $builder->withProviderKey($providerKey);
        }
        
        return $builder;
    }

    /**
     * Create a completions builder (OpenAI-compatible, for text completions).
     */
    public function completions(string $model, string $prompt): CompletionsBuilder
    {
        $builder = new CompletionsBuilder($this->getOpenAIClient(), $model, $prompt);
        
        // Auto-detect and set provider key if needed
        $providerKey = $this->getProviderKeyForModel($model);
        if ($providerKey) {
            $builder->withProviderKey($providerKey);
        }
        
        return $builder;
    }

    // ============================================
    // Text Task Helpers
    // ============================================

    /**
     * Audio to text conversion (audio-text-to-text).
     */
    public function audioTextToText(string $modelId, $audioInput, array $parameters = [], ?string $providerKey = null): array
    {
        $model = $this->chat($modelId);
        if ($providerKey) {
            $model->withProviderKey($providerKey);
        }
        return $model->run($audioInput, $parameters);
    }

    /**
     * Image to text conversion (image-text-to-text).
     */
    public function imageTextToText(string $modelId, $imageInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null): array
    {
        $input = $prompt ? ['image' => $imageInput, 'text' => $prompt] : $imageInput;
        $model = $this->chat($modelId);
        if ($providerKey) {
            $model->withProviderKey($providerKey);
        }
        return $model->run($input, $parameters);
    }

    /**
     * Video to text conversion (video-text-to-text).
     */
    public function videoTextToText(string $modelId, $videoInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null): array
    {
        $input = $prompt ? ['video' => $videoInput, 'text' => $prompt] : $videoInput;
        $model = $this->chat($modelId);
        if ($providerKey) {
            $model->withProviderKey($providerKey);
        }
        return $model->run($input, $parameters);
    }

    /**
     * Summarize text.
     */
    public function summarize(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('summarization')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    /**
     * Translate text.
     */
    public function translate(string $modelId, string $text, ?string $targetLanguage = null, array $parameters = [], ?string $providerKey = null): array
    {
        if ($targetLanguage) {
            $parameters['target_language'] = $targetLanguage;
        }
        return $this->task('translation')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    /**
     * Answer a question based on context.
     */
    public function answerQuestion(string $modelId, string $question, ?string $context = null, array $parameters = [], ?string $providerKey = null): array
    {
        $input = $context ? ['question' => $question, 'context' => $context] : $question;
        return $this->task('question-answering')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Extract features/embeddings from text.
     */
    public function extractFeatures(string $modelId, $input, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('feature-extraction')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Calculate sentence similarity.
     */
    public function sentenceSimilarity(string $modelId, string $sentence1, string $sentence2, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['source_sentence' => $sentence1, 'sentences' => [$sentence2]];
        return $this->task('sentence-similarity')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Zero-shot text classification.
     */
    public function zeroShotClassify(string $modelId, string $text, array $candidateLabels, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['sequences' => $text, 'candidate_labels' => $candidateLabels];
        return $this->task('zero-shot-classification')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Classify text.
     */
    public function classifyText(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('text-classification')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    /**
     * Classify tokens (NER, POS tagging, etc.).
     */
    public function classifyTokens(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('token-classification')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    /**
     * Fill mask in text.
     */
    public function fillMask(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('fill-mask')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    // ============================================
    // Image Task Helpers
    // ============================================

    /**
     * Convert image to text.
     */
    public function imageToText(string $modelId, $imageInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null): array
    {
        $input = $prompt ? ['image' => $imageInput, 'text' => $prompt] : $imageInput;
        return $this->task('image-to-text')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Zero-shot object detection.
     */
    public function zeroShotDetectObjects(string $modelId, $imageInput, array $candidateLabels, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['image' => $imageInput, 'candidate_labels' => $candidateLabels];
        return $this->task('zero-shot-object-detection')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Zero-shot image classification.
     */
    public function zeroShotClassifyImage(string $modelId, $imageInput, array $candidateLabels, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['image' => $imageInput, 'candidate_labels' => $candidateLabels];
        return $this->task('zero-shot-image-classification')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Extract image features/embeddings.
     */
    public function extractImageFeatures(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('image-feature-extraction')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($imageInput);
    }

    /**
     * Segment image.
     */
    public function segmentImage(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('image-segmentation')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($imageInput);
    }

    /**
     * Estimate depth in image.
     */
    public function estimateDepth(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('depth-estimation')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($imageInput);
    }

    /**
     * Generate mask for image.
     */
    public function generateMask(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('mask-generation')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($imageInput);
    }

    /**
     * Generate image unconditionally.
     */
    public function generateImageUnconditional(string $modelId, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('unconditional-image-generation')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run([]);
    }

    // ============================================
    // Audio Task Helpers
    // ============================================

    /**
     * Text to speech conversion.
     */
    public function textToSpeech(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('text-to-speech')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    /**
     * Text to audio conversion.
     */
    public function textToAudio(string $modelId, string $text, array $parameters = [], ?string $providerKey = null): array
    {
        return $this->task('text-to-audio')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($text);
    }

    // ============================================
    // Multimodal Task Helpers
    // ============================================

    /**
     * Answer question about a document.
     */
    public function answerDocumentQuestion(string $modelId, string $question, $documentInput, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['question' => $question, 'document' => $documentInput];
        return $this->task('document-question-answering')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }

    /**
     * Answer question about an image (visual question answering).
     */
    public function answerVisualQuestion(string $modelId, string $question, $imageInput, array $parameters = [], ?string $providerKey = null): array
    {
        $input = ['question' => $question, 'image' => $imageInput];
        return $this->task('visual-question-answering')
            ->withModel($modelId)
            ->withProviderKey($providerKey)
            ->run($input);
    }
}
