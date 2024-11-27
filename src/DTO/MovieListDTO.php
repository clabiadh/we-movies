<?php

namespace App\DTO;

class MovieListDTO
{
    /**
     * @param MovieDTO[] $movies
     */
    public function __construct(
        public readonly ?MovieDTO $featuredMovie,
        public readonly array $movies,
        public readonly int $currentPage,
        public readonly int $totalPages,
        public readonly ?string $search,
        public readonly array $genres,
        public readonly string $selectedGenre
    ) {}
}