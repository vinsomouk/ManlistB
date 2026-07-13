<?php

namespace App\Tests\Unit\Service;

use App\Entity\Anime;
use App\Repository\AnimeRepository;
use App\Service\AnimeSyncer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AnimeSyncerTest extends TestCase
{
    public function testSyncNewAnime(): void
    {
        $mockResponse = $this->createMock(
            ResponseInterface::class
        );

        $mockResponse
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $mockResponse
            ->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'data' => [
                    'Media' => [
                        'id' => 999,
                        'title' => [
                            'romaji' => 'Test Anime',
                            'english' => null,
                            'native' => null,
                        ],
                        'coverImage' => [
                            'large' => 'image.jpg',
                        ],
                        'description' => 'Test description',
                        'episodes' => 12,
                        'averageScore' => 85,
                        'duration' => 24,
                        'status' => 'FINISHED',
                        'format' => 'TV',
                        'bannerImage' => 'banner.jpg',
                        'genres' => [
                            'Action',
                        ],
                        'isAdult' => false,
                    ],
                ],
            ]);

        $mockHttpClient = $this->createMock(
            HttpClientInterface::class
        );

        $mockHttpClient
            ->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://graphql.anilist.co',
                self::callback(
                    static function (array $options): bool {
                        return isset(
                            $options['json']['variables']['id']
                        )
                            && $options['json']['variables']['id']
                                === 999;
                    }
                )
            )
            ->willReturn($mockResponse);

        $mockRepository = $this->createMock(
            AnimeRepository::class
        );

        $mockRepository
            ->expects(self::once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $mockEntityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $mockEntityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Anime::class)
            ->willReturn($mockRepository);

        $mockEntityManager
            ->expects(self::once())
            ->method('persist')
            ->with(
                self::callback(
                    static function (Anime $anime): bool {
                        return $anime->getId() === 999
                            && $anime->getTitle()
                                === 'Test Anime'
                            && $anime->getDescription()
                                === 'Test description'
                            && $anime->getEpisodes() === 12
                            && $anime->getStatus()
                                === 'FINISHED';
                    }
                )
            );

        $mockEntityManager
            ->expects(self::once())
            ->method('flush');

        $syncer = new AnimeSyncer(
            $mockHttpClient,
            $mockEntityManager
        );

        $anime = $syncer->sync(999);

        self::assertSame(999, $anime->getId());
        self::assertSame(
            'Test Anime',
            $anime->getTitle()
        );
        self::assertSame(
            'image.jpg',
            $anime->getImageUrl()
        );
        self::assertSame(
            'Test description',
            $anime->getDescription()
        );
        self::assertSame(12, $anime->getEpisodes());
        self::assertSame(
            'FINISHED',
            $anime->getStatus()
        );
        self::assertSame(85, $anime->getAverageScore());
        self::assertSame(24, $anime->getDuration());
        self::assertSame('TV', $anime->getFormat());
        self::assertSame(
            'banner.jpg',
            $anime->getBannerImage()
        );
    }
}