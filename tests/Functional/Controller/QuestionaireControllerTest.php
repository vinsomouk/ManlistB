<?php

namespace App\Tests\Functional\Controller;

use App\Entity\AnswerOption;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Tests\Functional\AbstractWebTestCase;

class QuestionnaireControllerTest extends AbstractWebTestCase
{
    public function testQuestionnaireSubmission(): void
    {
        $client = $this->createAuthenticatedClient();

        $entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $questionnaire = new Questionnaire();
        $questionnaire
            ->setTitle('Test Questionnaire')
            ->setDescription(
                'Questionnaire créé pour le test fonctionnel'
            )
            ->setIsActive(true);

        $entityManager->persist($questionnaire);

        $question = new Question();
        $question
            ->setText('Test Question')
            ->setQuestionnaire($questionnaire);

        $entityManager->persist($question);

        $option1 = new AnswerOption();
        $option1
            ->setText('Option 1')
            ->setQuestion($question);

        $entityManager->persist($option1);

        $option2 = new AnswerOption();
        $option2
            ->setText('Option 2')
            ->setQuestion($question);

        $entityManager->persist($option2);

        $entityManager->flush();

        $answers = [
            $question->getId() => $option1->getId(),
        ];

        $client->request(
            'POST',
            sprintf(
                '/api/questionnaires/%d/submit',
                $questionnaire->getId()
            ),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(
                [
                    'answers' => $answers,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        self::assertResponseIsSuccessful();

        $response = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey(
            'recommendations',
            $response
        );

        self::assertIsArray(
            $response['recommendations']
        );

       self::assertArrayHasKey('recommendations', $response);
self::assertIsArray($response['recommendations']);

if ($response['recommendations'] !== []) {
    self::assertArrayHasKey('score', $response['recommendations'][0]);
}
    }
}