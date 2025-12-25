<?php

namespace Bytez\LaravelBytez\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Bytez\LaravelBytez\Models\ChatModel chat(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\ImageModel image(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\VideoModel video(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\AudioModel audio(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\MultimodalModel multimodal(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\LanguageModel language(string $modelId)
 * @method static \Bytez\LaravelBytez\Models\TextToImageModel textToImage(string $modelId)
 * @method static \Bytez\LaravelBytez\Builders\ModelBuilder model(string $modelId)
 * @method static \Bytez\LaravelBytez\Builders\TaskBuilder task(string $taskName)
 * @method static \Bytez\LaravelBytez\Builders\ChatCompletionsBuilder chatCompletions(string $model)
 * @method static \Bytez\LaravelBytez\Builders\CompletionsBuilder completions(string $model, string $prompt)
 * @method static array listModels(array $filters = [])
 * @method static array listTasks()
 * @method static array listRunningModels()
 * @method static \Bytez\LaravelBytez\Services\BytezClient getClient()
 * 
 * Text Task Helpers
 * @method static array audioTextToText(string $modelId, $audioInput, array $parameters = [], ?string $providerKey = null)
 * @method static array imageTextToText(string $modelId, $imageInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null)
 * @method static array videoTextToText(string $modelId, $videoInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null)
 * @method static array summarize(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * @method static array translate(string $modelId, string $text, ?string $targetLanguage = null, array $parameters = [], ?string $providerKey = null)
 * @method static array answerQuestion(string $modelId, string $question, ?string $context = null, array $parameters = [], ?string $providerKey = null)
 * @method static array extractFeatures(string $modelId, $input, array $parameters = [], ?string $providerKey = null)
 * @method static array sentenceSimilarity(string $modelId, string $sentence1, string $sentence2, array $parameters = [], ?string $providerKey = null)
 * @method static array zeroShotClassify(string $modelId, string $text, array $candidateLabels, array $parameters = [], ?string $providerKey = null)
 * @method static array classifyText(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * @method static array classifyTokens(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * @method static array fillMask(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * 
 * Image Task Helpers
 * @method static array imageToText(string $modelId, $imageInput, ?string $prompt = null, array $parameters = [], ?string $providerKey = null)
 * @method static array zeroShotDetectObjects(string $modelId, $imageInput, array $candidateLabels, array $parameters = [], ?string $providerKey = null)
 * @method static array zeroShotClassifyImage(string $modelId, $imageInput, array $candidateLabels, array $parameters = [], ?string $providerKey = null)
 * @method static array extractImageFeatures(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null)
 * @method static array segmentImage(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null)
 * @method static array estimateDepth(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null)
 * @method static array generateMask(string $modelId, $imageInput, array $parameters = [], ?string $providerKey = null)
 * @method static array generateImageUnconditional(string $modelId, array $parameters = [], ?string $providerKey = null)
 * 
 * Audio Task Helpers
 * @method static array textToSpeech(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * @method static array textToAudio(string $modelId, string $text, array $parameters = [], ?string $providerKey = null)
 * 
 * Multimodal Task Helpers
 * @method static array answerDocumentQuestion(string $modelId, string $question, $documentInput, array $parameters = [], ?string $providerKey = null)
 * @method static array answerVisualQuestion(string $modelId, string $question, $imageInput, array $parameters = [], ?string $providerKey = null)
 *
 * @see \Bytez\LaravelBytez\Services\BytezManager
 */
class Bytez extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Bytez\LaravelBytez\Services\BytezManager::class;
    }
}
