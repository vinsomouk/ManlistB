<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    public function testRegistrationValidation(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => 'invalid-email',
                'nickname' => 'a',
                'password' => '123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(400);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('errors', $response);
        self::assertIsArray($response['errors']);
        self::assertNotEmpty($response['errors']);
    }

    public function testRegistrationSuccess(): void
    {
        $client = static::createClient();

        $email = sprintf(
            'valid-%s@email.com',
            uniqid()
        );

        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => $email,
                'nickname' => 'validuser',
                'password' => 'ValidPassword123!',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('id', $response);
        self::assertArrayHasKey('email', $response);
        self::assertArrayHasKey('nickname', $response);
        self::assertSame('validuser', $response['nickname']);
        self::assertSame($email, $response['email']);
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container
            ->get('doctrine')
            ->getManager();

        $email = sprintf(
            'login-%s@manlist.fr',
            uniqid()
        );

        $user = new User();
        $user->setEmail($email);
        $user->setNickname('testuser');

        $passwordHasher = $container->get(
            UserPasswordHasherInterface::class
        );

        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'password123'
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => $email,
                'password' => 'password123',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('id', $response);
        self::assertArrayHasKey('email', $response);
        self::assertArrayHasKey('nickname', $response);
        self::assertSame('testuser', $response['nickname']);
        self::assertSame($email, $response['email']);
    }

    public function testLoginInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => 'wrong@email.com',
                'password' => 'wrongpassword',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);

        $response = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertIsArray($response);

        self::assertTrue(
            array_key_exists('error', $response)
            || array_key_exists('message', $response)
        );
    }
}