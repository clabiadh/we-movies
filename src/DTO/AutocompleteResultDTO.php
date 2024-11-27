<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class AutocompleteResultDTO
{
    public function __construct(
        #[Groups(['autocomplete'])]
        public readonly int $id,
        #[Groups(['autocomplete'])]
        public readonly string $title,
        #[Groups(['autocomplete'])]
        public readonly string $year,
    ) {
    }
}
