# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Bytez Laravel Package
- Support for all Bytez API model types (Chat, Image, Video, Audio, Multimodal, Language, Text-to-Image)
- Builder pattern for flexible model configuration
- Task builder for task-based model discovery
- Provider key support for closed-source models
- Automatic retry logic with exponential backoff
- Rate limit handling with automatic retry
- Cache support for GET requests
- Comprehensive exception handling (BytezException, BytezRateLimitException, BytezTimeoutException)
- Full test suite with PHPUnit
- Complete documentation and examples
- Multi-environment configuration support
- Facade for easy access
- Service Provider for Laravel integration
