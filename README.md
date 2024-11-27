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

### Tests Fonctionnels

Les tests fonctionnels sont une partie cruciale de notre suite de tests. Ils vérifient le comportement de l'application du point de vue de l'utilisateur final, en simulant des interactions réelles avec l'interface utilisateur.

Pour exécuter les tests fonctionnels :

```shellscript
docker-compose exec php bin/phpunit tests/
ou
php bin/phpunit --no-coverage tests/Controller/MainControllerTest.php > rapport_tests.txt

avec ou non l'option  --colors=never
```

Nos tests fonctionnels couvrent plusieurs scénarios, notamment :

- Navigation sur la page d'accueil
- Recherche de films
- Filtrage par genre
- Affichage des détails d'un film
- Fonctionnalité d'autocomplétion


### Principe de Mock dans les Tests

Le mocking est une technique essentielle dans nos tests unitaires et fonctionnels. Elle nous permet de simuler le comportement de dépendances externes ou de composants complexes de notre application.

Principaux avantages du mocking dans notre projet :

1. **Isolation des composants** : Nous pouvons tester des parties spécifiques de notre application sans dépendre du bon fonctionnement d'autres composants.
2. **Contrôle des scénarios de test** : Les mocks nous permettent de simuler différentes réponses de l'API TMDB, y compris des cas d'erreur, sans avoir à manipuler l'API réelle.
3. **Rapidité et fiabilité des tests** : En évitant les appels réseau réels, nos tests s'exécutent plus rapidement et de manière plus fiable.
4. **Reproduction de scénarios spécifiques** : Nous pouvons facilement reproduire des scénarios difficiles à obtenir avec l'API réelle.


Exemple de mock dans un de nos tests :

```php
public function testMovieSearch()
{
    $mockTMDBApiService = $this->createMock(TMDBApiServiceInterface::class);
    $mockTMDBApiService->method('searchMovies')
        ->willReturn([
            'results' => [
                [
                    'id' => 11,
                    'title' => 'Star Wars',
                    'release_date' => '1977-05-25',
                    'vote_average' => 8.6,
                    'poster_path' => '/path/to/poster.jpg'
                ],
                // ... autres résultats de films
            ]
        ]);

    $movieService = new MovieService($mockTMDBApiService);
    $result = $movieService->searchMovies('Star Wars');

    $this->assertCount(1, $result);
    $this->assertEquals('Star Wars', $result[0]['title']);
    $this->assertEquals(1977, $result[0]['year']);
}
```

Dans cet exemple :

1. Nous créons un mock du `TMDBApiService`.
2. Nous définissons le comportement attendu de la méthode `searchMovies`.
3. Nous injectons ce mock dans notre `MovieService`.
4. Nous testons la méthode `searchMovies` du `MovieService` sans effectuer d'appel API réel.


Cette approche nous permet de tester la logique de notre `MovieService` indépendamment de l'API TMDB, assurant ainsi des tests rapides, fiables et reproductibles.

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

### PHP_CodeSniffer
Pour Symfony 7, il est effectivement recommandé d'utiliser PSR-12 avec PHP_CodeSniffer. Voici pourquoi :

1. PSR-12 est la norme la plus récente et la plus complète pour le style de codage PHP. Elle étend et remplace PSR-2, qui était précédemment utilisé.
2. Symfony suit de près les standards PSR et recommande leur utilisation. Bien que Symfony ait ses propres conventions de codage, elles sont largement basées sur PSR-12.
3. PSR-12 est compatible avec les versions PHP modernes, ce qui correspond bien à Symfony 7 qui utilise PHP 8.2+.
4. Il couvre un large éventail de règles de style, y compris l'utilisation des nouvelles fonctionnalités de PHP comme les types de retour nullable, les types d'union, etc.

```shellscript
# Vérification du style
docker-compose exec php composer phpcs

# Correction automatique
docker-compose exec php composer phpcbf
```

## Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Forker le projet
2. Créer une branche pour votre fonctionnalité
3. Soumettre une Pull Request