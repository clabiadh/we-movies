<?php


namespace App\Serializer;

use App\DTO\MovieListDTO;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MovieListDTONormalizer implements NormalizerInterface
{
    public function __construct(
        private MovieDTONormalizer $movieDTONormalizer,
        private GenreDTONormalizer $genreDTONormalizer
    ) {
    }

    /**
     * @param MovieListDTO $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof MovieListDTO) {
            throw new \InvalidArgumentException('The object must be an instance of MovieListDTO');
        }

        return [
            'featuredMovie' => $object->featuredMovie ? $this->movieDTONormalizer->normalize(
                $object->featuredMovie
            ) : null,
            'movies' => array_map([$this->movieDTONormalizer, 'normalize'], $object->movies),
            'currentPage' => $object->currentPage,
            'totalPages' => $object->totalPages,
            'search' => $object->search,
            'genres' => array_map([$this->genreDTONormalizer, 'normalize'], $object->genres),
            'selectedGenre' => $object->selectedGenre,
            'error' => $object->error,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MovieListDTO;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MovieListDTO::class => true,
        ];
    }
}