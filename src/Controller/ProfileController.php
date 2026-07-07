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

    #[Route('', name: 'profile_update', methods: ['POST'])]
    public function updateProfile(
        #[CurrentUser] User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $errors = [];

        $email = $request->request->get('email');
        $nickname = $request->request->get('nickname');
        $currentPassword = $request->request->get('currentPassword');
        $newPassword = $request->request->get('newPassword');

        if ($email) {
            $user->setEmail($email);
            $emailErrors = $validator->validateProperty($user, 'email');

            if (count($emailErrors) > 0) {
                $errors['email'] = $emailErrors[0]->getMessage();
            }
        }

        if ($nickname) {
            $user->setNickname($nickname);
            $nicknameErrors = $validator->validateProperty($user, 'nickname');

            if (count($nicknameErrors) > 0) {
                $errors['nickname'] = $nicknameErrors[0]->getMessage();
            }
        }

        $file = $request->files->get('profilePicture');

        if ($file) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
                $errors['profilePicture'] = 'Format image invalide';
            } else {
                $extension = $file->guessExtension() ?: 'jpg';
                $newFilename = uniqid('avatar_', true) . '.' . $extension;

                $uploadDir = $this->getParameter('uploads_directory');

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file->move($uploadDir, $newFilename);

                $user->setProfilePicture('/uploads/profile/' . $newFilename);
            }
        }

        if ($newPassword) {
            if (!$currentPassword) {
                $errors['currentPassword'] = 'Mot de passe actuel requis';
            } elseif (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $errors['currentPassword'] = 'Mot de passe actuel incorrect';
            } else {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $newPassword)
                );
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