<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\AnswerOption;
use App\Tests\Functional\AbstractWebTestCase;

class QuestionnaireControllerTest extends AbstractWebTestCase
{
    public function testQuestionnaireSubmission()
    {
        $client = $this->createAuthenticatedClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // Create questionnaire
        $questionnaire = new Questionnaire();
        $questionnaire->setTitle('Test Questionnaire');
        $em->persist($questionnaire);

        // Add questions
        $question = new Question();
        $question->setText('Test Question');
        $question->setQuestionnaire($questionnaire);
        $em->persist($question);

        // Add answer options
        $option1 = new AnswerOption();
        $option1->setText('Option 1');
        $option1->setQuestion($question);
        $em->persist($option1);

        $option2 = new AnswerOption();
        $option2->setText('Option 2');
        $option2->setQuestion($question);
        $em->persist($option2);

        $em->flush();

        // Prepare answers
        $answers = [
            $question->getId() => $option1->getId()
        ];

        // Submit questionnaire
        $client->request('POST', '/api/questionnaires/'.$questionnaire->getId().'/submit', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['answers' => $answers]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('recommendations', $response);
        $this->assertIsArray($response['recommendations']);
        $this->assertArrayHasKey('score', $response);
    }
}