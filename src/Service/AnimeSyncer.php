<?php

namespace App\Service;

use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnimeSyncer
{
    private const ANILIST_API_URL = 'https://graphql.anilist.co';
    private const REQUEST_TIMEOUT = 10;
    private const MAX_RETRIES = 2;

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em
    ) {}

    public function sync(int $animeId): Anime
    {
        $anime = $this->em->getRepository(Anime::class)->find($animeId);

    if (!$anime) {
        $anime = new Anime($animeId);
        $this->em->persist($anime);
    }

        // Synchroniser seulement si nécessaire
        if (!$anime->getTitle() || $anime->isStale()) {
            $data = $this->fetchAnimeData($animeId);
            $this->updateAnimeFromData($anime, $data);
            $anime->setLastSyncedAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        return $anime;
    }

    private function fetchAnimeData(int $id): array
    {
        $query = <<<GRAPHQL
            query (\$id: Int) {
                Media(id: \$id, type: ANIME) {
                    id
                    title {
                        romaji
                        english
                        native
                    }
                    coverImage {
                        large
                    }
                    episodes
                    averageScore
                    genres
                    isAdult
                    format
                    duration
                    status
                    bannerImage
                }
            }
        GRAPHQL;

        $retryCount = 0;
        while ($retryCount <= self::MAX_RETRIES) {
            try {
                $response = $this->httpClient->request('POST', self::ANILIST_API_URL, [
                    'json' => [
                        'query' => $query,
                        'variables' => ['id' => $id]
                    ],
                    'timeout' => self::REQUEST_TIMEOUT
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray();
                    return $data['data']['Media'] ?? [];
                }
            } catch (\Exception $e) {
                // Log error or handle it
            }

            $retryCount++;
            sleep(1); // Wait before retrying
        }

        throw new \RuntimeException('Failed to fetch anime data after retries');
    }

    private function updateAnimeFromData(Anime $anime, array $data): void
    {
        $anime->setTitle($data['title']['romaji'] ?? $data['title']['english'] ?? 'Unknown Title');
        $anime->setImageUrl($data['coverImage']['large'] ?? null);
        $anime->setEpisodeCount($data['episodes'] ?? null);
        $anime->setRawData($data);
    }
}