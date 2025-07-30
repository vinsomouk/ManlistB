<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Watchlist;
use App\Entity\User;
use App\Service\AnimeSyncer;
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
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
        private AnimeSyncer $animeSyncer
    ) {}

    #[Route('', name: 'watchlist_get', methods: ['GET'])]
    public function getWatchlist(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Authentication required'], 401);
        }

        try {
            $watchlistItems = $this->em->getRepository(Watchlist::class)->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );

            $data = array_map(function (Watchlist $item) {
                $anime = $item->getAnime();
                return [
                    'animeId' => $anime->getId(),
                    'status' => $item->getStatus(),
                    'progress' => $item->getProgress(),
                    'score' => $item->getScore(),
                    'notes' => $item->getNotes(),
                    'animeTitle' => $anime->getTitle(),
                    'animeImage' => $anime->getImageUrl(),
                    'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $item->getUpdatedAt()?->format('Y-m-d H:i:s')
                ];
            }, $watchlistItems);

            return $this->json(['data' => $data]);

        } catch (\Exception $e) {
            $this->logger->error('Watchlist error: ' . $e->getMessage());
            return $this->json(['error' => 'Server error'], 500);
        }
    }

    #[Route('', name: 'watchlist_add', methods: ['POST'])]
    public function addToWatchlist(
        #[CurrentUser] User $user,
        Request $request
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des données
            $constraints = new Assert\Collection([
                'fields' => [
                    'animeId' => [
                        new Assert\NotBlank(),
                        new Assert\Type('numeric'),
                        new Assert\Positive()
                    ],
                    'status' => [
                        new Assert\NotBlank(),
                        new Assert\Choice([
                            'choices' => ['WATCHING', 'COMPLETED', 'ON_HOLD', 'DROPPED', 'PLANNED'],
                            'message' => 'Invalid status'
                        ])
                    ],
                    'progress' => [
                        new Assert\Optional([
                            new Assert\Type('numeric'),
                            new Assert\PositiveOrZero()
                        ])
                    ],
                    'score' => [
                        new Assert\Optional([
                            new Assert\Type('numeric'),
                            new Assert\Range(['min' => 0, 'max' => 100])
                        ])
                    ],
                    'notes' => [
                        new Assert\Optional([
                            new Assert\Type('string'),
                            new Assert\Length(['max' => 1000])
                        ])
                    ]
                ],
                'allowExtraFields' => false
            ]);

            $errors = $this->validator->validate($data, $constraints);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], 400);
            }

            // Synchroniser/créer l'anime
            $anime = $this->animeSyncer->sync($data['animeId']);

            // Vérifier si l'anime est déjà dans la watchlist
            $existingItem = $this->em->getRepository(Watchlist::class)->findOneBy([
                'user' => $user,
                'anime' => $anime
            ]);

            if ($existingItem) {
                return $this->json(['error' => 'This anime is already in your watchlist'], 409);
            }

            // Créer un nouvel item
            $item = new Watchlist();
            $item->setUser($user)
                ->setAnime($anime)
                ->setStatus($data['status'])
                ->setProgress($data['progress'] ?? 0)
                ->setScore($data['score'] ?? null)
                ->setNotes($data['notes'] ?? null);

            $this->em->persist($item);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'item' => [
                    'animeId' => $item->getAnime()->getId(),
                    'status' => $item->getStatus(),
                    'progress' => $item->getProgress(),
                    'score' => $item->getScore(),
                    'notes' => $item->getNotes(),
                    'animeTitle' => $anime->getTitle(),
                    'animeImage' => $anime->getImageUrl(),
                ]
            ], 201);
            
        } catch (\Exception $e) {
            $this->logger->error('Add error: '.$e->getMessage());
            return $this->json([
                'error' => 'Server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{animeId}', name: 'watchlist_update', methods: ['PUT'])]
    public function updateWatchlistItem(
        #[CurrentUser] User $user,
        int $animeId,
        Request $request
    ): JsonResponse {
        try {
            // S'assurer que l'anime existe
            $anime = $this->em->getRepository(Anime::class)->find($animeId);
            if (!$anime) {
                $anime = $this->animeSyncer->sync($animeId);
                $this->em->persist($anime);
                $this->em->flush();
            }

            $item = $this->em->getRepository(Watchlist::class)->findOneBy([
                'user' => $user,
                'anime' => $anime
            ]);

            if (!$item) {
                return $this->json(['error' => 'Watchlist item not found'], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Validation
            $constraints = new Assert\Collection([
                'fields' => [
                    'status' => [
                        new Assert\Optional([
                            new Assert\Choice([
                                'choices' => ['WATCHING', 'COMPLETED', 'ON_HOLD', 'DROPPED', 'PLANNED']
                            ])
                        ])
                    ],
                    'progress' => [
                        new Assert\Optional([
                            new Assert\Type('numeric'),
                            new Assert\PositiveOrZero()
                        ])
                    ],
                    'score' => [
                        new Assert\Optional([
                            new Assert\Type('numeric'),
                            new Assert\Range(['min' => 0, 'max' => 100])
                        ])
                    ],
                    'notes' => [
                        new Assert\Optional([
                            new Assert\Type('string'),
                            new Assert\Length(['max' => 1000])
                        ])
                    ]
                ],
                'allowExtraFields' => false
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
                'success' => true,
                'item' => [
                    'animeId' => $item->getAnime()->getId(),
                    'status' => $item->getStatus(),
                    'progress' => $item->getProgress(),
                    'score' => $item->getScore(),
                    'notes' => $item->getNotes(),
                    'animeTitle' => $anime->getTitle(),
                    'animeImage' => $anime->getImageUrl(),
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Update error: '.$e->getMessage());
            return $this->json([
                'error' => 'Server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{animeId}', name: 'watchlist_remove', methods: ['DELETE'])]
    public function removeFromWatchlist(
        #[CurrentUser] User $user,
        int $animeId
    ): JsonResponse {
        try {
            $item = $this->em->getRepository(Watchlist::class)->findOneBy([
                'user' => $user,
                'anime' => $animeId
            ]);

            if (!$item) {
                return $this->json(['error' => 'Watchlist item not found'], 404);
            }

            $this->em->remove($item);
            $this->em->flush();

            return $this->json(null, 204);
            
        } catch (\Exception $e) {
            $this->logger->error('Remove error: '.$e->getMessage());
            return $this->json([
                'error' => 'Server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}