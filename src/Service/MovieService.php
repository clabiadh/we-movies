<?php

// src/Service/MovieService.php

namespace App\Service;

use App\DTO\AutocompleteResultDTO;
use App\DTO\GenreDTO;
use App\DTO\MovieDTO;
use App\DTO\MovieListDTO;
use App\Serializer\AutocompleteResultDTONormalizer;
use App\Serializer\GenreDTONormalizer;
use App\Serializer\MovieDTONormalizer;
use App\Serializer\MovieListDTONormalizer;
use Symfony\Component\HttpFoundation\Request;

class MovieService
{
    public function __construct(
        private readonly TMDBApiService $tmdbApiService,
        private AutocompleteResultDTONormalizer $autocompleteResultDTONormalizer,
        private MovieDTONormalizer $movieDTONormalizer,
        private GenreDTONormalizer $genreDTONormalizer,
        private MovieListDTONormalizer $movieListDTONormalizer
    ) {
    }

    /**
     * Récupère les films en fonction des paramètres de la requête.
     *
     * @param Request $request La requête HTTP contenant les paramètres de filtrage
     *
     * @return array Les données des films et les métadonnées associées normalisées
     */
    public function getMovies(Request $request): array
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search');
        $genre = $request->query->get('genre', 'all');

        try {
            $moviesData = $this->fetchMoviesData($search, $genre, $page);
            $movies = $moviesData['results'] ?? [];
            $featuredMovie = !empty($movies) ? array_shift($movies) : null;
            $totalPages = $moviesData['total_pages'] ?? 1;

            if (empty($movies) && empty($featuredMovie)) {
                throw new \Exception('Aucun film trouvé');
            }

            $movieListDTO = new MovieListDTO(
                featuredMovie: $featuredMovie ? $this->createMovieDTO($featuredMovie) : null,
                movies: array_map([$this, 'createMovieDTO'], $movies),
                currentPage: $page,
                totalPages: $totalPages,
                genres: $this->getGenresWithAll(), // Ceci retourne maintenant un tableau de GenreDTO
                selectedGenre: $genre,
                search: $search
            );

            return $this->movieListDTONormalizer->normalize($movieListDTO);
        } catch (\Exception $e) {
            $movieListDTO = new MovieListDTO(
                featuredMovie: null,
                movies: [],
                currentPage: $page,
                totalPages: 1,
                genres: $this->getGenresWithAll(), // Ceci retourne maintenant un tableau de GenreDTO
                selectedGenre: $genre,
                search: $search,
                error: $e->getMessage()
            );

            return $this->movieListDTONormalizer->normalize($movieListDTO);
        }
    }

    private function createMovieDTO(array $movieData): MovieDTO
    {
        return new MovieDTO(
            $movieData['id'],
            $movieData['title'],
            $movieData['overview'],
            $movieData['vote_average'],
            $movieData['vote_count'],
            $movieData['poster_path'] ?? null,
            $movieData['backdrop_path'] ?? null,
            $this->getTrailerUrl($movieData),
            $movieData['release_date'] ?? null
        );
    }

    /**
     * Récupère les détails d'un film spécifique.
     *
     * @param int $id L'identifiant du film
     *
     * @return array Les détails du film normalisés
     */
    public function getMovieDetails(int $id): array
    {
        $movieDetails = $this->tmdbApiService->getMovieDetails($id);
        $movieDTO = $this->createMovieDTO($movieDetails);

        return $this->movieDTONormalizer->normalize($movieDTO);
    }

    /**
     * Effectue une recherche de films pour l'autocomplétion.
     *
     * @param string $query Le terme de recherche
     *
     * @return array Les résultats de la recherche formatés
     */
    public function autocompleteSearch(string $query): array
    {
        $results = $this->tmdbApiService->searchMovies($query, 1);
        $movies = array_slice($results['results'], 0, 5); // Limite à 5 résultats

        $dtos = array_map(function ($movie) {
            return new AutocompleteResultDTO(
                id: $movie['id'],
                title: $movie['title'],
                year: substr($movie['release_date'], 0, 4)
            );
        }, $movies);

        return array_map(
            fn(AutocompleteResultDTO $dto) => $this->autocompleteResultDTONormalizer->normalize($dto),
            $dtos
        );
    }

    /**
     * Récupère les données des films en fonction des critères de recherche.
     *
     * @param string|null $search Le terme de recherche
     * @param string|null $genre  L'identifiant du genre
     * @param int         $page   Le numéro de page
     *
     * @return array Les données des films
     */
    private function fetchMoviesData(?string $search, ?string $genre, int $page): array
    {
        return match (true) {
            !empty($search) => $this->tmdbApiService->searchMovies($search, $page),
            !empty($genre) && 'all' !== $genre => $this->tmdbApiService->getMoviesByGenre($genre, $page),
            default => $this->tmdbApiService->getPopularMovies($page),
        };
    }

    /**
     * Récupère la liste des genres avec l'option "Tous les genres" en premier.
     *
     * @return GenreDTO[] La liste des genres
     */
    private function getGenresWithAll(): array
    {
        $genres = $this->tmdbApiService->getGenres();
        $genreDTOs = array_map(function($genre) {
            return new GenreDTO((string)$genre['id'], $genre['name']);
        }, $genres);

        array_unshift($genreDTOs, new GenreDTO('all', 'Tous les genres'));

        return $genreDTOs;
    }

    /**
     * Extrait l'URL de la bande-annonce des détails du film.
     *
     * @param array $movieDetails Les détails du film
     *
     * @return string|null L'URL de la bande-annonce YouTube ou null si non trouvée
     */
    private function getTrailerUrl(array $movieDetails): ?string
    {
        $videos = $movieDetails['videos']['results'] ?? [];
        //dd($movieDetails);
        foreach ($videos as $video) {
            if ('YouTube' === $video['site']) {
                return "https://www.youtube.com/embed/{$video['key']}";
            }
        }

        return null;
    }
}
