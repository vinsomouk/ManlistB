<?php

namespace App\Controller;

use App\Repository\UserResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/user')]
class UserResponseController extends AbstractController
{
    #[Route('/{userId}/responses', name: 'user_responses', methods: ['GET'])]
    public function getUserResponses(
        int $userId,
        UserResponseRepository $repository,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        // Vérifier que l'utilisateur demande ses propres réponses
        if ($currentUser->getId() !== $userId) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $responses = $repository->findByUserId($userId);
        
        $data = array_map(function($response) {
            return [
                'id' => $response->getId(),
                'questionnaire' => [
                    'id' => $response->getQuestionnaire()->getId(),
                    'title' => $response->getQuestionnaire()->getTitle()
                ],
                'completedAt' => $response->getCompletedAt()->format('Y-m-d H:i:s')
            ];
        }, $responses);

        return $this->json($data);
    }
}