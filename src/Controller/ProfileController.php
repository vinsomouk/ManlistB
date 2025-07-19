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
            'profilePicture' => $user->getProfilePicture()
        ]);
    }

    #[Route('', name: 'profile_update', methods: ['PUT'])]
    public function updateProfile(
        #[CurrentUser] User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['nickname'])) {
            $user->setNickname($data['nickname']);
        }

        if (isset($data['profilePicture'])) {
            $user->setProfilePicture($data['profilePicture']);
        }

        if (isset($data['newPassword']) && isset($data['currentPassword'])) {
            if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                return $this->json(['error' => 'Current password is invalid'], 400);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
        }

        $em->flush();

        return $this->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nickname' => $user->getNickname(),
                'profilePicture' => $user->getProfilePicture()
            ]
        ]);
    }

    #[Route('', name: 'profile_delete', methods: ['DELETE'])]
    public function deleteProfile(
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Account deleted successfully']);
    }
}