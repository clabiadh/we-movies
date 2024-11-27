<?php

namespace App\Service\Interface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

interface MovieServiceInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function getMovies(Request $request): array;

    /**
     * @throws ExceptionInterface
     */
    public function getMovieDetails(int $id): array;

    public function autocompleteSearch(string $query): array;
}
