<?php
// src/Controller/MainController.php

namespace App\Controller;

use App\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MainController extends AbstractController
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * Page d'accueil affichant la liste des films.
     */
    #[Route('/', name: 'app_main')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $movieData = $this->movieService->getMovies($request);

        if (isset($movieData['error'])) {
            $this->addFlash('error', $movieData['error']);
        } elseif (empty($movieData['movies']) && empty($movieData['featuredMovie'])) {
            $this->addFlash('error', 'Aucun film trouvé. Veuillez réessayer avec une autre recherche.');
        }

        return $this->render('main/index.html.twig', array_merge(
            $movieData,
            ['search' => $search]
        ));
    }

    /**
     * Endpoint pour récupérer les détails d'un film.
     */
    #[Route('/movie/{id}', name: 'movie_details')]
    public function movieDetails(int $id): JsonResponse
    {
        $movieDTO = $this->movieService->getMovieDetails($id);
        $jsonContent = $this->serializer->serialize($movieDTO, 'json', ['groups' => 'movie_details']);

        return new JsonResponse($jsonContent, 200, [], true);
    }

    /**
     * Endpoint pour l'autocomplétion de la recherche de films.
     */
    #[Route('/autocomplete', name: 'movie_autocomplete')]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('query', '');

        return $this->json($this->movieService->autocompleteSearch($query));
    }
}