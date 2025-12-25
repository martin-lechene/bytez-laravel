# Package Structure

This document describes the structure of the Bytez Laravel Package.

## Directory Structure

```
bytez-laravel/
├── config/
│   └── bytez.php              # Configuration file
├── src/
│   ├── Builders/
│   │   ├── ChatCompletionsBuilder.php    # OpenAI-compatible chat completions
│   │   ├── CompletionsBuilder.php        # OpenAI-compatible text completions
│   │   ├── ModelBuilder.php              # Generic model builder
│   │   └── TaskBuilder.php               # Task-based model builder
│   ├── Exceptions/
│   │   ├── BytezException.php            # Base exception
│   │   ├── BytezRateLimitException.php   # Rate limit exception
│   │   └── BytezTimeoutException.php     # Timeout exception
│   ├── Facades/
│   │   └── Bytez.php                     # Laravel Facade
│   ├── Models/
│   │   ├── AudioModel.php                # Audio model handler
│   │   ├── BaseModel.php                 # Base model class
│   │   ├── ChatModel.php                 # Chat model handler
│   │   ├── ImageModel.php                # Image model handler
│   │   ├── LanguageModel.php             # Language model handler
│   │   ├── MultimodalModel.php           # Multimodal model handler
│   │   ├── TextToImageModel.php          # Text-to-image model handler
│   │   └── VideoModel.php                # Video model handler
│   ├── Services/
│   │   ├── BytezClient.php               # Main HTTP client
│   │   ├── BytezManager.php             # Main service manager
│   │   └── OpenAIClient.php             # OpenAI-compatible client
│   └── BytezServiceProvider.php          # Laravel Service Provider
├── tests/
│   ├── Unit/
│   │   ├── Builders/
│   │   │   ├── ChatCompletionsBuilderTest.php
│   │   │   └── ModelBuilderTest.php
│   │   ├── Models/
│   │   │   └── ChatModelTest.php
│   │   ├── BytezClientTest.php
│   │   ├── BytezManagerHelpersTest.php
│   │   ├── BytezManagerTest.php
│   │   └── Services/
│   │       └── OpenAIClientTest.php
│   └── TestCase.php
├── .gitattributes
├── .gitignore
├── CHANGELOG.md
├── composer.json
├── CONTRIBUTING.md
├── EXAMPLES.md
├── LICENSE
├── phpunit.xml
├── README.md
└── SECURITY.md
```

## Core Components

### Services

- **BytezClient**: Main HTTP client for Bytez API with retry logic, caching, and error handling
- **OpenAIClient**: Specialized client for OpenAI-compatible endpoints with streaming support
- **BytezManager**: Main service manager providing access to all models and helpers

### Models

Each model type has its own class extending `BaseModel`:
- **ChatModel**: Text conversations and chat completions
- **ImageModel**: Image classification, object detection, feature extraction
- **VideoModel**: Video processing and classification
- **AudioModel**: Audio transcription, generation, classification
- **LanguageModel**: Text processing, embeddings, NLP tasks
- **MultimodalModel**: Multi-input processing
- **TextToImageModel**: Text-to-image generation

### Builders

- **ChatCompletionsBuilder**: OpenAI-compatible chat completions with all parameters
- **CompletionsBuilder**: OpenAI-compatible text completions
- **ModelBuilder**: Generic model execution builder
- **TaskBuilder**: Task-based model discovery and execution

### Helpers

The `BytezManager` provides 27+ helper methods for common tasks:
- Text: summarize, translate, answerQuestion, extractFeatures, etc.
- Image: imageToText, zeroShotDetectObjects, segmentImage, etc.
- Audio: textToSpeech, textToAudio
- Multimodal: answerDocumentQuestion, answerVisualQuestion

## Features

✅ OpenAI-compatible endpoints  
✅ Streaming support  
✅ Automatic provider key detection  
✅ Retry logic with exponential backoff  
✅ Rate limit handling  
✅ Caching support  
✅ Comprehensive error handling  
✅ 40+ task-specific helpers  
✅ Full test coverage  
✅ Extensive documentation  

## Testing

Run tests with:
```bash
composer test
composer test:coverage
composer test:unit
```

## Documentation

- **README.md**: Main documentation with quick start and examples
- **EXAMPLES.md**: 30+ real-world examples and use cases
- **CHANGELOG.md**: Version history and changes
- **CONTRIBUTING.md**: Contribution guidelines
- **SECURITY.md**: Security policy and best practices
