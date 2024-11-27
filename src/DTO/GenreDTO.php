<?php

namespace App\DTO;

class GenreDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}