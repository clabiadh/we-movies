<?php

namespace App\Serializer;

use App\DTO\MovieDTO;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MovieDTONormalizer implements NormalizerInterface
{
    /**
     * @param MovieDTO $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof MovieDTO) {
            throw new \InvalidArgumentException('The object must be an instance of MovieDTO');
        }

        return [
            'id' => $object->id,
            'title' => $object->title,
            'overview' => $object->overview,
            'voteAverage' => $object->voteAverage,
            'voteCount' => $object->voteCount,
            'posterPath' => $object->posterPath,
            'trailerUrl' => $object->trailerUrl,
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof MovieDTO;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MovieDTO::class => true,
        ];
    }
}