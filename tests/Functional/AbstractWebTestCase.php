<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(
        string $email = 'test@manlist.fr',
        string $password = 'password123'
    ): KernelBrowser {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $user = $userRepository->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setNickname('testuser');

            $passwordHasher = $container->get(
                UserPasswordHasherInterface::class
            );

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
        }

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
                'password' => $password,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();

        return $client;
    }
}