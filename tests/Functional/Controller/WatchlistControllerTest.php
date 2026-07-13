<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\AbstractWebTestCase;

class WatchlistControllerTest extends AbstractWebTestCase
{
    public function testWatchlistLifecycle(): void
    {
        $client = $this->createAuthenticatedClient();

        // Identifiant AniList valide.
        $animeId = 1;

        $client->request(
            'POST',
            '/api/watchlist',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(
                [
                    'animeId' => $animeId,
                    'status' => 'WATCHING',
                ],
                JSON_THROW_ON_ERROR
            )
        );

        self::assertResponseStatusCodeSame(201);

        $addResponse = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertTrue($addResponse['success']);
        self::assertArrayHasKey('item', $addResponse);
        self::assertSame(
            $animeId,
            $addResponse['item']['animeId']
        );
        self::assertSame(
            'WATCHING',
            $addResponse['item']['status']
        );

        $client->request(
            'PUT',
            sprintf('/api/watchlist/%d', $animeId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(
                [
                    'status' => 'COMPLETED',
                    'score' => 85,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        self::assertResponseIsSuccessful();

        $updateResponse = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertTrue($updateResponse['success']);
        self::assertArrayHasKey(
            'item',
            $updateResponse
        );
        self::assertSame(
            'COMPLETED',
            $updateResponse['item']['status']
        );
        self::assertSame(
            85,
            $updateResponse['item']['score']
        );

        $client->request('GET', '/api/watchlist');

        self::assertResponseIsSuccessful();

        $listResponse = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('data', $listResponse);
        self::assertCount(1, $listResponse['data']);
        self::assertSame(
            $animeId,
            $listResponse['data'][0]['animeId']
        );

        $client->request(
            'DELETE',
            sprintf('/api/watchlist/%d', $animeId)
        );

        self::assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/watchlist');

        self::assertResponseIsSuccessful();

        $emptyResponse = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('data', $emptyResponse);
        self::assertCount(0, $emptyResponse['data']);
    }
}