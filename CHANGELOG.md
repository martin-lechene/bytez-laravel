# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- GitHub Actions workflow running the tests on PHP 8.2-8.4 and Laravel 10-13, plus `composer validate --strict`
- `CODE_OF_CONDUCT.md`, issue and pull request templates, Dependabot configuration (Composer and GitHub Actions)
- `support`, `homepage` and `config.sort-packages` in `composer.json`

### Changed
- Widened the supported versions to PHP ^8.2 and Laravel 10 to 13 (previously PHP ^8.4 and Laravel ^12 only); Orchestra Testbench ^8 to ^11 and PHPUnit ^10.5 to ^12 for development
- Removed `minimum-stability: dev` from `composer.json`
- Contact address changed to contact@doganddev.eu
- README: unofficial/beta notice, VCS installation instructions, requirements, configuration table, corrected cache and retry documentation
- Completed `.gitattributes` export-ignore rules and `.gitignore`

### Fixed
- Service provider injected the cache manager instead of a cache repository into `BytezClient`, which raised a `TypeError` when resolving the client
- `TaskBuilder::withProviderKey()` now accepts `null`, so the `Bytez::summarize()`-style helpers no longer fail when no provider key is given
- `ChatModel::stream()` uses an explicit nullable `callable` type (implicit nullable is deprecated in PHP 8.4)
- Cache unit test no longer relies on a mocked facade and now checks that a second GET is served from the cache

## [1.0.0] - initial development (never tagged)

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
