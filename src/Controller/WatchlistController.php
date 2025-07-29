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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/watchlist')]
class WatchlistController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;

    public function __construct(
        EntityManagerInterface $em, 
        ValidatorInterface $validator,
        LoggerInterface $logger,
        HttpClientInterface $httpClient
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    #[Route('', name: 'watchlist_get', methods: ['GET'])]
    public function getWatchlist(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Authentication required'], 401);
        }

        try {
            $watchlist = $this->em->getRepository(Watchlist::class)->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );

            // Récupérer les IDs des animés
            $animeIds = array_map(fn($item) => $item->getAnimeId(), $watchlist);

            // Requête batch pour récupérer les infos des animés
            $animeDetails = [];
            if (!empty($animeIds)) {
                try {
                    $response = $this->httpClient->request('POST', 'https://graphql.anilist.co', [
                        'json' => [
                            'query' => '
                                query ($ids: [Int]) {
                                    Page {
                                        media(id_in: $ids) {
                                            id
                                            title {
                                                romaji
                                            }
                                            coverImage {
                                                large
                                            }
                                        }
                                    }
                                }
                            ',
                            'variables' => ['ids' => array_values($animeIds)]
                        ],
                        'timeout' => 5
                    ]);

                    $content = $response->toArray();
                    $animeDetails = array_reduce($content['data']['Page']['media'] ?? [], function($carry, $media) {
                        $carry[$media['id']] = [
                            'title' => $media['title']['romaji'] ?? null,
                            'image' => $media['coverImage']['large'] ?? null
                        ];
                        return $carry;
                    }, []);
                } catch (\Exception $e) {
                    $this->logger->error('AniList API error: ' . $e->getMessage());
                }
            }

            // Construire la réponse
            $data = array_map(function ($item) use ($animeDetails) {
                $animeId = $item->getAnimeId();
                return [
                    'animeId' => $animeId,
                    'status' => $item->getStatus(),
                    'progress' => $item->getProgress(),
                    'score' => $item->getScore(),
                    'notes' => $item->getNotes(),
                    'animeTitle' => $animeDetails[$animeId]['title'] ?? null,
                    'animeImage' => $animeDetails[$animeId]['image'] ?? null,
                    'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $item->getUpdatedAt()?->format('Y-m-d H:i:s')
                ];
            }, $watchlist);

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
            ->setProgress($data['progress'] ?? 0)
            ->setScore($data['score'] ?? null)
            ->setNotes($data['notes'] ?? null);

        $this->em->persist($item);
        $this->em->flush();

        return $this->json([
            'animeId' => $item->getAnimeId(),
            'status' => $item->getStatus(),
            'progress' => $item->getProgress(),
            'score' => $item->getScore(),
            'notes' => $item->getNotes()
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
            'animeId' => $item->getAnimeId(),
            'status' => $item->getStatus(),
            'progress' => $item->getProgress(),
            'score' => $item->getScore(),
            'notes' => $item->getNotes()
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
}