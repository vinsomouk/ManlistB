<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\AbstractWebTestCase;

class WatchlistControllerTest extends AbstractWebTestCase
{
    public function testWatchlistLifecycle()
    {
        $client = $this->createAuthenticatedClient();
        $animeId = mt_rand(1000, 10000);

        // Test adding to watchlist
        $client->request('POST', '/api/watchlist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'animeId' => $animeId,
            'status' => 'WATCHING'
        ]));
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $addResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $addResponse);

        // Test updating watchlist item
        $client->request('PUT', '/api/watchlist/'.$animeId, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'status' => 'COMPLETED',
            'score' => 85
        ]));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $updateResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('COMPLETED', $updateResponse['status']);

        // Test getting watchlist
        $client->request('GET', '/api/watchlist');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $listResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $listResponse);

        // Test deleting from watchlist
        $client->request('DELETE', '/api/watchlist/'.$animeId);
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // Verify deletion
        $client->request('GET', '/api/watchlist');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $emptyResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(0, $emptyResponse);
    }
}