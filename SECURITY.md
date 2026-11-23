# Security Policy

## Supported Versions

This package is in beta and has no published release yet. Security fixes are applied to the `main` branch and will be included in the next tagged release:

| Version | Supported          |
| ------- | ------------------ |
| `main` (beta, not yet tagged) | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please **do not** open a public issue. Instead, send an email to:

**contact@doganddev.eu**

Please include:
- A description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will respond within 48 hours and work with you to address the issue before making it public.

## Security Best Practices

When using this package:

1. **Never commit API keys** - Always use environment variables
2. **Use dedicated API keys** - Create separate keys for different environments
3. **Rotate keys regularly** - Change your API keys periodically
4. **Monitor usage** - Keep track of API usage for anomalies
5. **Use HTTPS** - Always use secure connections
6. **Validate input** - Always validate user input before sending to AI models
7. **Rate limiting** - Implement rate limiting in your application
8. **Error handling** - Don't expose sensitive information in error messages

## API Key Security

- Store API keys in `.env` file (never commit to version control)
- Use Laravel's config system to access keys
- Consider using a secrets management service for production
- Regularly audit who has access to your API keys

## Provider Keys

When using closed-source models:
- Provider keys are passed through Bytez but never stored
- Use dedicated provider keys for Bytez integration
- Monitor provider billing separately
- Rotate provider keys if compromised

Thank you for helping keep this package secure!
