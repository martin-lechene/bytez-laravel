# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Upgraded PHP requirement from ^8.2 to ^8.4
- Upgraded Laravel support to ^12.0 only (removed Laravel 11 support)
- Updated Orchestra Testbench to ^10.0 for Laravel 12 compatibility
- Added Composer scripts for testing (test, test:coverage, test:unit)

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Bytez Laravel Package
- Support for all Bytez API model types (Chat, Image, Video, Audio, Multimodal, Language, Text-to-Image)
- OpenAI-compatible endpoints for Chat Completions and Completions
- Full streaming support for chat completions with SSE
- Builder pattern for flexible model configuration
- Task builder for task-based model discovery
- Provider key support for closed-source models with automatic detection
- Support for free providers (openai, mistral, google) without provider keys
- Automatic retry logic with exponential backoff
- Rate limit handling with automatic retry
- Cache support for GET requests with configurable TTL
- Comprehensive exception handling (BytezException, BytezRateLimitException, BytezTimeoutException)
- Full test suite with PHPUnit
- Complete documentation with 50+ practical examples
- Multi-environment configuration support
- Facade for easy access
- Service Provider for Laravel integration

### Task-Specific Helpers Added
- Text Tasks: `summarize()`, `translate()`, `answerQuestion()`, `extractFeatures()`, `sentenceSimilarity()`, `zeroShotClassify()`, `classifyText()`, `classifyTokens()`, `fillMask()`
- Multimodal Text: `audioTextToText()`, `imageTextToText()`, `videoTextToText()`
- Image Tasks: `imageToText()`, `zeroShotDetectObjects()`, `zeroShotClassifyImage()`, `extractImageFeatures()`, `segmentImage()`, `estimateDepth()`, `generateMask()`, `generateImageUnconditional()`
- Audio Tasks: `textToSpeech()`, `textToAudio()`
- Multimodal Tasks: `answerDocumentQuestion()`, `answerVisualQuestion()`

### Model-Specific Methods Added
- `LanguageModel`: Added `summarize()`, `translate()`, `answerQuestion()`, `extractFeatures()`, `sentenceSimilarity()`, `zeroShotClassify()`, `classifyText()`, `classifyTokens()`, `fillMask()`
- `ImageModel`: Added `toText()`, `zeroShotDetectObjects()`, `zeroShotClassify()`, `extractFeatures()`, `segment()`, `estimateDepth()`, `generateMask()`
- `AudioModel`: Added `textToSpeech()`, `textToAudio()`
- `MultimodalModel`: Added `answerDocumentQuestion()`, `answerVisualQuestion()`

### OpenAI-Compatible Features
- Chat Completions with full parameter support (max_tokens, temperature, top_p, logprobs, etc.)
- Text Completions with all OpenAI parameters
- Streaming support with proper SSE handling
- Automatic provider key detection for closed-source models
- Support for all OpenAI message roles (system, user, assistant)
- Finish reason handling in streaming responses

### Documentation
- Comprehensive README with usage examples
- Detailed EXAMPLES.md with 30+ real-world use cases
- Examples for e-commerce, customer support, content moderation, translation services
- Advanced examples for batch processing, semantic search, image analysis
- Integration examples with Laravel queues, caching, and streaming
