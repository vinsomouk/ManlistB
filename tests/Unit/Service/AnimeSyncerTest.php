// tests/Unit/Service/AnimeSyncerTest.php
public function testSyncNewAnime()
{
    $mockHttp = $this->createMock(HttpClientInterface::class);
    $mockHttp->method('request')->willReturn(
        new MockResponse(json_encode([
            'data' => ['Media' => [
                'title' => ['romaji' => 'Test Anime'],
                'coverImage' => ['large' => 'image.jpg']
            ]]
        ]))
    );

    $syncer = new AnimeSyncer($mockHttp, $this->em);
    $anime = $syncer->sync(999);
    
    $this->assertEquals('Test Anime', $anime->getTitle());
    $this->assertEquals('image.jpg', $anime->getImageUrl());
}