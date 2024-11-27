<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class MovieDTO
{
    public function __construct(
        #[Groups(['movie_details'])]
        public readonly int $id,

        #[Groups(['movie_details'])]
        public readonly string $title,

        #[Groups(['movie_details'])]
        public readonly string $overview,

        #[Groups(['movie_details'])]
        public readonly float $voteAverage,

        #[Groups(['movie_details'])]
        public readonly int $voteCount,

        #[Groups(['movie_details'])]
        public readonly ?string $posterPath,

        #[Groups(['movie_details'])]
        public readonly ?string $backdropPath,

        #[Groups(['movie_details'])]
        public readonly ?string $trailerUrl,

        #[Groups(['movie_details'])]
        public readonly ?string $releaseDate,
    ) {
    }

    #[Groups(['movie_details'])]
    public function getReleaseYear(): ?int
    {
        return $this->releaseDate ? (int) substr($this->releaseDate, 0, 4) : null;
    }
}
