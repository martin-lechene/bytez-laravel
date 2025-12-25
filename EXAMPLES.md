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

### Task-Specific Helpers

#### Text Tasks

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Summarization
$summary = Bytez::summarize('facebook/bart-large-cnn', 'Long article text...');
echo $summary['output'];

// Translation
$translated = Bytez::translate('Helsinki-NLP/opus-mt-en-fr', 'Hello, how are you?', 'fr');
echo $translated['output'];

// Question Answering
$answer = Bytez::answerQuestion(
    'deepset/roberta-base-squad2',
    'What is the capital of France?',
    'France is a country in Europe. Its capital is Paris.'
);
echo $answer['output'];

// Feature Extraction (Embeddings)
$features = Bytez::extractFeatures('sentence-transformers/all-MiniLM-L6-v2', 'Text to embed');
print_r($features['output']);

// Sentence Similarity
$similarity = Bytez::sentenceSimilarity(
    'sentence-transformers/all-MiniLM-L6-v2',
    'I love programming',
    'I enjoy coding'
);
echo $similarity['output'];

// Zero-shot Classification
$classification = Bytez::zeroShotClassify(
    'facebook/bart-large-mnli',
    'I love this movie!',
    ['positive', 'negative', 'neutral']
);
print_r($classification['output']);

// Text Classification
$classification = Bytez::classifyText('distilbert-base-uncased-finetuned-sst-2-english', 'I love this!');
print_r($classification['output']);

// Token Classification (NER)
$tokens = Bytez::classifyTokens('dbmdz/bert-large-cased-finetuned-conll03-english', 'John lives in New York');
print_r($tokens['output']);

// Fill Mask
$filled = Bytez::fillMask('bert-base-uncased', 'The capital of France is [MASK].');
print_r($filled['output']);
```

#### Multimodal Text Tasks

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

// Audio to Text
$audioData = Storage::get('path/to/audio.mp3');
$base64Audio = base64_encode($audioData);
$text = Bytez::audioTextToText('openai/whisper', $base64Audio);
echo $text['output'];

// Image to Text
$imageData = Storage::get('path/to/image.jpg');
$base64Image = base64_encode($imageData);
$text = Bytez::imageTextToText('Salesforce/blip-image-captioning-base', $base64Image, 'Describe this image');
echo $text['output'];

// Video to Text
$videoData = Storage::get('path/to/video.mp4');
$base64Video = base64_encode($videoData);
$text = Bytez::videoTextToText('model-id', $base64Video, 'What is happening in this video?');
echo $text['output'];
```

#### Image Tasks

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

$imageData = Storage::get('path/to/image.jpg');
$base64Image = base64_encode($imageData);

// Image to Text
$text = Bytez::imageToText('Salesforce/blip-image-captioning-base', $base64Image, 'What is in this image?');
echo $text['output'];

// Zero-shot Object Detection
$objects = Bytez::zeroShotDetectObjects(
    'facebook/detr-resnet-50',
    $base64Image,
    ['person', 'car', 'bicycle', 'dog']
);
print_r($objects['output']);

// Zero-shot Image Classification
$classification = Bytez::zeroShotClassifyImage(
    'openai/clip-vit-base-patch32',
    $base64Image,
    ['cat', 'dog', 'bird', 'car']
);
print_r($classification['output']);

// Extract Image Features
$features = Bytez::extractImageFeatures('google/vit-base-patch16-224', $base64Image);
print_r($features['output']);

// Image Segmentation
$segments = Bytez::segmentImage('facebook/detr-resnet-50-panoptic', $base64Image);
print_r($segments['output']);

// Depth Estimation
$depth = Bytez::estimateDepth('Intel/dpt-large', $base64Image);
print_r($depth['output']);

// Generate Mask
$mask = Bytez::generateMask('facebook/sam-vit-base', $base64Image);
print_r($mask['output']);

// Unconditional Image Generation
$image = Bytez::generateImageUnconditional('model-id');
print_r($image['output']);
```

#### Audio Tasks

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Text to Speech
$audio = Bytez::textToSpeech('microsoft/speecht5_tts', 'Hello, this is a test.');
// Save $audio['output'] as audio file

// Text to Audio
$audio = Bytez::textToAudio('model-id', 'Generate audio from this text.');
// Save $audio['output'] as audio file
```

#### Multimodal Tasks

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

