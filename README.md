# Application de Gestion de Films

# Application de Gestion de Films (We Movies)

## Table des matières
1. [Description du Projet](#description-du-projet)
2. [Technologies Utilisées](#technologies-utilisées)
3. [Architecture](#architecture)
4. [Installation](#installation)
5. [Utilisation](#utilisation)
6. [Structure du Projet](#structure-du-projet)
7. [Services et Interfaces](#services-et-interfaces)
8. [Tests](#tests)
9. [Qualité du Code](#qualité-du-code)
10. [Contribution](#contribution)

## Description du Projet
Cette application web est une plateforme de gestion de films permettant aux utilisateurs de rechercher, filtrer, et noter des films. Elle utilise l'API TMDb pour récupérer les informations sur les films.

## Technologies Utilisées
- **Symfony 7** : Framework PHP pour le backend
- **Twig** : Moteur de template pour les vues
- **Webpack Encore** : Compilation des assets
- **Bootstrap** : Framework CSS pour le design responsive
- **API TMDb** : Source des données sur les films
- **Docker** : Conteneurisation de l'application

## Architecture
Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) avec les composants suivants :
- **Controller** : Gestion des requêtes HTTP
- **Service** : Encapsulation de la logique métier
- **Templates** : Gestion de l'affichage
- **JavaScript** : Interactions côté client

## Installation
1. Cloner le dépôt :
```shellscript
   git clone https://github.com/clabiadh/we-movies.git
   cd movie-app
```
2. Configuration :

```shellscript
# Créer le fichier .env.local et ajouter la clé API TMDb
echo "TMDB_API_KEY=votre_clé_api_ici" > .env.local
```
3. Lancement avec Docker :

```shellscript
docker-compose up -d --build
docker-compose exec php composer install
```
4. Accès : [http://localhost:8080](http://localhost:8080)

## Utilisation

### Démarrage et Arrêt

```shellscript
# Arrêter l'application
docker-compose down

# Relancer l'application
docker-compose up -d
```

## Structure du Projet

```plaintext
src/
├── Controller/    # Contrôleurs de l'application
├── Service/       # Services métier
├── Serializer/    # Normalizers pour la sérialisation des données
├── DTO/           # Data Transfer Objects
├── Interface/     # Interfaces des services
templates/         # Templates Twig
public/           # Assets publics
assets/           # Sources JS/CSS
tests/            # Tests unitaires et fonctionnels
```

## Services et Interfaces

### ApiDataServiceInterface

Interface pour l'interaction avec l'API TMDb :

```php
fetchData(string $endpoint, array $params = []): array
getPopularMovies(int $page = 1): array
searchMovies(string $query, int $page = 1): array
getMoviesByGenre(string $genreId, int $page = 1): array
getGenres(): array
getMovieDetails(int $movieId): array
```

### MovieServiceInterface

Interface pour la logique métier des films :

```php
getMovies(Request $request): array
getMovieDetails(int $id): array
autocompleteSearch(string $query): array
```

### TMDBApiServiceInterface

Interface d'abstraction pour l'API TMDb :

```php
getPopularMovies(int $page = 1): array
searchMovies(string $query, int $page = 1): array
getMoviesByGenre(string $genreId, int $page = 1): array
getGenres(): array
getMovieDetails(int $movieId): array
testConnection(): array
```

## Tests

### Tests Unitaires

```shellscript
docker-compose exec php bin/phpunit
```

## Qualité du Code

### PHPStan

```shellscript
# Analyse statique du code
docker-compose exec php composer phpstan
```

### PHP CS Fixer

```shellscript
# Vérification du style
docker-compose exec php composer cs-check

# Correction automatique
docker-compose exec php composer cs-fix
```

## Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Forker le projet
2. Créer une branche pour votre fonctionnalité
3. Soumettre une Pull Request