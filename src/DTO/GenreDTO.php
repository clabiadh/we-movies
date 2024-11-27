<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class GenreDTO
{
    public function __construct(
        #[Groups(['genre'])]
        public readonly string $id,

        #[Groups(['genre'])]
        public readonly string $name,
    ) {
    }
}
