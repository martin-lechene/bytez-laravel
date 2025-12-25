# Bytez Laravel Package

[![Latest Version](https://img.shields.io/packagist/v/martin-lechene/laravel-bytez.svg?style=flat-square)](https://packagist.org/packages/martin-lechene/laravel-bytez)
[![Total Downloads](https://img.shields.io/packagist/dt/martin-lechene/laravel-bytez.svg?style=flat-square)](https://packagist.org/packages/martin-lechene/laravel-bytez)
[![License](https://img.shields.io/packagist/l/martin-lechene/laravel-bytez.svg?style=flat-square)](https://packagist.org/packages/martin-lechene/laravel-bytez)

A complete Laravel package for integrating the Bytez API, allowing you to use 175k+ AI models (open-source and closed-source) with a single API key.

## 🚀 Installation

```bash
composer require martin-lechene/laravel-bytez
```

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
    ],
    'provider_keys' => [
        'openai' => env('OPENAI_API_KEY'),
        'anthropic' => env('ANTHROPIC_API_KEY'),
    ],
];
```

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
    $retryAfter = $e->getRetryAfter();
} catch (BytezTimeoutException $e) {
    // Handle timeouts
} catch (BytezException $e) {
    // Handle other errors
}
```

### Cache

The package supports automatic caching for GET requests:

```php
// Cache is enabled by default for GET requests
$models = Bytez::listModels(); // Automatically cached

// Disable cache for a specific request
$client = Bytez::getClient();
$models = $client->get('/models/v2/models', [], null, null); // No cache
```

### Retry Logic

The package includes automatic retry logic:

- Automatic retry on server errors (5xx)
- Retry with exponential backoff
- Special handling for rate limits (429)
- Configuration via `retry_attempts` and `retry_delay`

## 🧪 Tests

```bash
composer test
```

## 📝 License

MIT

## 🤝 Contributing

Contributions are welcome! Feel free to open an issue or pull request.

## 🔗 Links

- [Bytez Documentation](https://docs.bytez.com)
- [API Dashboard](https://dashboard.bytez.com)
