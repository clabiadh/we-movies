<?php
// tests/Controller/MainControllerTest.php

namespace App\Tests\Controller;

use App\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class MainControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->client->getContainer()->set('session', new Session(new MockArraySessionStorage()));
    }

    public function testHomepage()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'A propos de We Movies');
        $this->assertCount(19, $crawler->filter('.movie-card')); // Assuming 19 movies per page
    }

    public function testMovieDetails()
    {
        $crawler = $this->client->request('GET', '/');
        $movieId = $crawler->filter('.movie-card')->first()->attr('data-movie-id');

        $this->client->xmlHttpRequest('GET', "/movie/{$movieId}");
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('overview', $data);
        $this->assertArrayHasKey('voteAverage', $data);
        $this->assertArrayHasKey('voteCount', $data);
    }

    public function testSearchMovie()
    {
        $crawler = $this->client->request('GET', '/?search=inception');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('inception', strtolower($crawler->filter('.movie-card')->first()->text()));
    }

    public function testFilterByGenre()
    {
        $crawler = $this->client->request('GET', '/');
        $genreId = $crawler->filter('#genre-form input[name="genre"]')->first()->attr('value');

        $crawler = $this->client->request('GET', "/?genre={$genreId}");

        $this->assertResponseIsSuccessful();
        $this->assertCount(19, $crawler->filter('.movie-card')); // Assuming 19 movies per page
    }

    public function testResetFilters()
    {
        $crawler = $this->client->request('GET', '/?search=inception&genre=28');
        $this->assertStringContainsString('inception', strtolower($crawler->filter('.movie-card')->first()->text()));

        $crawler = $this->client->request('GET', '/');
        $this->assertCount(19, $crawler->filter('.movie-card')); // Assuming 19 movies per page
    }

    public function testPagination()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertCount(19, $crawler->filter('.movie-card')); // First page

        $crawler = $this->client->request('GET', '/?page=2');
        $this->assertCount(19, $crawler->filter('.movie-card')); // Second page
        $this->assertNotEquals(
            $crawler->filter('.movie-card')->first()->attr('data-movie-id'),
            $this->client->request('GET', '/')->filter('.movie-card')->first()->attr('data-movie-id')
        );
    }

    public function testAutocomplete()
    {
        $this->client->xmlHttpRequest('GET', '/autocomplete?query=star');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey('title', $data[0]);
        $this->assertArrayHasKey('year', $data[0]);
    }

    public function testInvalidMovieId()
    {
        $this->client->xmlHttpRequest('GET', '/movie/999999999');
        $this->assertResponseStatusCodeSame(500);
    }

    public function testInvalidPage()
    {
        $crawler = $this->client->request('GET', '/?page=9999');
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('.movie-card'));
    }

    public function testEmptySearchResult()
    {
        // Mock the MovieService
        $movieServiceMock = $this->createMock(MovieService::class);
        $movieServiceMock->method('getMovies')->willReturn([
            'movies' => [],
            'featuredMovie' => null,
            'genres' => [],
            'currentPage' => 1,
            'totalPages' => 0,
        ]);

        // Replace the real service with the mock
        $this->client->getContainer()->set(MovieService::class, $movieServiceMock);

        $crawler = $this->client->request('GET', '/?search=xyznonexistentmovie');
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('.movie-card'));

        // Check for the error message in the response content
        $this->assertStringContainsString('Désolé, aucun film n\'a été trouvé ou il y a eu un problème de chargement. Veuillez réessayer ou choisir un autre film.', $crawler->filter('.error-message')->text());
    }
}