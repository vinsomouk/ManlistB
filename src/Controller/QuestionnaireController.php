<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\UserResponse;
use App\Service\RecommendationService;

#[Route('/api/questionnaires')]
class QuestionnaireController extends AbstractController
{
    #[Route('', name: 'questionnaire_list', methods: ['GET'])]
    public function list(QuestionnaireRepository $repository): JsonResponse
    {
        $questionnaires = $repository->findBy(['isActive' => true]);
        
        $data = [];
        foreach ($questionnaires as $questionnaire) {
            $data[] = [
                'id' => $questionnaire->getId(),
                'title' => $questionnaire->getTitle(),
                'description' => $questionnaire->getDescription(),
                'questionCount' => $questionnaire->getQuestions()->count(),
            ];
        }
        
        return $this->json($data);
    }

    #[Route('/{id}', name: 'questionnaire_show', methods: ['GET'])]
    public function show(Questionnaire $questionnaire): JsonResponse
    {
        $questions = [];
        foreach ($questionnaire->getQuestions() as $question) {
            $options = [];
            foreach ($question->getAnswerOptions() as $option) {
                $options[] = [
                    'id' => $option->getId(),
                    'text' => $option->getText(),
                ];
            }
            
            $questions[] = [
                'id' => $question->getId(),
                'text' => $question->getText(),
                'order' => $question->getOrder(),
                'options' => $options,
            ];
        }
        
        usort($questions, fn($a, $b) => $a['order'] <=> $b['order']);
        
        return $this->json([
            'id' => $questionnaire->getId(),
            'title' => $questionnaire->getTitle(),
            'description' => $questionnaire->getDescription(),
            'questions' => $questions,
        ]);
    }

    #[Route('/{id}/submit', name: 'questionnaire_submit', methods: ['POST'])]
    public function submit(
        #[CurrentUser] $user,
        Questionnaire $questionnaire,
        Request $request,
        EntityManagerInterface $em,
        RecommendationService $recommendationService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['answers']) || !is_array($data['answers'])) {
            return $this->json(['error' => 'Invalid response format'], 400);
        }
        
        $userResponse = new UserResponse();
        $userResponse->setUser($user);
        $userResponse->setQuestionnaire($questionnaire);
        $userResponse->setAnswers($data['answers']);
        
        $em->persist($userResponse);
        $em->flush();
        
        // Générer les recommandations
        $recommendations = $recommendationService->generateRecommendations($userResponse);
        
        return $this->json([
            'success' => true,
            'recommendations' => $recommendations,
            'responseId' => $userResponse->getId(),
        ]);
    }

    #[Route('/{id}/completed', name: 'questionnaire_completed', methods: ['GET'])]
public function checkCompleted(
    #[CurrentUser] User $user,
    int $id,
    UserResponseRepository $repo
): JsonResponse {
    $completed = $repo->hasUserCompleted($user->getId(), $id);
    return $this->json(['completed' => $completed]);
}

#[Route('/api/user_responses', name: 'user_responses', methods: ['GET'])]
public function getUserResponses(#[CurrentUser] User $user): JsonResponse
{
    $responses = $this->em->getRepository(UserResponse::class)->findBy(['user' => $user]);
    
    $data = array_map(function(UserResponse $response) {
        return [
            'id' => $response->getId(),
            'questionnaire' => [
                'id' => $response->getQuestionnaire()->getId(),
                'title' => $response->getQuestionnaire()->getTitle()
            ],
            'completedAt' => $response->getCompletedAt()->format('Y-m-d H:i:s')
        ];
    }, $responses);

    return $this->json($data);
}

    #[Route('/{id}/status', name: 'questionnaire_status', methods: ['GET'])]
public function status(
    #[CurrentUser] $user,
    Questionnaire $questionnaire,
    EntityManagerInterface $em
): JsonResponse {
    $response = $em->getRepository(UserResponse::class)->findOneBy([
        'user' => $user,
        'questionnaire' => $questionnaire
    ]);

    return $this->json([
        'completed' => $response !== null,
        'completedAt' => $response ? $response->getCompletedAt()->format('Y-m-d H:i:s') : null
    ]);
}
}