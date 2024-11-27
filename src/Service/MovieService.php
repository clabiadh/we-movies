<?php
// src/Service/MovieService.php

namespace App\Service;

use App\DTO\MovieDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class MovieService
{
    public function __construct(
        private readonly TMDBApiService $tmdbApiService,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * Récupère les films en fonction des paramètres de la requête.
     *
     * @param Request $request La requête HTTP contenant les paramètres de filtrage
     * @return array Les données des films et les métadonnées associées
     */
    public function getMovies(Request $request): array
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search');
        $genre = $request->query->get('genre');

        try {
            $moviesData = $this->fetchMoviesData($search, $genre, $page);
            $movies = $moviesData['results'] ?? [];
            $featuredMovie = !empty($movies) ? array_shift($movies) : null;
            $totalPages = $moviesData['total_pages'] ?? 1;

            if (empty($movies) && empty($featuredMovie)) {
                throw new \Exception('Aucun film trouvé');
            }

            $genres = $this->getGenresWithAll();

            return [
                'featuredMovie' => $featuredMovie,
                'movies' => $movies,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'genres' => $genres,
                'selectedGenre' => $genre ?? 'all',
            ];
        } catch (\Exception $e) {
            // Log l'erreur ici si nécessaire
            return [
                'error' => $e->getMessage(),
                'movies' => [],
                'featuredMovie' => null,
                'totalPages' => 1,
                'genres' => $this->getGenresWithAll(),
                'selectedGenre' => $genre ?? 'all',
            ];
        }
    }

    /**
     * Récupère les détails d'un film spécifique.
     *
     * @param int $id L'identifiant du film
     * @return MovieDTO Les détails du film
     */
    public function getMovieDetails(int $id): MovieDTO
    {
        $movieDetails = $this->tmdbApiService->getMovieDetails($id);

        return new MovieDTO(
            $movieDetails['id'],
            $movieDetails['title'],
            $movieDetails['overview'],
            $movieDetails['vote_average'],
            $movieDetails['vote_count'],
            $movieDetails['poster_path'] ?? null,
            $this->getTrailerUrl($movieDetails),
            $movieDetails['release_date'] ?? null  // Ajout de la date de sortie
        );
    }

    /**
     * Effectue une recherche de films pour l'autocomplétion.
     *
     * @param string $query Le terme de recherche
     * @return array Les résultats de la recherche formatés
     */
    public function autocompleteSearch(string $query): array
    {
        $results = $this->tmdbApiService->searchMovies($query, 1);
        $movies = array_slice($results['results'], 0, 5); // Limite à 5 résultats

        return array_map(function($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'year' => substr($movie['release_date'], 0, 4)
            ];
        }, $movies);
    }

    /**
     * Récupère les données des films en fonction des critères de recherche.
     *
     * @param string|null $search Le terme de recherche
     * @param string|null $genre L'identifiant du genre
     * @param int $page Le numéro de page
     * @return array Les données des films
     */
    private function fetchMoviesData(?string $search, ?string $genre, int $page): array
    {
        return match(true) {
            !empty($search) => $this->tmdbApiService->searchMovies($search, $page),
            !empty($genre) && $genre !== 'all' => $this->tmdbApiService->getMoviesByGenre($genre, $page),
            default => $this->tmdbApiService->getPopularMovies($page),
        };
    }

    /**
     * Récupère la liste des genres avec l'option "Tous les genres" en premier.
     *
     * @return array La liste des genres
     */
    private function getGenresWithAll(): array
    {
        $genres = $this->tmdbApiService->getGenres();
        array_unshift($genres, ['id' => 'all', 'name' => 'Tous les genres']);

        return $genres;
    }

    /**
     * Extrait l'URL de la bande-annonce des détails du film.
     *
     * @param array $movieDetails Les détails du film
     * @return string|null L'URL de la bande-annonce YouTube ou null si non trouvée
     */
    private function getTrailerUrl(array $movieDetails): ?string
    {
        $videos = $movieDetails['videos']['results'] ?? [];
        foreach ($videos as $video) {
            if ($video['site'] === 'YouTube') {
                return "https://www.youtube.com/embed/{$video['key']}";
            }
        }
        return null;
    }
}