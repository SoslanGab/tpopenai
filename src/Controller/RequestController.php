<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChatGPTType;
use App\Repository\MessageRepository;
use App\Service\ChatGPTService;
use Symfony\Component\HttpFoundation\Request;

class RequestController extends AbstractController
{

    #[Route('/chat', name: 'chat_gpt',  methods: ['GET', 'POST'])]
    public function chat(
        Request $request,
        ChatGPTService $chatGPTService, MessageRepository $messageRepository
    ): Response { {
            $answer = null;
            $messages = $messageRepository->findAll();
            $form = $this->createForm(ChatGPTType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $prompt = $form->get('prompt')->getData();
                $answer = $chatGPTService->getAnswer($prompt);
            }

            return $this->render('chat.html.twig', [
                'form' => $form->createView(),
                'messages' => $messages,
                'answer' => $answer,
            ]);
        }
    }

    #[Route('/request', name: 'app_request')]
    public function jsonPlaceholder(): Response
    {
        // Créez une instance du client HTTP Symfony
        $client = HttpClient::create();

        // Effectuez une requête GET vers JSONPlaceholder
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts/1');

        // Récupérez le contenu de la réponse au format JSON
        $data = $response->toArray();
        return $this->render('request/index.html.twig', [
            'data' => $data,
            'controller_name' => 'RequestController',
        ]);
    }
}
