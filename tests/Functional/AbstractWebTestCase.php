<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(string $email = 'test@manlist.fr', string $password = 'password123')
    {
        $client = static::createClient();
        $container = static::getContainer();

        // Create or get existing user
        $em = $container->get('doctrine')->getManager();
        $userRepository = $em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setNickname('testuser');
            $user->setPassword($container->get(UserPasswordHasherInterface::class)
                ->hashPassword($user, $password));
            $em->persist($user);
            $em->flush();
        }

        // Authenticate
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $data['token']);

        return $client;
    }
}