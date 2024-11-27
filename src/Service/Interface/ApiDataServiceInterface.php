<?php

namespace App\Service\Interface;

interface ApiDataServiceInterface
{
    public function fetchData(string $endpoint, array $params = []): array;

    public function getPopularMovies(int $page = 1): array;

    public function searchMovies(string $query, int $page = 1): array;

    public function getMoviesByGenre(string $genreId, int $page = 1): array;

    public function getGenres(): array;

    public function getMovieDetails(int $movieId): array;
}
