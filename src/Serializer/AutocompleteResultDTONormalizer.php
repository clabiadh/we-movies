<?php

namespace App\Serializer;

use App\DTO\AutocompleteResultDTO;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AutocompleteResultDTONormalizer implements NormalizerInterface
{
    /**
     * @param AutocompleteResultDTO $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof AutocompleteResultDTO) {
            throw new \InvalidArgumentException('The object must be an instance of AutocompleteResultDTO');
        }

        return [
            'id' => $object->id,
            'title' => $object->title,
            'year' => $object->year,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AutocompleteResultDTO;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AutocompleteResultDTO::class => true,
        ];
    }
}