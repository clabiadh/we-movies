<?php

namespace App\DTO;

class AutocompleteResultDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $year
    ) {}
}