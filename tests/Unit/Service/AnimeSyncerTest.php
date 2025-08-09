<?php

namespace App\Tests\Unit\Service;

use App\Entity\Anime;
use App\Service\AnimeSyncer;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnimeSyncerTest extends TestCase
{
    public function testSyncNewAnime()
    {
        // Mock HTTP response
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')
            ->willReturn([
                'data' => [
                    'Media' => [
                        'title' => ['romaji' => 'Test Anime'],
                        'coverImage' => ['large' => 'image.jpg'],
                        'description' => 'Test description',
                        'episodes' => 12,
                        'status' => 'FINISHED'
                    ]
                ]
            ]);

        // Mock HTTP client
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->method('request')
            ->willReturn($mockResponse);

        // Mock EntityManager
        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockEm->expects($this->once())
            ->method('persist')
            ->with($this->callback(function($anime) {
                return $anime instanceof Anime && 
                       $anime->getTitle() === 'Test Anime';
            }));
        $mockEm->expects($this->once())
            ->method('flush');

        // Test the syncer
        $syncer = new AnimeSyncer($mockHttp, $mockEm);
        $anime = $syncer->sync(999);

        // Assertions
        $this->assertInstanceOf(Anime::class, $anime);
        $this->assertEquals('Test Anime', $anime->getTitle());
        $this->assertEquals('image.jpg', $anime->getImageUrl());
        $this->assertEquals('Test description', $anime->getDescription());
        $this->assertEquals(12, $anime->getEpisodes());
        $this->assertEquals('FINISHED', $anime->getStatus());
    }
}