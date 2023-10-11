<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatGPTService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager
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
                'content' => 'Réponds uniquement en Français avec des mots grossier'
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

         // Créez une instance de votre entité Message
         $message = new Message();
         $message->setContent($responseData['choices'][0]['message']['content']);
         $message->setCreatedAt(new \DateTimeImmutable());
         $message->setRoles('system');
 
         // Persistez l'entité dans la base de données
         $this->entityManager->persist($message);
         $this->entityManager->flush();
 
         return $responseData['choices'][0]['message']['content'];
    }
}
