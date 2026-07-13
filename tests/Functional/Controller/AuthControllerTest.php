<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
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
        $this->assertArrayHasKey('email', $response['errors']);
        $this->assertContains('Cette valeur n\'est pas une adresse email valide.', $response['errors']['email']);
        $this->assertArrayHasKey('nickname', $response['errors']);
        $this->assertArrayHasKey('password', $response['errors']);
    }

    public function testRegistrationSuccess()
    {
        $client = static::createClient();
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid@email.com',
            'nickname' => 'validuser',
            'password' => 'ValidPassword123!'
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $response);
        $this->assertEquals('validuser', $response['user']['nickname']);
    }

    public function testLoginSuccess()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $em = $container->get('doctrine')->getManager();
        $user = new User();
        $user->setEmail('test@manlist.fr');
        $user->setNickname('testuser');
        
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        
        $em->persist($user);
        $em->flush();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@manlist.fr',
            'password' => 'password123'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('user', $response);
        $this->assertEquals('testuser', $response['user']['nickname']);
    }

    public function testLoginInvalidCredentials()
    {
        $client = static::createClient();
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Invalid credentials.', $response['message']);
    }
}