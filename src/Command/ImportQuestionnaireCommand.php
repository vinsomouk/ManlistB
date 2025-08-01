<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Questionnaire;
use App\Entity\Question;
use App\Entity\AnswerOption;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:import-questionnaire',
    description: 'Importe un questionnaire depuis un tableau PHP'
)]
class ImportQuestionnaireCommand extends Command
{
    private EntityManagerInterface $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }
    
    protected function configure(): void
    {
        $this->setHelp('Cette commande permet d\'importer un questionnaire de test');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
{
    // Supprimez cette ligne qui cause l'erreur :
    // $this->em->getFilters()->disable('softdeleteable');
    
    $questionnaireData = [
        'title' => 'Questionnaire de test',
        'description' => 'Description du questionnaire',
        'questions' => [
            [
                'text' => 'Question 1',
                'order' => 1,
                'options' => [
                    ['text' => 'Option 1', 'tags' => ['tag1']],
                    ['text' => 'Option 2', 'tags' => ['tag2']]
                ]
            ]
        ]
    ];

    $questionnaire = new Questionnaire();
    $questionnaire->setTitle($questionnaireData['title']);
    $questionnaire->setDescription($questionnaireData['description']);
    $questionnaire->setIsActive(true);
    
    foreach ($questionnaireData['questions'] as $qData) {
        $question = new Question();
        $question->setText($qData['text']);
        $question->setOrder($qData['order']);
        
        foreach ($qData['options'] as $oData) {
            $option = new AnswerOption();
            $option->setText($oData['text']);
            $option->setTags($oData['tags'] ?? []);
            $question->addAnswerOption($option);
        }
        
        $questionnaire->addQuestion($question);
    }
    
    $this->em->persist($questionnaire);
    $this->em->flush();
    
    $output->writeln('<info>Questionnaire importé avec succès !</info>');
    return Command::SUCCESS;
}
}