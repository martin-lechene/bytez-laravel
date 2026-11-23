# Contributing to Bytez Laravel Package

Thank you for considering contributing to the Bytez Laravel Package! This document provides guidelines and instructions for contributing. By participating, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/bytez-laravel.git`
3. Install dependencies: `composer install`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## Development Setup

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test:coverage
```

## Code Style

- Follow PSR-12 coding standards
- Use type hints where possible
- Add PHPDoc comments for all public methods
- Keep methods focused and single-purpose

## Testing

- Write tests for all new features
- Ensure all tests pass: `composer test`
- Aim for high test coverage
- Test both success and error cases

## Pull Request Process

1. Ensure your code follows the project's code style
2. Update documentation if needed
3. Add tests for new features
4. Update CHANGELOG.md with your changes
5. Ensure all tests pass
6. Submit a pull request with a clear description

## Commit Messages

Use clear, descriptive commit messages:
- `feat: Add new feature`
- `fix: Fix bug in X`
- `docs: Update documentation`
- `test: Add tests for X`
- `refactor: Refactor X component`

## Reporting Issues

When reporting issues, please include:
- Laravel version
- PHP version
- Package version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages or logs

## Feature Requests

For feature requests, please:
- Describe the use case
- Explain why it would be useful
- Provide examples if possible

Thank you for contributing! 🎉
