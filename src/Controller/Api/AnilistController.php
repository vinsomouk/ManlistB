<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AniListController extends AbstractController
{
    #[Route('/api/anilist', name: 'anilist_proxy', methods: ['POST'])]
    public function proxy(Request $request): JsonResponse
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('POST', 'https://graphql.anilist.co', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $request->getContent(),
            ]);

            return JsonResponse::fromJsonString($response->getContent());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}