// Document Question Answering
$documentData = Storage::get('path/to/document.pdf');
$base64Document = base64_encode($documentData);
$answer = Bytez::answerDocumentQuestion(
    'impira/layoutlm-document-qa',
    'What is the main topic?',
    $base64Document
);
echo $answer['output'];

// Visual Question Answering
$imageData = Storage::get('path/to/image.jpg');
$base64Image = base64_encode($imageData);
$answer = Bytez::answerVisualQuestion(
    'dandelin/vilt-b32-finetuned-vqa',
    'What color is the car?',
    $base64Image
);
echo $answer['output'];
```

### Using Model-Specific Methods

You can also use methods directly on model instances:

```php
// Language Model Methods
$language = Bytez::language('model-id');
$summary = $language->summarize('Long text...');
$translated = $language->translate('Hello', 'fr');
$answer = $language->answerQuestion('What is AI?', 'Context...');
$features = $language->extractFeatures('Text');
$similarity = $language->sentenceSimilarity('Text 1', 'Text 2');
$classification = $language->zeroShotClassify('Text', ['label1', 'label2']);

// Image Model Methods
$image = Bytez::image('model-id');
$text = $image->toText($imageData, 'Describe this');
$objects = $image->zeroShotDetectObjects($imageData, ['person', 'car']);
$classification = $image->zeroShotClassify($imageData, ['cat', 'dog']);
$features = $image->extractFeatures($imageData);
$segments = $image->segment($imageData);
$depth = $image->estimateDepth($imageData);
$mask = $image->generateMask($imageData);

// Audio Model Methods
$audio = Bytez::audio('model-id');
$speech = $audio->textToSpeech('Text to speech');
$audioOutput = $audio->textToAudio('Text to audio');

// Multimodal Model Methods
$multimodal = Bytez::multimodal('model-id');
$docAnswer = $multimodal->answerDocumentQuestion('Question?', $documentData);
$visualAnswer = $multimodal->answerVisualQuestion('Question?', $imageData);
```

### Real-World Use Cases

#### E-commerce Product Description Generator

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Generate product descriptions
$productName = "Wireless Bluetooth Headphones";
$features = "Noise cancelling, 30-hour battery, premium sound";

$description = Bytez::chatCompletions('Qwen/Qwen3-4B')
    ->system('You are a professional e-commerce copywriter. Write compelling product descriptions.')
    ->user("Create a product description for: {$productName}. Features: {$features}")
    ->maxTokens(200)
    ->temperature(0.8)
    ->create();

echo $description['choices'][0]['message']['content'];
```

#### Customer Support Chatbot

```php
use Bytez\LaravelBytez\Facades\Bytez;

class SupportController extends Controller
{
    public function handleSupport(Request $request)
    {
        $conversation = $request->input('conversation', []);
        $customerMessage = $request->input('message');
        
        // Build conversation history
        $messages = [];
        foreach ($conversation as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $customerMessage];
        
        $response = Bytez::chatCompletions('openai/gpt-4')
            ->system('You are a helpful customer support agent. Be friendly, professional, and solution-oriented.')
            ->messages($messages)
            ->maxTokens(300)
            ->temperature(0.7)
            ->create();
        
        return response()->json([
            'response' => $response['choices'][0]['message']['content'],
            'conversation_id' => $request->input('conversation_id')
        ]);
    }
}
```

#### Content Moderation System

```php
use Bytez\LaravelBytez\Facades\Bytez;

function moderateContent(string $text): array
{
    $result = Bytez::zeroShotClassify(
        'facebook/bart-large-mnli',
        $text,
        ['safe', 'spam', 'hate_speech', 'violence', 'harassment']
    );
    
    $labels = $result['output'];
    $highestScore = max(array_column($labels, 'score'));
    $category = $labels[array_search($highestScore, array_column($labels, 'score'))]['label'];
    
    return [
        'category' => $category,
        'score' => $highestScore,
        'is_safe' => $category === 'safe',
        'all_scores' => $labels
    ];
}

// Usage
$moderation = moderateContent('This is a great product!');
if (!$moderation['is_safe']) {
    // Flag for review
    logger()->warning('Content flagged', $moderation);
}
```

#### Multi-language Translation Service

