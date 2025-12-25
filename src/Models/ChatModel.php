<?php

namespace Bytez\LaravelBytez\Models;

use Bytez\LaravelBytez\Services\BytezClient;

class ChatModel extends BaseModel
{
    /**
     * Run the chat model with a message or conversation.
     */
    public function run($input, array $parameters = []): array
    {
        $parameters = $this->mergeParameters($parameters);

        $data = [
            'input' => $input,
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        return $this->client->post(
            "/models/v2/{$this->modelId}/run",
            $data,
            $this->providerKey
        );
    }

    /**
     * Send a single message.
     */
    public function message(string $message, array $parameters = []): array
    {
        return $this->run($message, $parameters);
    }

    /**
     * Send a conversation (array of messages).
     */
    public function conversation(array $messages, array $parameters = []): array
    {
        return $this->run($messages, $parameters);
    }

    /**
     * Stream chat responses (if supported by the model).
     * 
     * @deprecated Use Bytez::chatCompletions() with stream() for OpenAI-compatible streaming
     */
    public function stream(string $message, array $parameters = [], callable $callback = null): \Generator
    {
        $parameters = $this->mergeParameters($parameters);
        $parameters['stream'] = true;

        $data = [
            'input' => $message,
            'parameters' => $parameters,
        ];

        // Note: Streaming implementation would require special handling
        // This is a placeholder for the streaming interface
        $response = $this->client->post(
            "/models/v2/{$this->modelId}/run",
            $data,
            $this->providerKey
        );

        yield $response;
    }
}
