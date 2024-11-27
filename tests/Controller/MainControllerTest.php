<?php

namespace App\Tests\Controller;

use App\Service\MovieService;
use App\Service\TMDBApiService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class MainControllerTest extends WebTestCase
{
    private $client;
    private $mockTMDBApiService;
    private $mockMovieService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->client->getContainer()->set('session', new Session(new MockArraySessionStorage()));

        // Create mock TMDB API service
        $this->mockTMDBApiService = $this->createMock(TMDBApiService::class);
        $this->client->getContainer()->set(TMDBApiService::class, $this->mockTMDBApiService);

        // Create mock Movie service
        $this->mockMovieService = $this->createMock(MovieService::class);
        $this->client->getContainer()->set(MovieService::class, $this->mockMovieService);
    }

    public function testHomepage()
    {
        $this->mockMovieService->method('getMovies')->willReturn($this->getTestMoviesData());

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'A propos de We Movies');
        $this->assertCount(1, $crawler->filter('.movie-card'));
    }

    public function testMovieDetails()
    {
        $testMovie = $this->getTestMoviesData()['movies'][0];
        $this->mockMovieService->method('getMovieDetails')->willReturn($testMovie);

        $this->client->xmlHttpRequest('GET', "/movie/{$testMovie['id']}");
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('overview', $data);
        $this->assertArrayHasKey('voteAverage', $data);
        $this->assertArrayHasKey('voteCount', $data);
        $this->assertEquals('Star', $data['title']);
    }

    public function testSearchMovie()
    {
        $this->mockMovieService->method('getMovies')->willReturn($this->getTestMoviesData());

        $crawler = $this->client->request('GET', '/?search=star&genre=12');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('star', strtolower($crawler->filter('.movie-card')->first()->text()));
        $this->assertCount(1, $crawler->filter('.movie-card'));
    }

    public function testFilterByGenre()
    {
        $testData = $this->getTestMoviesData();
        $testData['selectedGenre'] = '12';  // Adventure genre

        $this->mockMovieService->method('getMovies')->willReturn($testData);

        $crawler = $this->client->request('GET', '/?genre=12');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.movie-card'));
    }

    public function testPagination()
    {
        $this->mockMovieService->method('getMovies')->willReturn($this->getTestMoviesData());

        $crawler = $this->client->request('GET', '/');
        $this->assertCount(1, $crawler->filter('.movie-card'));
    }

    public function testAutocomplete()
    {
        $autocompleteData = [
            [
                'id' => 52959,
                'title' => 'Star!',
                'year' => '1968',
            ],
            [
                'id' => 136244,
                'title' => 'Star',
                'year' => '1993',
            ],
            [
                'id' => 22599,
                'title' => 'Star',
                'year' => '2001',
            ],
            [
                'id' => 125225,
                'title' => 'Star',
                'year' => '1982',
            ],
            [
                'id' => 1225239,
                'title' => 'Star',
                'year' => '2025',
            ],
        ];

        $this->mockMovieService->method('autocompleteSearch')->willReturn($autocompleteData);

        $this->client->xmlHttpRequest('GET', '/autocomplete?query=star');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertCount(5, $data);

        foreach ($data as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('year', $item);

            $this->assertIsInt($item['id']);
            $this->assertIsString($item['title']);
            $this->assertIsString($item['year']);
            $this->assertMatchesRegularExpression('/^\d{4}$/', $item['year']);
        }

        // Test specific values
        $this->assertEquals(52959, $data[0]['id']);
        $this->assertEquals('Star!', $data[0]['title']);
        $this->assertEquals('1968', $data[0]['year']);

        $this->assertEquals(1225239, $data[4]['id']);
        $this->assertEquals('Star', $data[4]['title']);
        $this->assertEquals('2025', $data[4]['year']);
    }

    private function getTestMoviesData(): array
    {
        return [
            'featuredMovie' => [
                'id' => 52959,
                'title' => 'Star!',
                'overview' => 'Gertrude Lawrence rises to stage stardom at the cost of happiness.',
                'voteAverage' => 6.9,
                'voteCount' => 220,
                'posterPath' => '/xMyOm5hLWeuoWBea1DJrcJy8bU6.jpg',
                'backdropPath' => '/l1wcKPBvB4FKXpvG6rD8FbpjvPT.jpg',
                'trailerUrl' => null,
                'releaseDate' => '1968-07-18',
                'releaseYear' => 1968,
            ],
            'movies' => [
                [
                    'id' => 136244,
                    'title' => 'Star',
                    'overview' => 'When Crystal Wyatt was 16 her father passed away. From that time her life would never be the same. Crystal is banished from the family ranch, and starts a new life.',
                    'voteAverage' => 6.1,
                    'voteCount' => 143,
                    'posterPath' => '/jYmt7JBvAmRJ0FxPx2UayOQ1uOB.jpg',
                    'backdropPath' => '/a1lMRUeGozBAczWaSoU1p3zLmGs.jpg',
                    'trailerUrl' => null,
                    'releaseDate' => '1993-09-20',
                    'releaseYear' => 1993,
                ],
                // ... 18 more movie entries ...
            ],
            'currentPage' => 1,
            'totalPages' => 264,
            'search' => 'star',
            'genres' => [
                ['id' => 'all', 'name' => 'Tous les genres'],
                // ... 19 more genre entries ...
            ],
            'selectedGenre' => '12',
            'error' => null,
        ];
    }
}
