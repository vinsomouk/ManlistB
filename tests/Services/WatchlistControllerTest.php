// tests/Functional/Controller/WatchlistControllerTest.php
public function testWatchlistLifecycle()
{
    $client = $this->createAuthenticatedClient();
    
    // Ajout
    $client->request('POST', '/api/watchlist', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'animeId' => 101,
        'status' => 'WATCHING'
    ]));
    $this->assertEquals(201, $client->getResponse()->getStatusCode());
    
    // Mise à jour
    $client->request('PUT', '/api/watchlist/101', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'status' => 'COMPLETED',
        'score' => 85
    ]));
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    
    // Suppression
    $client->request('DELETE', '/api/watchlist/101');
    $this->assertEquals(204, $client->getResponse()->getStatusCode());
}

private function createAuthenticatedClient()
{
    $client = static::createClient();
    $client->request('POST', '/api/auth/login', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'email' => 'test@manlist.fr',
        'password' => 'password123'
    ]));
    
    $data = json_decode($client->getResponse()->getContent(), true);
    $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $data['token']);
    
    return $client;
}