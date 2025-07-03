<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
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
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $user = new User();
        $user->setEmail($data['email'])
             ->setNickname($data['nickname']);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorsArray], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nickname' => $user->getNickname()
            ]
        ], 201, [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        $user = $this->getUser ();
        if (!$user) {
            return $this->json(['error' => 'Authentification requise'], 401);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nickname' => $user->getNickname()
            ]
        ], 200, [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(['success' => true, 'message' => 'Déconnexion réussie'], 200, [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    #[Route('/register', name: 'api_register_options', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        return $this->json([], 200, [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept',
        ]);
    }
}
