# Usage Examples

This file contains practical examples of using the Bytez Laravel package.

## Basic Examples

### OpenAI-Compatible Chat Completions (Recommended)

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Basic chat completion
$response = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->system('You are a helpful assistant')
    ->user('What is the capital of France?')
    ->maxTokens(150)
    ->temperature(0.7)
    ->create();

echo $response['choices'][0]['message']['content'];

// With closed-source model (provider key auto-detected)
$response = Bytez::chatCompletions('openai/gpt-4')
    ->system('You are a friendly chatbot')
    ->user('Hello!')
    ->maxTokens(100)
    ->create();

echo $response['choices'][0]['message']['content'];
```

### Streaming Chat Completions

```php
use Bytez\LaravelBytez\Facades\Bytez;

$stream = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->user('Tell me a short story about space')
    ->maxTokens(200)
    ->stream()
    ->create();

$fullText = '';
foreach ($stream as $chunk) {
    if (isset($chunk['choices'][0]['delta']['content'])) {
        $content = $chunk['choices'][0]['delta']['content'];
        echo $content;
        $fullText .= $content;
    }
    
    // Check for finish reason
    if (isset($chunk['choices'][0]['finish_reason'])) {
        $reason = $chunk['choices'][0]['finish_reason'];
        echo "\n\nFinished: {$reason}\n";
        break;
    }
}
```

### Full Conversation with OpenAI Format

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->system('You are a helpful assistant')
    ->user('Hello!')
    ->assistant('Hi there! How can I help you?')
    ->user('What is Laravel?')
    ->maxTokens(200)
    ->create();

echo $response['choices'][0]['message']['content'];
```

### Legacy Bytez API (Alternative)

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Using legacy API
$response = Bytez::chat('openai-community/gpt-2')
    ->message('Hello, how are you?');

echo $response['output'];
```

## Advanced Examples

### OpenAI-Compatible with All Parameters

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::chatCompletions('openai/gpt-4')
    ->messages([
        ['role' => 'system', 'content' => 'You are a creative writer'],
        ['role' => 'user', 'content' => 'Write a haiku about coding'],
    ])
    ->maxTokens(50)
    ->temperature(0.8)
    ->topP(0.9)
    ->frequencyPenalty(0.3)
    ->presencePenalty(0.3)
    ->logprobs(5)
    ->stop(['\n\n'])
    ->n(1)
    ->userId('user-123')
    ->create();

echo $response['choices'][0]['message']['content'];

// Access logprobs if requested
if (isset($response['choices'][0]['logprobs'])) {
    print_r($response['choices'][0]['logprobs']);
}
```

### Text Completions (OpenAI-compatible)

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::completions('Qwen/Qwen3-4B', 'The weather today is')
    ->maxTokens(50)
    ->temperature(0.7)
    ->topP(0.9)
    ->logprobs(3)
    ->echo(true)
    ->create();

echo $response['choices'][0]['text'];
```

### Using the Builder Pattern (Legacy)

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::model('openai-community/gpt-2')
    ->withProviderKey('your-provider-key')
    ->withParameter('temperature', 0.7)
    ->withParameter('max_tokens', 150)
    ->withParameter('top_p', 0.9)
    ->run('Tell me a short story');
```

### Image Generation

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::textToImage('stability-ai/stable-diffusion')
    ->generate('A sunset over the ocean with vibrant colors');

// The image is usually in $response['output'] or $response['image']
```

### Image Classification

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

$imageData = Storage::get('path/to/image.jpg');
$base64Image = base64_encode($imageData);

$response = Bytez::image('google/vit-base-patch16-224')
    ->classify($base64Image);

print_r($response['output']);
```

### Audio Transcription

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

$audioData = Storage::get('path/to/audio.mp3');
$base64Audio = base64_encode($audioData);

$response = Bytez::audio('openai/whisper')
    ->withProviderKey(env('OPENAI_API_KEY'))
    ->transcribe($base64Audio);

echo $response['output'];
```

### Text Embeddings

```php
use Bytez\LaravelBytez\Facades\Bytez;

$response = Bytez::language('sentence-transformers/all-MiniLM-L6-v2')
    ->embed('This is text to transform into an embedding');

$embedding = $response['output'];
// $embedding is an array of numbers representing the vector
```

### Usage in a Controller

```php
namespace App\Http\Controllers;

use Bytez\LaravelBytez\Services\BytezManager;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected BytezManager $bytez
    ) {}

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model' => 'nullable|string',
        ]);

        $modelId = $request->input('model', 'openai-community/gpt-2');
        
        try {
            $response = $this->bytez->chat($modelId)
                ->message($request->input('message'));

            return response()->json([
                'success' => true,
                'response' => $response['output'],
            ]);
        } catch (\Bytez\LaravelBytez\Exceptions\BytezException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### Error Handling

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Exceptions\BytezRateLimitException;
use Bytez\LaravelBytez\Exceptions\BytezTimeoutException;

try {
    $response = Bytez::chat('openai-community/gpt-2')
        ->message('Hello');
} catch (BytezRateLimitException $e) {
    // Rate limit reached
    $retryAfter = $e->getRetryAfter();
    logger()->warning("Rate limit reached. Retry after {$retryAfter} seconds");
    
    // Optional: wait and retry
    sleep($retryAfter);
    $response = Bytez::chat('openai-community/gpt-2')
        ->message('Hello');
} catch (BytezTimeoutException $e) {
    // Timeout
    logger()->error("Timeout: " . $e->getMessage());
} catch (BytezException $e) {
    // Other error
    logger()->error("Bytez error: " . $e->getMessage());
    throw $e;
}
```

### Model Discovery

```php
use Bytez\LaravelBytez\Facades\Bytez;

// List all models
$allModels = Bytez::listModels();

// Filter by task
$chatModels = Bytez::listModels(['task' => 'text-generation']);

// List available tasks
$tasks = Bytez::listTasks();

// Use a Task Builder
$models = Bytez::task('text-generation')
    ->getModels();
```

### Custom Cache Usage

```php
use Bytez\LaravelBytez\Facades\Bytez;

$client = Bytez::getClient();

// Request with cache (60 seconds)
$models = $client->get('/models/v2/models', [], null, 60);

// Request without cache
$models = $client->get('/models/v2/models', [], null, null);

// Clear cache
$client->clearCache();
```

### Multi-Environment Configuration

In your `.env`:

```env
# Production
BYTEZ_API_KEY=prod-key-here
BYTEZ_CACHE_ENABLED=true
BYTEZ_CACHE_TTL=3600

# Development
BYTEZ_API_KEY=dev-key-here
BYTEZ_CACHE_ENABLED=false
```

Or in `config/bytez.php`:

```php
return [
    'api_key' => env('BYTEZ_API_KEY'),
    'cache' => [
        'enabled' => env('APP_ENV') === 'production',
        'ttl' => env('BYTEZ_CACHE_TTL', 3600),
    ],
];
```
