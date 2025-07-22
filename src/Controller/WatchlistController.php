<?php
// src/Controller/WatchlistController.php

namespace App\Controller;

use App\Entity\Watchlist;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/watchlist')]
class WatchlistController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(
    EntityManagerInterface $em, 
    ValidatorInterface $validator,
    LoggerInterface $logger
) {
    $this->em = $em;
    $this->validator = $validator;
    $this->logger = $logger;
}

    #[Route('', name: 'watchlist_get', methods: ['GET'])]
public function getWatchlist(#[CurrentUser] ?User $user): JsonResponse
{
    // Vérifiez l'authentification
    if (!$user) {
        return $this->json(['error' => 'Authentication required'], 401);
    }

    try {
        $watchlist = $this->em->getRepository(Watchlist::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        // Version debug simplifiée :
        return $this->json([
            'status' => 'success',
            'user' => $user->getId(),
            'watchlist_count' => count($watchlist),
            'data' => array_map(function ($item) {
                return [
                    'id' => $item->getId(),
                    'animeId' => $item->getAnimeId()
                ];
            }, $watchlist)
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString() // Temporaire pour le debug
        ], 500);
    }
}

    #[Route('', name: 'watchlist_add', methods: ['POST'])]
public function addToWatchlist(
    #[CurrentUser] User $user,
    Request $request
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    // Validation des données
    $constraints = new Assert\Collection([
        'fields' => [
            'animeId' => [new Assert\NotBlank(), new Assert\Type('numeric')],
            'status' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices' => ['WATCHING', 'COMPLETED', 'ON_HOLD', 'DROPPED', 'PLANNED'],
                    'message' => 'Invalid status'
                ])
            ],
            'progress' => [new Assert\Optional([new Assert\Type('numeric')])]
        ],
        'allowExtraFields' => false
    ]);

    $errors = $this->validator->validate($data, $constraints);
    if (count($errors) > 0) {
        return $this->json(['errors' => (string) $errors], 400);
    }

        // Vérifier si l'anime est déjà dans la watchlist
        $existingItem = $this->em->getRepository(Watchlist::class)->findOneBy([
            'user' => $user,
            'animeId' => $data['animeId']
        ]);

        if ($existingItem) {
            return $this->json(['error' => 'This anime is already in your watchlist'], 409);
        }

        // Créer un nouvel item
        $item = new Watchlist();
        $item->setUser($user)
            ->setAnimeId($data['animeId'])
            ->setStatus($data['status'])
            ->setProgress($data['progress'] ?? 0);

        $this->em->persist($item);
        $this->em->flush();

        return $this->json([
            'id' => $item->getId(),
            'animeId' => $item->getAnimeId(),
            'status' => $item->getStatus()
        ], 201);
    }

    #[Route('/{animeId}', name: 'watchlist_update', methods: ['PUT'])]
    public function updateWatchlistItem(
        #[CurrentUser] User $user,
        int $animeId,
        Request $request
    ): JsonResponse {
        $item = $this->em->getRepository(Watchlist::class)->findOneBy([
            'user' => $user,
            'animeId' => $animeId
        ]);

        if (!$item) {
            return $this->json(['error' => 'Watchlist item not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validation
        $constraints = new Assert\Collection([
            'status' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['WATCHING', 'COMPLETED', 'ON_HOLD', 'DROPPED', 'PLANNED']
                    ])
                ])
            ],
            'progress' => [new Assert\Optional([new Assert\Type('numeric')])],
            'score' => [new Assert\Optional([new Assert\Range(['min' => 0, 'max' => 100])])]
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        // Mise à jour
        if (isset($data['status'])) {
            $item->setStatus($data['status']);
        }
        if (isset($data['progress'])) {
            $item->setProgress($data['progress']);
        }
        if (isset($data['score'])) {
            $item->setScore($data['score']);
        }
        if (isset($data['notes'])) {
            $item->setNotes($data['notes']);
        }

        $item->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->json([
            'id' => $item->getId(),
            'animeId' => $item->getAnimeId(),
            'status' => $item->getStatus(),
            'progress' => $item->getProgress()
        ]);
    }

    #[Route('/{animeId}', name: 'watchlist_remove', methods: ['DELETE'])]
    public function removeFromWatchlist(
        #[CurrentUser] User $user,
        int $animeId
    ): JsonResponse {
        $item = $this->em->getRepository(Watchlist::class)->findOneBy([
            'user' => $user,
            'animeId' => $animeId
        ]);

        if (!$item) {
            return $this->json(['error' => 'Watchlist item not found'], 404);
        }

        $this->em->remove($item);
        $this->em->flush();

        return $this->json(null, 204);
    }

    #[Route('/statuses', name: 'watchlist_statuses', methods: ['GET'])]
    public function getAvailableStatuses(): JsonResponse
    {
        return $this->json([
            'statuses' => [
                'WATCHING' => 'En cours',
                'COMPLETED' => 'Terminé',
                'ON_HOLD' => 'En pause',
                'DROPPED' => 'Abandonné',
                'PLANNED' => 'Prévu'
            ]
        ]);
    }
}