<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiDataService
{
    public function __construct(
        #[Autowire(service: 'tmdb.client')]
        private HttpClientInterface $tmdbClient,
        #[Autowire('%env(TMDB_API_TOKEN)%')]
        private string $apiToken,
        private LoggerInterface $logger,
    ) {
    }

    public function fetchData(string $endpoint, array $params = []): array
    {
        $this->logger->info('Sending request to TMDB API', [
            'endpoint' => $endpoint,
            'params' => $params,
        ]);

        try {
            $response = $this->tmdbClient->request('GET', $endpoint, [
                'query' => $params,
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiToken,
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('Received response from TMDB API', [
                'statusCode' => $statusCode,
                'endpoint' => $endpoint,
            ]);

            if (200 !== $statusCode) {
                $content = $response->getContent(false);
                $this->logger->error('Error response from TMDB API', [
                    'statusCode' => $statusCode,
                    'content' => $content,
                    'endpoint' => $endpoint,
                ]);
                throw new \RuntimeException("Failed to fetch data from TMDB API. Status code: $statusCode, Content: $content");
            }

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Exception while fetching data from TMDB API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'endpoint' => $endpoint,
            ]);

            throw $e;
        }
    }

    public function getPopularMovies(int $page = 1): array
    {
        return $this->fetchData('movie/popular', ['page' => $page]);
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        return $this->fetchData('search/movie', ['query' => $query, 'page' => $page]);
    }

    public function getMoviesByGenre(string $genreId, int $page = 1): array
    {
        return $this->fetchData('discover/movie', ['with_genres' => $genreId, 'page' => $page]);
    }

    public function getGenres(): array
    {
        $response = $this->fetchData('genre/movie/list');

        return $response['genres'] ?? [];
    }

    public function getMovieDetails(int $movieId): array
    {
        return $this->fetchData("movie/$movieId", ['append_to_response' => 'videos,credits']);
    }
}
