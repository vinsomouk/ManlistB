// tests/Functional/Controller/AuthControllerTest.php
public function testRegistrationValidation()
{
    $client = static::createClient();
    $client->request('POST', '/api/auth/register', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'email' => 'invalid-email',
        'nickname' => 'a',
        'password' => '123'
    ]));

    $this->assertEquals(400, $client->getResponse()->getStatusCode());
    $response = json_decode($client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('errors', $response);
    $this->assertContains('Invalid email format', $response['errors']);
}

public function testLoginSuccess()
{
    // Création utilisateur via fixtures
    $this->loadUserFixture();
    
    $client = static::createClient();
    $client->request('POST', '/api/auth/login', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode([
        'email' => 'test@manlist.fr',
        'password' => 'password123'
    ]));

    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    $response = json_decode($client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('nickname', $response);
    $this->assertEquals('testuser', $response['nickname']);
}