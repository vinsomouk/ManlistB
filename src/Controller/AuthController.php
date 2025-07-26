<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['email']) || !isset($data['nickname']) || !isset($data['password'])) {
            return $this->json(['error' => 'Email, nickname and password are required'], 400);
        }

        $user = new User();
        $user->setEmail($data['email'])
             ->setNickname($data['nickname'])
             ->setProfilePicture($data['profilePicture'] ?? null);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
            'profilePicture' => $user->getProfilePicture()
        ], 201);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
            'profilePicture' => $user->getProfilePicture()
        ]);
    }

   #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // On retourne simplement un succès, la déconnexion est gérée côté client
        return $this->json(['message' => 'Logged out successfully']);
    }


    #[Route('/check', name: 'auth_check', methods: ['GET'])]
    public function checkAuth(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(null, 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
            'profilePicture' => $user->getProfilePicture()
        ]);
    }
}