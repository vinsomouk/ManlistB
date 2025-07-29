<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/profile')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'profile_get', methods: ['GET'])]
    public function getProfile(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
            'profilePicture' => $user->getProfilePicture(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('', name: 'profile_update', methods: ['PUT'])]
    public function updateProfile(
        #[CurrentUser] User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        // Vérifier si les données JSON sont valides
        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], 400);
        }

        $errors = [];
        $hasPasswordChange = false;

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
            $emailErrors = $validator->validateProperty($user, 'email');
            if (count($emailErrors) > 0) {
                $errors['email'] = $emailErrors[0]->getMessage();
            }
        }

        if (isset($data['nickname'])) {
            $user->setNickname($data['nickname']);
            $nicknameErrors = $validator->validateProperty($user, 'nickname');
            if (count($nicknameErrors) > 0) {
                $errors['nickname'] = $nicknameErrors[0]->getMessage();
            }
        }

        if (isset($data['profilePicture'])) {
            $user->setProfilePicture($data['profilePicture']);
        }

        if (isset($data['newPassword'])) {
            $hasPasswordChange = true;
            
            if (!isset($data['currentPassword'])) {
                $errors['currentPassword'] = 'Current password is required';
            } elseif (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                $errors['currentPassword'] = 'Current password is invalid';
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
                $passwordErrors = $validator->validateProperty($user, 'password');
                if (count($passwordErrors) > 0) {
                    $errors['newPassword'] = $passwordErrors[0]->getMessage();
                }
            }
        }

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        try {
            $em->flush();
            
            return $this->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nickname' => $user->getNickname(),
                    'profilePicture' => $user->getProfilePicture(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while updating profile',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('', name: 'profile_delete', methods: ['DELETE'])]
    public function deleteProfile(
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            $em->remove($user);
            $em->flush();
            
            return $this->json(['message' => 'Account deleted successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while deleting account',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}