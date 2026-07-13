<?php

namespace App\Service;

use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AnimeSyncer
{
    private const ANILIST_API_URL = 'https://graphql.anilist.co';
    private const REQUEST_TIMEOUT = 10;
    private const MAX_RETRIES = 2;

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sync(int $animeId): Anime
    {
        $anime = $this->entityManager
            ->getRepository(Anime::class)
            ->find($animeId);

        $isNewAnime = $anime === null;

        if ($isNewAnime) {
            $anime = new Anime($animeId);
        }

        if (
            $isNewAnime
            || $anime->getTitle() === ''
            || $anime->isStale()
        ) {
            $data = $this->fetchAnimeData($animeId);

            $this->updateAnimeFromData($anime, $data);

            $anime->setLastSyncedAt(
                new \DateTimeImmutable()
            );

            if ($isNewAnime) {
                $this->entityManager->persist($anime);
            }

            $this->entityManager->flush();
        }

        return $anime;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchAnimeData(int $id): array
    {
        $query = <<<'GRAPHQL'
            query ($id: Int) {
                Media(id: $id, type: ANIME) {
                    id

                    title {
                        romaji
                        english
                        native
                    }

                    description

                    coverImage {
                        large
                    }

                    bannerImage
                    episodes
                    averageScore
                    genres
                    isAdult
                    format
                    duration
                    status
                }
            }
        GRAPHQL;

        $lastError = null;

        for (
            $attempt = 0;
            $attempt <= self::MAX_RETRIES;
            $attempt++
        ) {
            try {
                $response = $this->httpClient->request(
                    'POST',
                    self::ANILIST_API_URL,
                    [
                        'json' => [
                            'query' => $query,
                            'variables' => [
                                'id' => $id,
                            ],
                        ],
                        'timeout' => self::REQUEST_TIMEOUT,
                    ]
                );

                return $this->extractAnimeData($response);
            } catch (\Throwable $exception) {
                $lastError = $exception;

                if ($attempt < self::MAX_RETRIES) {
                    sleep(1);
                }
            }
        }

        throw new \RuntimeException(
            'Failed to fetch anime data after retries',
            previous: $lastError
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extractAnimeData(
        ResponseInterface $response
    ): array {
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf(
                    'AniList returned HTTP status %d',
                    $response->getStatusCode()
                )
            );
        }

        $responseData = $response->toArray();

        if (!empty($responseData['errors'])) {
            $messages = array_map(
                static fn (array $error): string =>
                    $error['message'] ?? 'Unknown AniList error',
                $responseData['errors']
            );

            throw new \RuntimeException(
                implode(', ', $messages)
            );
        }

        $animeData = $responseData['data']['Media'] ?? null;

        if (!is_array($animeData)) {
            throw new \RuntimeException(
                'Anime data was not found in AniList response'
            );
        }

        return $animeData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateAnimeFromData(
        Anime $anime,
        array $data
    ): void {
        $titleData = is_array($data['title'] ?? null)
            ? $data['title']
            : [];

        $coverImageData = is_array(
            $data['coverImage'] ?? null
        )
            ? $data['coverImage']
            : [];

        $title =
            $titleData['romaji']
            ?? $titleData['english']
            ?? $titleData['native']
            ?? 'Unknown Title';

        $anime
            ->setTitle((string) $title)
            ->setImageUrl(
                isset($coverImageData['large'])
                    ? (string) $coverImageData['large']
                    : null
            )
            ->setDescription(
                isset($data['description'])
                    ? (string) $data['description']
                    : null
            )
            ->setEpisodeCount(
                isset($data['episodes'])
                    ? (int) $data['episodes']
                    : null
            )
            ->setAverageScore(
                isset($data['averageScore'])
                    ? (int) $data['averageScore']
                    : null
            )
            ->setDuration(
                isset($data['duration'])
                    ? (int) $data['duration']
                    : null
            )
            ->setStatus(
                isset($data['status'])
                    ? (string) $data['status']
                    : null
            )
            ->setFormat(
                isset($data['format'])
                    ? (string) $data['format']
                    : null
            )
            ->setBannerImage(
                isset($data['bannerImage'])
                    ? (string) $data['bannerImage']
                    : null
            )
            ->setRawData($data);
    }
}