```php
use Bytez\LaravelBytez\Facades\Bytez;

class TranslationService
{
    private array $languageModels = [
        'fr' => 'Helsinki-NLP/opus-mt-en-fr',
        'es' => 'Helsinki-NLP/opus-mt-en-es',
        'de' => 'Helsinki-NLP/opus-mt-en-de',
        'it' => 'Helsinki-NLP/opus-mt-en-it',
    ];
    
    public function translate(string $text, string $targetLang): string
    {
        if (!isset($this->languageModels[$targetLang])) {
            throw new \InvalidArgumentException("Unsupported language: {$targetLang}");
        }
        
        $result = Bytez::translate(
            $this->languageModels[$targetLang],
            $text,
            $targetLang
        );
        
        return $result['output'];
    }
    
    public function translateBatch(array $texts, string $targetLang): array
    {
        $translations = [];
        foreach ($texts as $text) {
            $translations[] = $this->translate($text, $targetLang);
        }
        return $translations;
    }
}

// Usage
$service = new TranslationService();
$french = $service->translate('Hello, how are you?', 'fr');
$spanish = $service->translate('Hello, how are you?', 'es');
```

#### Document Summarization Service

```php
use Bytez\LaravelBytez\Facades\Bytez;

class DocumentService
{
    public function summarizeArticle(string $article, int $maxLength = 150): string
    {
        $result = Bytez::summarize(
            'facebook/bart-large-cnn',
            $article,
            ['max_length' => $maxLength, 'min_length' => 50]
        );
        
        return $result['output'];
    }
    
    public function summarizeMultiple(array $articles): array
    {
        $summaries = [];
        foreach ($articles as $id => $article) {
            $summaries[$id] = $this->summarizeArticle($article);
        }
        return $summaries;
    }
}

// Usage
$service = new DocumentService();
$summary = $service->summarizeArticle('Very long article text...', 200);
```

#### Image Analysis for E-commerce

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

class ProductImageAnalyzer
{
    public function analyzeProductImage(string $imagePath): array
    {
        $imageData = Storage::get($imagePath);
        $base64Image = base64_encode($imageData);
        
        // Get image description
        $description = Bytez::imageToText(
            'Salesforce/blip-image-captioning-base',
            $base64Image,
            'Describe this product image in detail'
        );
        
        // Detect objects
        $objects = Bytez::zeroShotDetectObjects(
            'facebook/detr-resnet-50',
            $base64Image,
            ['product', 'person', 'background', 'text', 'logo']
        );
        
        // Classify product category
        $category = Bytez::zeroShotClassifyImage(
            'openai/clip-vit-base-patch32',
            $base64Image,
            ['electronics', 'clothing', 'food', 'furniture', 'books', 'toys']
        );
        
        return [
            'description' => $description['output'],
            'objects' => $objects['output'],
            'category' => $category['output'],
        ];
    }
}

// Usage
$analyzer = new ProductImageAnalyzer();
$analysis = $analyzer->analyzeProductImage('products/image.jpg');
```

#### Sentiment Analysis for Reviews

```php
use Bytez\LaravelBytez\Facades\Bytez;

class ReviewAnalyzer
{
    public function analyzeSentiment(string $review): array
    {
        $result = Bytez::zeroShotClassify(
            'facebook/bart-large-mnli',
            $review,
            ['positive', 'negative', 'neutral']
        );
        
        $labels = $result['output'];
        $sentiment = $labels[0]['label'];
        $confidence = $labels[0]['score'];
        
        return [
            'sentiment' => $sentiment,
            'confidence' => $confidence,
            'all_scores' => $labels
        ];
    }
    
    public function analyzeBatch(array $reviews): array
    {
        $results = [];
        foreach ($reviews as $id => $review) {
            $results[$id] = $this->analyzeSentiment($review);
        }
        return $results;
    }
}

// Usage
$analyzer = new ReviewAnalyzer();
$sentiment = $analyzer->analyzeSentiment('This product is amazing! Highly recommend.');
// Returns: ['sentiment' => 'positive', 'confidence' => 0.95, ...]
```

#### Named Entity Recognition (NER)

```php
use Bytez\LaravelBytez\Facades\Bytez;

class EntityExtractor
{
    public function extractEntities(string $text): array
    {
        $result = Bytez::classifyTokens(
            'dbmdz/bert-large-cased-finetuned-conll03-english',
            $text
        );
        
        $entities = [];
        foreach ($result['output'] as $token) {
            if ($token['entity'] !== 'O') { // 'O' means no entity
                $entities[] = [
                    'text' => $token['word'],
                    'entity' => $token['entity'],
                    'score' => $token['score'] ?? 1.0
                ];
            }
        }
        
        return $entities;
    }
    
