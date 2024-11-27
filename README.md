# Application de Gestion de Films

## Description du Projet

Cette application web est une plateforme de gestion de films permettant aux utilisateurs de rechercher, filtrer, et noter des films. Elle utilise l'API TMDb pour récupérer les informations sur les films.

## Architecture du Projet

Le projet est construit avec Symfony 7 et suit une architecture MVC (Modèle-Vue-Contrôleur) avec les composants suivants :

- **Controller** : `MainController` gère les requêtes HTTP et coordonne les interactions entre les services et les vues.
- **Service** : `MovieService` encapsule la logique métier liée aux films.
- **Templates** : Les vues Twig dans le dossier `templates/` gèrent l'affichage.
- **JavaScript** : `app.js` gère les interactions côté client.

## Services

- `MovieService` : Gère toutes les opérations liées aux films, y compris la récupération des données depuis l'API TMDb, le filtrage, la recherche, et la gestion des notes.

## Installation avec Docker

1. Clonez le dépôt : git clone [https://github.com/clabiadh/we-movies.git](https://github.com/clabiadh/we-movies.git)
   cd movie-app
2. Créez un fichier `.env.local` à la racine du projet et ajoutez votre clé API TMDb : TMDB_API_KEY=votre_clé_api_ici
3. Construisez et lancez les conteneurs Docker : docker-compose up -d --build
4. Installez les dépendances : docker-compose exec php composer install

5. L'application devrait maintenant être accessible à l'adresse `http://localhost:8080/`

## Lancement de l'Application

Une fois les conteneurs Docker en cours d'exécution, l'application est automatiquement lancée et accessible. Pour arrêter l'application, utilisez : docker-compose down
Pour relancer l'application : docker-compose up -d

## Exécution des Tests Unitaires

Pour exécuter les tests unitaires : docker-compose exec php bin/phpunit

## Composants du Projet

- **Symfony 7** : Framework PHP pour le backend
- **Twig** : Moteur de template pour les vues
- **Webpack Encore** : Compilation des assets
- **Bootstrap** : Framework CSS pour le design responsive
- **API TMDb** : Source des données sur les films

## Structure du Projet

- `src/Controller/` : Contrôleurs de l'application
- `src/Service/` : Services métier
- `templates/` : Templates Twig
- `public/` : Assets publics et point d'entrée
- `assets/` : Fichiers JavaScript et CSS source
- `tests/` : Tests unitaires et fonctionnels

## Contribution

Les contributions sont les bienvenues ! Veuillez créer une issue ou une pull request pour toute suggestion ou amélioration.


