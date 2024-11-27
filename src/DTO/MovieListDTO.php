<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class MovieListDTO
{
    /**
     * @param MovieDTO[] $movies
     * @param GenreDTO[] $genres
     */
    public function __construct(
        #[Groups(['movie_list'])]
        public readonly ?MovieDTO $featuredMovie,

        #[Groups(['movie_list'])]
        public readonly array $movies,

        #[Groups(['movie_list'])]
        public readonly int $currentPage,

        #[Groups(['movie_list'])]
        public readonly int $totalPages,

        #[Groups(['movie_list'])]
        public readonly array $genres,

        #[Groups(['movie_list'])]
        public readonly string $selectedGenre,

        #[Groups(['movie_list'])]
        public readonly ?string $search = null,

        #[Groups(['movie_list'])]
        public readonly ?string $error = null
    ) {
    }
}