    public function extractPeople(array $texts): array
    {
        $allPeople = [];
        foreach ($texts as $text) {
            $entities = $this->extractEntities($text);
            $people = array_filter($entities, fn($e) => str_contains($e['entity'], 'PER'));
            $allPeople = array_merge($allPeople, $people);
        }
        return $allPeople;
    }
}

// Usage
$extractor = new EntityExtractor();
$entities = $extractor->extractEntities('John Smith works at Microsoft in Seattle.');
// Returns entities like: John Smith (PERSON), Microsoft (ORG), Seattle (LOC)
```

#### Audio Transcription Service

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

class TranscriptionService
{
    public function transcribeAudio(string $audioPath, ?string $language = null): string
    {
        $audioData = Storage::get($audioPath);
        $base64Audio = base64_encode($audioData);
        
        $parameters = [];
        if ($language) {
            $parameters['language'] = $language;
        }
        
        $result = Bytez::audio('openai/whisper')
            ->withProviderKey(env('OPENAI_API_KEY'))
            ->transcribe($base64Audio, $parameters);
        
        return $result['output'];
    }
    
    public function transcribeMultiple(array $audioPaths): array
    {
        $transcriptions = [];
        foreach ($audioPaths as $path) {
            $transcriptions[$path] = $this->transcribeAudio($path);
        }
        return $transcriptions;
    }
}

// Usage
$service = new TranscriptionService();
$text = $service->transcribeAudio('recordings/meeting.mp3', 'en');
```

#### Search with Embeddings

```php
use Bytez\LaravelBytez\Facades\Bytez;

class SemanticSearch
{
    private array $documents = [];
    private array $embeddings = [];
    
    public function addDocument(string $id, string $text): void
    {
        $this->documents[$id] = $text;
        
        // Generate embedding
        $result = Bytez::extractFeatures(
            'sentence-transformers/all-MiniLM-L6-v2',
            $text
        );
        
        $this->embeddings[$id] = $result['output'];
    }
    
    public function search(string $query, int $topK = 5): array
    {
        // Get query embedding
        $queryResult = Bytez::extractFeatures(
            'sentence-transformers/all-MiniLM-L6-v2',
            $query
        );
        $queryEmbedding = $queryResult['output'];
        
        // Calculate similarities
        $similarities = [];
        foreach ($this->embeddings as $id => $embedding) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $embedding);
            $similarities[$id] = [
                'document' => $this->documents[$id],
                'similarity' => $similarity
            ];
        }
        
        // Sort by similarity
        arsort($similarities);
        
        return array_slice($similarities, 0, $topK, true);
    }
    
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = array_sum(array_map(fn($x, $y) => $x * $y, $a, $b));
        $normA = sqrt(array_sum(array_map(fn($x) => $x * $x, $a)));
        $normB = sqrt(array_sum(array_map(fn($x) => $x * $x, $b)));
        
        return $dotProduct / ($normA * $normB);
    }
}

// Usage
$search = new SemanticSearch();
$search->addDocument('doc1', 'Laravel is a PHP framework');
$search->addDocument('doc2', 'Python is a programming language');
$search->addDocument('doc3', 'Laravel provides elegant syntax');

$results = $search->search('PHP framework');
```

