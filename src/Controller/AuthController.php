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

    // Valider AVANT de hasher le mot de passe
    $errors = $validator->validate($user);
    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], 400);
    }

    // Hasher le mot de passe
    $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
    $user->setPassword($hashedPassword);
    
    try {
        $entityManager->persist($user);
        $entityManager->flush();
    } catch (\Exception $e) {
        return $this->json(['error' => 'Database error: ' . $e->getMessage()], 500);
    }

    return $this->json([
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'nickname' => $user->getNickname(),
        'profilePicture' => $user->getProfilePicture(),
        'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        'isVerified' => $user->isVerified(),
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
        'profilePicture' => $user->getProfilePicture(),
        'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        'isVerified' => $user->isVerified(),
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