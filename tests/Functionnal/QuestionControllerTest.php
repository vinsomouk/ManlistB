// tests/Functional/Controller/QuestionnaireControllerTest.php
public function testQuestionnaireSubmission()
{
    $client = $this->createAuthenticatedClient();
    $questionnaire = $this->loadQuestionnaireFixture();

    $answers = [];
    foreach ($questionnaire->getQuestions() as $question) {
        $options = $question->getAnswerOptions();
        $answers[$question->getId()] = $options[0]->getId();
    }

    $client->request('POST', '/api/questionnaires/'.$questionnaire->getId().'/submit', [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode(['answers' => $answers]));

    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    $response = json_decode($client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('recommendations', $response);
    $this->assertGreaterThan(0, count($response['recommendations']));
}