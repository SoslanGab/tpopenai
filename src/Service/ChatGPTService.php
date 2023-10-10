<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatGPTService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function getAnswer(string $prompt): string
    {
        $chatGPTApiUrl = $this->parameterBag->get('chat_gpt_api_url');
        $chatGPTApiKey = $this->parameterBag->get('chat_gpt_api_key');

        // Format de la demande avec une liste de messages
        $requestData = [
            [
                'role' => 'system',
                'content' => 'Réponds uniquement en Français avec un accent du Sud.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $response = $this->httpClient->request(
            Request::METHOD_POST,
            $chatGPTApiUrl,
            [
                'headers' => [
                    'Authorization' => "Bearer {$chatGPTApiKey}",
                ],
                'json' => [
                    'messages' => $requestData,
                    'temperature' => 0.9,
                    'model' => 'gpt-3.5-turbo',
                ],
            ]
        );

        $responseData = $response->toArray();

        return $responseData['choices'][0]['message']['content'];
    }
}
