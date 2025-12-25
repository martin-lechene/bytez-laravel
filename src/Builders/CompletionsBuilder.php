<?php

namespace Bytez\LaravelBytez\Builders;

use Bytez\LaravelBytez\Services\OpenAIClient;

class CompletionsBuilder
{
    protected OpenAIClient $client;
    protected string $model;
    protected string $prompt;
    protected ?string $providerKey = null;
    protected array $parameters = [];
    protected bool $stream = false;

    // OpenAI-specific parameters
    protected ?string $suffix = null;
    protected ?int $maxTokens = null;
    protected ?float $temperature = null;
    protected ?float $topP = null;
    protected ?int $n = null;
    protected ?array $stop = null;
    protected ?float $presencePenalty = null;
    protected ?float $frequencyPenalty = null;
    protected ?array $logitBias = null;
    protected ?string $user = null;
    protected ?int $logprobs = null;
    protected ?bool $echo = null;
    protected ?int $bestOf = null;

    public function __construct(OpenAIClient $client, string $model, string $prompt)
    {
        $this->client = $client;
        $this->model = $model;
        $this->prompt = $prompt;
    }

    /**
     * Set the provider key for closed-source models.
     */
    public function withProviderKey(string $providerKey): self
    {
        $this->providerKey = $providerKey;
        return $this;
    }

    /**
     * Set the prompt.
     */
    public function prompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Enable streaming.
     */
    public function stream(bool $stream = true): self
    {
        $this->stream = $stream;
        return $this;
    }

    /**
     * Set suffix parameter.
     */
    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Set max_tokens parameter.
     */
    public function maxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    /**
     * Set temperature parameter.
     */
    public function temperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * Set top_p parameter.
     */
    public function topP(float $topP): self
    {
        $this->topP = $topP;
        return $this;
    }

    /**
     * Set n parameter (number of completions).
     */
    public function n(int $n): self
    {
        $this->n = $n;
        return $this;
    }

    /**
     * Set stop sequences.
     */
    public function stop(array|string $stop): self
    {
        $this->stop = is_array($stop) ? $stop : [$stop];
        return $this;
    }

    /**
     * Set presence_penalty parameter.
     */
    public function presencePenalty(float $presencePenalty): self
    {
        $this->presencePenalty = $presencePenalty;
        return $this;
    }

    /**
     * Set frequency_penalty parameter.
     */
    public function frequencyPenalty(float $frequencyPenalty): self
    {
        $this->frequencyPenalty = $frequencyPenalty;
        return $this;
    }

    /**
     * Set logit_bias parameter.
     */
    public function logitBias(array $logitBias): self
    {
        $this->logitBias = $logitBias;
        return $this;
    }

    /**
     * Set user identifier.
     */
    public function userId(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set logprobs parameter.
     */
    public function logprobs(int $logprobs): self
    {
        $this->logprobs = $logprobs;
        return $this;
    }

    /**
     * Set echo parameter.
     */
    public function echo(bool $echo): self
    {
        $this->echo = $echo;
        return $this;
    }

    /**
     * Set best_of parameter.
     */
    public function bestOf(int $bestOf): self
    {
        $this->bestOf = $bestOf;
        return $this;
    }

    /**
     * Set custom parameters.
     */
    public function withParameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    /**
     * Create the request payload.
     */
    protected function buildPayload(): array
    {
        $payload = [
            'model' => $this->model,
            'prompt' => $this->prompt,
        ];

        if ($this->suffix !== null) {
            $payload['suffix'] = $this->suffix;
        }

        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        if ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }

        if ($this->topP !== null) {
            $payload['top_p'] = $this->topP;
        }

        if ($this->n !== null) {
            $payload['n'] = $this->n;
        }

        if ($this->stop !== null) {
            $payload['stop'] = $this->stop;
        }

        if ($this->presencePenalty !== null) {
            $payload['presence_penalty'] = $this->presencePenalty;
        }

        if ($this->frequencyPenalty !== null) {
            $payload['frequency_penalty'] = $this->frequencyPenalty;
        }

        if ($this->logitBias !== null) {
            $payload['logit_bias'] = $this->logitBias;
        }

        if ($this->user !== null) {
            $payload['user'] = $this->user;
        }

        if ($this->logprobs !== null) {
            $payload['logprobs'] = $this->logprobs;
        }

        if ($this->echo !== null) {
            $payload['echo'] = $this->echo;
        }

        if ($this->bestOf !== null) {
            $payload['best_of'] = $this->bestOf;
        }

        if ($this->stream) {
            $payload['stream'] = true;
        }

        // Merge custom parameters
        if (!empty($this->parameters)) {
            $payload = array_merge($payload, $this->parameters);
        }

        return $payload;
    }

    /**
     * Create completion.
     */
    public function create(): array|\Generator
    {
        $payload = $this->buildPayload();

        if ($this->stream) {
            return $this->client->request('POST', '/completions', $payload, $this->providerKey, true);
        }

        return $this->client->request('POST', '/completions', $payload, $this->providerKey, false);
    }
}
