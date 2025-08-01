<?php

namespace App\DataFixtures;

use App\Entity\Questionnaire;
use App\Entity\Question;
use App\Entity\AnswerOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionnaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void // Ajout du type de retour 'void'
    {
        $questionnaire = new Questionnaire();
        $questionnaire->setTitle("Découverte de vos préférences animés");
        $questionnaire->setDescription("Répondez à ces questions pour obtenir des recommandations personnalisées");
        $questionnaire->setIsActive(true);

        // Question 1
        $question1 = new Question();
        $question1->setText("Quel type d'histoire préférez-vous ?");
        $question1->setOrder(1);
        
        $options1 = [
            ['text' => "Action et aventure palpitante", 'tags' => ["Action", "Adventure"]],
            ['text' => "Drame émotionnel et relations complexes", 'tags' => ["Drama", "Slice of Life"]],
            ['text' => "Mystère et intrigue psychologique", 'tags' => ["Mystery", "Psychological"]],
            ['text' => "Humour léger et comédie", 'tags' => ["Comedy", "Parody"]]
        ];
        
        foreach ($options1 as $optData) {
            $option = new AnswerOption();
            $option->setText($optData['text']);
            $option->setTags($optData['tags']);
            $question1->addAnswerOption($option);
        }
        $questionnaire->addQuestion($question1);

        // Question 2
        $question2 = new Question();
        $question2->setText("Quelle ambiance visuelle vous attire le plus ?");
        $question2->setOrder(2);
        
        $options2 = [
            ['text' => "Visuels épurés et artistiques", 'tags' => ["Artistic", "Stylized"]],
            ['text' => "Animation dynamique et colorée", 'tags' => ["Colorful", "Energetic"]],
            ['text' => "Atmosphères sombres et contrastées", 'tags' => ["Dark", "Gritty"]],
            ['text' => "Esthétique rétro/vintage", 'tags' => ["Retro", "Vintage"]]
        ];
        
        foreach ($options2 as $optData) {
            $option = new AnswerOption();
            $option->setText($optData['text']);
            $option->setTags($optData['tags']);
            $question2->addAnswerOption($option);
        }
        $questionnaire->addQuestion($question2);

        // Ajoutez d'autres questions ici...

        $manager->persist($questionnaire);
        $manager->flush();
    }
}