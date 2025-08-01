<?php

namespace App\Service;

use App\Entity\UserResponse;
use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AnswerOption;

class RecommendationService
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function generateRecommendations(UserResponse $response): array
    {
        // 1. Collecter tous les tags des réponses
        $selectedTags = $this->getSelectedTags($response);
        
        // 2. Trouver les animés correspondants
        $animes = $this->findMatchingAnimes($selectedTags);
        
        // 3. Formater les résultats
        return $this->formatRecommendations($animes);
    }
    
    private function getSelectedTags(UserResponse $response): array
    {
        $tags = [];
        $answers = $response->getAnswers();
        
        foreach ($answers as $answer) {
            if (isset($answer['optionId'])) {
                $option = $this->em->getRepository(AnswerOption::class)->find($answer['optionId']);
                if ($option) {
                    $tags = array_merge($tags, $option->getTags());
                }
            }
        }
        
        return array_unique($tags);
    }
    
    private function findMatchingAnimes(array $tags): array
{
    if (empty($tags)) {
        return [];
    }
    
    $animeRepository = $this->em->getRepository(Anime::class);
    
    // Utilisez une requête DQL compatible PostgreSQL
    $query = $animeRepository->createQueryBuilder('a')
        ->where('a.genres && :tags') // Opérateur && pour le chevauchement de tableaux
        ->setParameter('tags', $tags)
        ->setMaxResults(10)
        ->getQuery();
        
    return $query->getResult();
}
    
    private function formatRecommendations(array $animes): array
    {
        $result = [];
        
        foreach ($animes as $anime) {
            $result[] = [
                'id' => $anime->getId(),
                'title' => $anime->getTitle(),
                'imageUrl' => $anime->getImageUrl(),
                'score' => $anime->getAverageScore(),
                'genres' => $anime->getGenres(),
            ];
        }
        
        return $result;
    }
}