#### Batch Processing with Queue

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ProcessTextBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(
        private array $texts,
        private string $task,
        private string $modelId
    ) {}
    
    public function handle(): array
    {
        $results = [];
        
        foreach ($this->texts as $id => $text) {
            try {
                switch ($this->task) {
                    case 'summarize':
                        $results[$id] = Bytez::summarize($this->modelId, $text);
                        break;
                    case 'translate':
                        $results[$id] = Bytez::translate($this->modelId, $text, 'fr');
                        break;
                    case 'classify':
                        $results[$id] = Bytez::classifyText($this->modelId, $text);
                        break;
                }
            } catch (\Exception $e) {
                $results[$id] = ['error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
}

// Dispatch job
ProcessTextBatch::dispatch($texts, 'summarize', 'facebook/bart-large-cnn');
```

#### Streaming Response in Laravel Controller

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamingController extends Controller
{
    public function streamChat(Request $request)
    {
        $prompt = $request->input('message');
        
        return new StreamedResponse(function () use ($prompt) {
            $stream = Bytez::chatCompletions('Qwen/Qwen3-4B')
                ->user($prompt)
                ->stream()
                ->create();
            
            foreach ($stream as $chunk) {
                if (isset($chunk['choices'][0]['delta']['content'])) {
                    $content = $chunk['choices'][0]['delta']['content'];
                    echo "data: " . json_encode(['content' => $content]) . "\n\n";
                    flush();
                }
                
                if (isset($chunk['choices'][0]['finish_reason'])) {
                    echo "data: " . json_encode(['done' => true]) . "\n\n";
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
```

#### Image Similarity Search

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Storage;

class ImageSimilaritySearch
{
    private array $imageEmbeddings = [];
    
    public function addImage(string $id, string $imagePath): void
    {
        $imageData = Storage::get($imagePath);
        $base64Image = base64_encode($imageData);
        
        $result = Bytez::extractImageFeatures(
            'google/vit-base-patch16-224',
            $base64Image
        );
        
        $this->imageEmbeddings[$id] = [
            'path' => $imagePath,
            'embedding' => $result['output']
        ];
    }
    
    public function findSimilar(string $queryImagePath, int $topK = 5): array
    {
        $queryImageData = Storage::get($queryImagePath);
        $base64Query = base64_encode($queryImageData);
        
        $queryResult = Bytez::extractImageFeatures(
            'google/vit-base-patch16-224',
            $base64Query
        );
        $queryEmbedding = $queryResult['output'];
        
        $similarities = [];
        foreach ($this->imageEmbeddings as $id => $data) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $data['embedding']);
            $similarities[] = [
                'id' => $id,
                'path' => $data['path'],
                'similarity' => $similarity
            ];
        }
        
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return array_slice($similarities, 0, $topK);
    }
    
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = array_sum(array_map(fn($x, $y) => $x * $y, $a, $b));
        $normA = sqrt(array_sum(array_map(fn($x) => $x * $x, $a)));
        $normB = sqrt(array_sum(array_map(fn($x) => $x * $x, $b)));
        
        return $dotProduct / ($normA * $normB);
    }
}
```

#### Advanced Error Handling with Retry

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Bytez\LaravelBytez\Exceptions\BytezException;
use Bytez\LaravelBytez\Exceptions\BytezRateLimitException;

class RobustAIService
{
    public function processWithRetry(callable $operation, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $maxRetries) {
            try {
                return $operation();
            } catch (BytezRateLimitException $e) {
                $retryAfter = $e->getRetryAfter() ?? 60;
                logger()->warning("Rate limit hit, waiting {$retryAfter} seconds");
                sleep($retryAfter);
                $attempt++;
            } catch (BytezException $e) {
                if ($e->getCode() >= 500) {
                    // Server error, retry
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        sleep(pow(2, $attempt)); // Exponential backoff
                        continue;
                    }
                }
                throw $e;
            }
        }
        
        throw $lastException ?? new \RuntimeException('Max retries exceeded');
    }
    
    public function safeSummarize(string $text): ?string
    {
        try {
            $result = $this->processWithRetry(function () use ($text) {
                return Bytez::summarize('facebook/bart-large-cnn', $text);
            });
            
            return $result['output'] ?? null;
        } catch (\Exception $e) {
            logger()->error('Summarization failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
```

### Additional Advanced Examples

#### Chaining Multiple AI Operations

```php
use Bytez\LaravelBytez\Facades\Bytez;

// Process a document through multiple AI steps
class DocumentProcessor
{
    public function processDocument(string $documentPath): array
    {
        $document = file_get_contents($documentPath);
        
        // Step 1: Summarize
        $summary = Bytez::summarize('facebook/bart-large-cnn', $document)['output'];
        
        // Step 2: Extract entities
        $entities = Bytez::classifyTokens(
            'dbmdz/bert-large-cased-finetuned-conll03-english',
            $document
        )['output'];
        
        // Step 3: Analyze sentiment
        $sentiment = Bytez::zeroShotClassify(
            'facebook/bart-large-mnli',
            $summary,
            ['positive', 'negative', 'neutral']
        )['output'];
        
        // Step 4: Translate if needed
        $translated = Bytez::translate(
            'Helsinki-NLP/opus-mt-en-fr',
            $summary,
            'fr'
        )['output'];
        
        return [
            'summary' => $summary,
            'entities' => $entities,
            'sentiment' => $sentiment,
            'translated_summary' => $translated
        ];
    }
}
```

#### Dynamic Model Selection

