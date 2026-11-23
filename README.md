# Bytez Laravel Package

[![Tests](https://github.com/martin-lechene/bytez-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/martin-lechene/bytez-laravel/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/martin-lechene/bytez-laravel.svg?style=flat-square)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4-777BB4.svg?style=flat-square)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-FF2D20.svg?style=flat-square)](composer.json)

A Laravel package for the [Bytez](https://bytez.com) API, allowing you to use 175k+ AI models (open-source and closed-source) with a single API key.

> **Unofficial package.** This is an independent community package maintained by Dog and Dev. It is not published, endorsed or supported by Bytez. "Bytez" is the name of the third-party service this package talks to; the `Bytez\LaravelBytez` namespace only reflects that.
>
> **Beta.** The package is not on Packagist yet and has no tagged release yet. Install it from the GitHub repository (see below). Public APIs may still change.

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, 12 or 13 (the `illuminate/support`, `illuminate/http` and `illuminate/cache` components)
- A Bytez API key

CI runs the test suite against PHP 8.2 to 8.4 and Laravel 10 to 13 (see the matrix in `.github/workflows/tests.yml`).

## 🚀 Installation

The package is not on Packagist yet. Declare the GitHub repository as a VCS repository in your application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/martin-lechene/bytez-laravel"
        }
    ]
}
```

Then require it. As there is no tag yet, use the `main` branch:

```bash
composer require martin-lechene/laravel-bytez:dev-main
```

Once a release is tagged and the package is submitted to Packagist, a plain `composer require martin-lechene/laravel-bytez` will be enough.

Laravel auto-discovers the service provider and the `Bytez` facade alias.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=bytez-config
```

Add your Bytez API key to your `.env` file:

```env
BYTEZ_API_KEY=your-bytez-api-key
```

## 📖 Documentation

### Configuration

The `config/bytez.php` configuration file contains all available options:

```php
return [
    'api_key' => env('BYTEZ_API_KEY'),
    'base_url' => env('BYTEZ_BASE_URL', 'https://api.bytez.com'),
    'timeout' => env('BYTEZ_TIMEOUT', 30),
    'retry_attempts' => env('BYTEZ_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('BYTEZ_RETRY_DELAY', 1000),
    'cache' => [
        'enabled' => env('BYTEZ_CACHE_ENABLED', true),
        'ttl' => env('BYTEZ_CACHE_TTL', 3600),
        'prefix' => env('BYTEZ_CACHE_PREFIX', 'bytez'),
    ],
    'provider_keys' => [
        // 'openai' => env('OPENAI_API_KEY'),
        // 'anthropic' => env('ANTHROPIC_API_KEY'),
    ],
];
```

