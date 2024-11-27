<?php

namespace App\Serializer;

use App\DTO\GenreDTO;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GenreDTONormalizer implements NormalizerInterface
{
    /**
     * @param GenreDTO $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof GenreDTO) {
            throw new \InvalidArgumentException('The object must be an instance of GenreDTO');
        }

        return [
            'id' => $object->id,
            'name' => $object->name,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GenreDTO;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GenreDTO::class => true,
        ];
    }
}