```php
use Bytez\LaravelBytez\Facades\Bytez;

class SmartModelSelector
{
    private array $modelMap = [
        'short' => 'facebook/bart-large-cnn',
        'medium' => 'google/pegasus-xsum',
        'long' => 'facebook/bart-large-cnn',
    ];
    
    public function summarize(string $text): string
    {
        $length = strlen($text);
        $model = match(true) {
            $length < 500 => $this->modelMap['short'],
            $length < 2000 => $this->modelMap['medium'],
            default => $this->modelMap['long'],
        };
        
        return Bytez::summarize($model, $text)['output'];
    }
}
```

#### Caching AI Results

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Cache;

class CachedAIService
{
    public function getCachedTranslation(string $text, string $targetLang): string
    {
        $cacheKey = "translation:{$targetLang}:" . md5($text);
        
        return Cache::remember($cacheKey, 3600, function () use ($text, $targetLang) {
            $model = match($targetLang) {
                'fr' => 'Helsinki-NLP/opus-mt-en-fr',
                'es' => 'Helsinki-NLP/opus-mt-en-es',
                default => 'Helsinki-NLP/opus-mt-en-fr',
            };
            
            return Bytez::translate($model, $text, $targetLang)['output'];
        });
    }
}
```

#### Parallel Processing

```php
use Bytez\LaravelBytez\Facades\Bytez;
use Illuminate\Support\Facades\Parallel;

class ParallelProcessor
{
    public function processMultiple(array $texts): array
    {
        return Parallel::map($texts, function ($text) {
            return [
                'summary' => Bytez::summarize('facebook/bart-large-cnn', $text)['output'],
                'sentiment' => Bytez::zeroShotClassify(
                    'facebook/bart-large-mnli',
                    $text,
                    ['positive', 'negative', 'neutral']
                )['output'],
                'entities' => Bytez::classifyTokens(
                    'dbmdz/bert-large-cased-finetuned-conll03-english',
                    $text
                )['output']
            ];
        });
    }
}
```

#### Image Processing Pipeline

```php
use Bytez\LaravelBytez\Facades\Bytez;

class ImageAnalysisPipeline
{
    public function analyzeImage(string $imagePath): array
    {
        $imageData = base64_encode(file_get_contents($imagePath));
        
        // Run multiple analyses in parallel
        $results = [
            'description' => Bytez::imageToText(
                'Salesforce/blip-image-captioning-base',
                $imageData,
                'Describe this image in detail'
            )['output'],
            'objects' => Bytez::zeroShotDetectObjects(
                'facebook/detr-resnet-50',
                $imageData,
                ['person', 'vehicle', 'animal', 'building', 'nature']
            )['output'],
            'category' => Bytez::zeroShotClassifyImage(
                'openai/clip-vit-base-patch32',
                $imageData,
                ['indoor', 'outdoor', 'urban', 'nature', 'abstract']
            )['output'],
            'features' => Bytez::extractImageFeatures(
                'google/vit-base-patch16-224',
                $imageData
            )['output']
        ];
        
        return $results;
    }
}
```

#### Text Analysis Dashboard Data

```php
use Bytez\LaravelBytez\Facades\Bytez;

class TextAnalytics
{
    public function analyzeText(string $text): array
    {
        return [
            'word_count' => str_word_count($text),
            'char_count' => strlen($text),
            'sentiment' => Bytez::zeroShotClassify(
                'facebook/bart-large-mnli',
                $text,
                ['positive', 'negative', 'neutral']
            )['output'],
            'entities' => Bytez::classifyTokens(
                'dbmdz/bert-large-cased-finetuned-conll03-english',
                $text
            )['output'],
            'summary' => Bytez::summarize('facebook/bart-large-cnn', $text)['output'],
            'language' => $this->detectLanguage($text),
            'topics' => $this->extractTopics($text)
        ];
    }
    
    private function detectLanguage(string $text): string
    {
        // Use zero-shot classification for language detection
        $result = Bytez::zeroShotClassify(
            'facebook/bart-large-mnli',
            substr($text, 0, 100), // Sample
            ['english', 'french', 'spanish', 'german', 'italian']
        );
        
        return $result['output'][0]['label'] ?? 'unknown';
    }
    
    private function extractTopics(string $text): array
    {
        $result = Bytez::zeroShotClassify(
            'facebook/bart-large-mnli',
            $text,
            ['technology', 'sports', 'politics', 'entertainment', 'science', 'business']
        );
        
        return array_map(fn($item) => $item['label'], $result['output']);
    }
}
```

#### Multi-Environment Configuration

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