| Key | Environment variable | Default | Description |
|---|---|---|---|
| `api_key` | `BYTEZ_API_KEY` | none | Your Bytez API key (required) |
| `base_url` | `BYTEZ_BASE_URL` | `https://api.bytez.com` | API base URL |
| `timeout` | `BYTEZ_TIMEOUT` | `30` | Request timeout in seconds |
| `retry_attempts` | `BYTEZ_RETRY_ATTEMPTS` | `3` | Maximum number of attempts per request |
| `retry_delay` | `BYTEZ_RETRY_DELAY` | `1000` | Base delay between attempts, in milliseconds |
| `cache.enabled`, `cache.ttl`, `cache.prefix` | `BYTEZ_CACHE_*` | `true`, `3600`, `bytez` | Declared in the config file but **not read by the code yet** (see [Cache](#cache)) |
| `provider_keys` | e.g. `ANTHROPIC_API_KEY` | empty | Default provider keys for closed-source models, indexed by provider name |

### Basic Usage

#### Via Facade

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Chat Model
$response = Bytez::chat('openai-community/gpt-2')
    ->message('Hello, how are you?');

// Image Model
$response = Bytez::image('google/vit-base-patch16-224')
    ->classify($imageData);

// Text-to-Image
$response = Bytez::textToImage('stability-ai/stable-diffusion')
    ->generate('A beautiful sunset over the ocean');
```

#### Via Dependency Injection

```php
use Bytez\LaravelBytez\Services\BytezManager;

class MyController extends Controller
{
    public function __construct(
        protected BytezManager $bytez
    ) {}

    public function chat()
    {
        $response = $this->bytez->chat('openai-community/gpt-2')
            ->message('Hello!');
    }
}
```

### OpenAI-Compatible Chat Completions

The package supports OpenAI-compatible endpoints for seamless integration:

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Basic chat completion (open-source model)
$response = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->system('You are a helpful assistant')
    ->user('What is the capital of France?')
    ->maxTokens(150)
    ->temperature(0.7)
    ->create();

echo $response['choices'][0]['message']['content'];

// Chat completion with closed-source model (provider key auto-detected)
$response = Bytez::chatCompletions('openai/gpt-4')
    ->system('You are a friendly chatbot')
    ->user('Hello!')
    ->maxTokens(100)
    ->create();

// Streaming support
$stream = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->user('Tell me a story')
    ->stream()
    ->create();

foreach ($stream as $chunk) {
    if (isset($chunk['choices'][0]['delta']['content'])) {
        echo $chunk['choices'][0]['delta']['content'];
    }
    
    // Check for finish reason
    if (isset($chunk['choices'][0]['finish_reason'])) {
        break;
    }
}

// With all OpenAI parameters
$response = Bytez::chatCompletions('openai/gpt-4')
    ->messages([
        ['role' => 'system', 'content' => 'You are helpful'],
        ['role' => 'user', 'content' => 'Hello'],
    ])
    ->maxTokens(150)
    ->temperature(0.7)
    ->topP(0.9)
    ->frequencyPenalty(0.5)
    ->presencePenalty(0.5)
    ->logprobs(5)
    ->stop(['\n', 'END'])
    ->n(1)
    ->create();
```

### Text Completions (OpenAI-compatible)

```php
// Text completion
$response = Bytez::completions('Qwen/Qwen3-4B', 'The weather today is')
    ->maxTokens(50)
    ->temperature(0.7)
    ->create();

echo $response['choices'][0]['text'];
```

### Closed-source Models with Provider Keys

Provider keys are automatically detected for closed-source models. Free providers (openai, mistral, google) don't require a key:

```php
// Free provider - no key needed
$response = Bytez::chatCompletions('openai/gpt-4')
    ->user('Hello!')
    ->create();

// Paid provider - key from config or explicit
$response = Bytez::chatCompletions('anthropic/claude-sonnet-4-5')
    ->withProviderKey(env('ANTHROPIC_API_KEY'))
    ->user('Hello!')
    ->create();

// Or configure in config/bytez.php:
'provider_keys' => [
    'anthropic' => env('ANTHROPIC_API_KEY'),
    'cohere' => env('COHERE_API_KEY'),
],
```

### Builder Pattern

The package offers a flexible builder system:

```php
// Model Builder
$response = Bytez::model('openai-community/gpt-2')
    ->withProviderKey('your-provider-key')
    ->withParameter('temperature', 0.7)
    ->withParameter('max_tokens', 100)
    ->run('Your input here');

// Task Builder
$models = Bytez::task('text-generation')
    ->withModel('openai-community/gpt-2')
    ->getModels();
```

### Supported Model Types

#### Chat Models

**Using OpenAI-compatible endpoint (Recommended):**

```php
// OpenAI-compatible chat completions
$response = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->system('You are helpful')
    ->user('Hello!')
    ->maxTokens(100)
    ->create();
```

**Using legacy Bytez API:**

```php
$chat = Bytez::chat('openai-community/gpt-2');

// Simple message
$response = $chat->message('Hello!');

// Conversation
$response = $chat->conversation([
    ['role' => 'user', 'content' => 'Hello'],
    ['role' => 'assistant', 'content' => 'Hi there!'],
    ['role' => 'user', 'content' => 'How are you?'],
]);
```

#### Image Models

```php
$image = Bytez::image('google/vit-base-patch16-224');

// Classification
$response = $image->classify($imageData);

// Object detection
$response = $image->detectObjects($imageData);

// Generation
$response = $image->generate('A beautiful landscape');
```

#### Video Models

```php
$video = Bytez::video('model-id');

// Generation
$response = $video->generate('A video of a sunset');

// Classification
$response = $video->classify($videoData);
```

#### Audio Models

```php
$audio = Bytez::audio('model-id');

// Transcription
$response = $audio->transcribe($audioData);

// Generation
$response = $audio->generate('Generate audio from this text');

// Classification
$response = $audio->classify($audioData);
```

#### Multimodal Models

```php
$multimodal = Bytez::multimodal('model-id');

// Processing multiple input types
$response = $multimodal->process([
    'text' => 'Describe this image',
    'image' => $imageData,
]);
```

#### Language Models

```php
$language = Bytez::language('model-id');

// Text completion
$response = $language->complete('The weather today is');

// Embeddings
$response = $language->embed('Text to embed');
```

#### Text-to-Image Models

```php
$textToImage = Bytez::textToImage('stability-ai/stable-diffusion');

// Generation
$response = $textToImage->generate('A beautiful sunset');
```

### Task-Specific Helpers

The package provides dedicated helper methods for common tasks:

> **Known limitation (beta):** the optional `$parameters` argument of these `Bytez::` helpers (and the `$targetLanguage` of `translate()`) is accepted but not yet forwarded to the API. To send model parameters, use the [Builder Pattern](#builder-pattern) (`Bytez::model(...)->withParameter(...)`) or a model class (`Bytez::chat(...)`, `Bytez::language(...)`...). The `$providerKey` argument is forwarded.

#### Text Tasks

```php
// Summarization
$summary = Bytez::summarize('model-id', 'Long text to summarize...');

// Translation
$translated = Bytez::translate('model-id', 'Hello', 'fr'); // Translate to French

// Question Answering
$answer = Bytez::answerQuestion('model-id', 'What is AI?', 'Context about AI...');

// Feature Extraction
$features = Bytez::extractFeatures('model-id', 'Text to extract features from');

// Sentence Similarity
$similarity = Bytez::sentenceSimilarity('model-id', 'Sentence 1', 'Sentence 2');

// Zero-shot Classification
$classification = Bytez::zeroShotClassify('model-id', 'Text', ['positive', 'negative']);

// Text Classification
$classification = Bytez::classifyText('model-id', 'Text to classify');

// Token Classification (NER, POS)
$tokens = Bytez::classifyTokens('model-id', 'Text with entities');

// Fill Mask
$filled = Bytez::fillMask('model-id', 'The capital of France is [MASK].');
```

#### Multimodal Text Tasks

```php
// Audio to Text
$text = Bytez::audioTextToText('model-id', $audioData);

// Image to Text
$text = Bytez::imageTextToText('model-id', $imageData, 'Describe this image');

// Video to Text
$text = Bytez::videoTextToText('model-id', $videoData, 'What is happening?');
```

#### Image Tasks

```php
// Image to Text
$text = Bytez::imageToText('model-id', $imageData, 'What is in this image?');

// Zero-shot Object Detection
$objects = Bytez::zeroShotDetectObjects('model-id', $imageData, ['person', 'car', 'bike']);

// Zero-shot Image Classification
$classification = Bytez::zeroShotClassifyImage('model-id', $imageData, ['cat', 'dog']);

// Extract Image Features
$features = Bytez::extractImageFeatures('model-id', $imageData);

// Image Segmentation
$segments = Bytez::segmentImage('model-id', $imageData);

// Depth Estimation
$depth = Bytez::estimateDepth('model-id', $imageData);

// Generate Mask
$mask = Bytez::generateMask('model-id', $imageData);

// Unconditional Image Generation
$image = Bytez::generateImageUnconditional('model-id');
```

#### Audio Tasks

```php
// Text to Speech
$audio = Bytez::textToSpeech('model-id', 'Text to convert to speech');

// Text to Audio
$audio = Bytez::textToAudio('model-id', 'Text to convert to audio');
```

#### Multimodal Tasks

```php
// Document Question Answering
$answer = Bytez::answerDocumentQuestion('model-id', 'What is the main topic?', $documentData);

// Visual Question Answering
$answer = Bytez::answerVisualQuestion('model-id', 'What color is the car?', $imageData);
```

### Listing Models and Tasks

```php
// List all models
$models = Bytez::listModels();

// Filter models
$chatModels = Bytez::listModels(['task' => 'text-generation']);

// List tasks
$tasks = Bytez::listTasks();

// List running models
$running = Bytez::listRunningModels();
```

### Error Handling

The package includes specific exceptions:

```php
use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Exceptions\BytezRateLimitException;
use Bytez\LaravelBytez\Exceptions\BytezTimeoutException;

try {
    $response = Bytez::chat('model-id')->message('Hello');
} catch (BytezRateLimitException $e) {
    // Handle rate limits
    $retryAfter = $e->getRetryAfter(); // ?int, may be null
} catch (BytezTimeoutException $e) {
    // Handle timeouts
} catch (BytezException $e) {
    // Handle other errors
}
```

### Cache

GET requests made through `BytezClient::get()` (which `listModels()`, `listTasks()`, `listRunningModels()`, `TaskBuilder::getModels()` and `info()` use) are cached in your application's default cache store for 60 seconds:

```php
$models = Bytez::listModels(); // cached for 60 seconds

$client = Bytez::getClient();

// Choose the TTL (in seconds) for one call
$models = $client->get('/models/v2/models', [], null, 300);

// Bypass the cache: request() does not use the cache unless asked to
$models = $client->request('GET', '/models/v2/models');

// Clear one cached key (the key is 'bytez:' followed by an md5 hash);
// with no argument, flushes the 'bytez' tag when the cache driver supports tags
$client->clearCache();
```

Note: the `cache.enabled`, `cache.ttl` and `cache.prefix` options of `config/bytez.php` are not wired to the client yet. POST requests (model runs) are never cached.

### Retry Logic

- Server errors (5xx) and network errors are retried up to `retry_attempts` times in total, waiting `retry_delay` ms multiplied by the attempt number (linear backoff).
- Client errors (4xx) are not retried.
- A rate limit response (429) is not retried: after a short wait the package throws `BytezRateLimitException`.

### Real-World Examples

#### Content Moderation

```php
// Check if content is safe
$moderation = Bytez::zeroShotClassify(
    'facebook/bart-large-mnli',
    $userContent,
    ['safe', 'spam', 'hate_speech', 'violence']
);

if ($moderation['output'][0]['label'] !== 'safe') {
    // Flag for review
}
```

#### Product Image Analysis

```php
// Analyze product images
$imageData = base64_encode(file_get_contents('product.jpg'));

// Get description
$description = Bytez::imageToText(
    'Salesforce/blip-image-captioning-base',
    $imageData,
    'Describe this product'
);

// Detect objects
$objects = Bytez::zeroShotDetectObjects(
    'facebook/detr-resnet-50',
    $imageData,
    ['product', 'person', 'background']
);

// Classify category
$category = Bytez::zeroShotClassifyImage(
    'openai/clip-vit-base-patch32',
    $imageData,
    ['electronics', 'clothing', 'food', 'furniture']
);
```

#### Multi-language Support

```php
// Translate content to multiple languages
$text = "Hello, welcome to our store!";
$languages = ['fr', 'es', 'de', 'it'];

$translations = [];
foreach ($languages as $lang) {
    $model = match($lang) {
        'fr' => 'Helsinki-NLP/opus-mt-en-fr',
        'es' => 'Helsinki-NLP/opus-mt-en-es',
        'de' => 'Helsinki-NLP/opus-mt-en-de',
        'it' => 'Helsinki-NLP/opus-mt-en-it',
    };
    
    $translations[$lang] = Bytez::translate($model, $text, $lang)['output'];
}
```

#### Batch Processing

```php
// Process multiple items efficiently
$articles = ['Article 1...', 'Article 2...', 'Article 3...'];

$summaries = [];
foreach ($articles as $id => $article) {
    $summaries[$id] = Bytez::summarize(
        'facebook/bart-large-cnn',
        $article
    )['output'];
}
```

#### Semantic Search

```php
// Create embeddings for search
$queryEmbedding = Bytez::extractFeatures(
    'sentence-transformers/all-MiniLM-L6-v2',
    'search query'
)['output'];

$documentEmbedding = Bytez::extractFeatures(
    'sentence-transformers/all-MiniLM-L6-v2',
    'document text'
)['output'];

// Calculate similarity with your own helper (not provided by the package)
$similarity = cosineSimilarity($queryEmbedding, $documentEmbedding);
```

#### Audio Processing Pipeline

```php
// Transcribe audio
$audioData = base64_encode(file_get_contents('recording.mp3'));
$transcription = Bytez::audio('openai/whisper')
    ->withProviderKey(env('OPENAI_API_KEY'))
    ->transcribe($audioData)['output'];

// Summarize transcription
$summary = Bytez::summarize(
    'facebook/bart-large-cnn',
    $transcription
)['output'];

// Extract key entities
$entities = Bytez::classifyTokens(
    'dbmdz/bert-large-cased-finetuned-conll03-english',
    $transcription
)['output'];
```

#### Advanced Chat with Context

```php
// Chat with memory and context
$conversation = Bytez::chatCompletions('openai/gpt-4')
    ->system('You are a helpful assistant with access to product database.')
    ->user('What products do you have?')
    ->assistant('We have electronics, clothing, and books.')
    ->user('Tell me about electronics')
    ->maxTokens(200)
    ->temperature(0.7)
    ->create();

$response = $conversation['choices'][0]['message']['content'];
```

#### Image Generation with Parameters

```php
// Generate image with specific parameters
$image = Bytez::textToImage('stability-ai/stable-diffusion')
    ->generate('A futuristic city at sunset', [
        'width' => 1024,
        'height' => 1024,
        'num_inference_steps' => 50,
        'guidance_scale' => 7.5
    ]);

// Save generated image
file_put_contents('generated.jpg', base64_decode($image['output']));
```

#### Question Answering with Documents

```php
// Answer questions from document
$document = base64_encode(file_get_contents('document.pdf'));

$answer = Bytez::answerDocumentQuestion(
    'impira/layoutlm-document-qa',
    'What is the main topic discussed?',
    $document
)['output'];
```

#### Sentiment Analysis for Reviews

```php
// Analyze customer reviews
$reviews = [
    'Great product, highly recommend!',
    'Not what I expected, poor quality',
    'Average product, nothing special'
];

$sentiments = [];
foreach ($reviews as $review) {
    $result = Bytez::zeroShotClassify(
        'facebook/bart-large-mnli',
        $review,
        ['positive', 'negative', 'neutral']
    );
    
    $sentiments[] = [
        'review' => $review,
        'sentiment' => $result['output'][0]['label'],
        'confidence' => $result['output'][0]['score']
    ];
}
```

## 🧪 Tests

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

Run only unit tests:

```bash
composer test:unit
```

The tests are unit tests that mock the HTTP layer: they do not call the Bytez API and need no API key.

## 📋 Changelog

See [CHANGELOG.md](CHANGELOG.md).

## 📝 License

MIT License - see [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) and our [Code of Conduct](CODE_OF_CONDUCT.md).

## 🔒 Security

If you discover a security vulnerability, please email contact@doganddev.eu instead of using the issue tracker. See [SECURITY.md](SECURITY.md) for more information.

## 📬 Contact

Dog and Dev / Martin Lechene: contact@doganddev.eu

## 🔗 Links

- [Bytez Documentation](https://docs.bytez.com)
- [API Dashboard](https://dashboard.bytez.com)
- [GitHub Repository](https://github.com/martin-lechene/bytez-laravel)
- [Issue Tracker](https://github.com/martin-lechene/bytez-laravel/issues)
- [More examples](EXAMPLES.md) and [package structure](PACKAGE_STRUCTURE.md)