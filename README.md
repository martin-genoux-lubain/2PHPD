# Plateforme de gestion de tournois sportifs

Application Symfony permettant de gérer des tournois sportifs, des joueurs, des inscriptions et des matchs. Le projet fournit une interface web, une interface d'administration EasyAdmin, une API REST via API Platform, des fixtures de démonstration et une commande Symfony de statistiques utilisateur.

## Stack technique

- PHP 8.4+
- Symfony 8
- Doctrine ORM et Doctrine Migrations
- SQLite
- Symfony Security
- Twig
- PHPUnit 13
- Doctrine Fixtures et Faker

## Fonctionnalités

- Inscription et connexion des utilisateurs.
- Gestion des rôles `ROLE_USER` et `ROLE_ADMIN`.
- Consultation des tournois publics.
- Création de tournois par les utilisateurs connectés.
- Modification des tournois par les administrateurs.
- Gestion centralisée des utilisateurs, tournois, inscriptions et matchs depuis EasyAdmin.
- API REST pour les joueurs, tournois, inscriptions et matchs.
- Statut dynamique des tournois selon les dates : `à venir`, `en cours`, `terminé`.
- Protection des opérations sensibles par règles de sécurité Symfony/API Platform.
- Fixtures avec un administrateur, des joueurs, des tournois, des inscriptions et des matchs.
- Commande CLI pour afficher les victoires, défaites et matchs nuls d'un utilisateur.

## Prérequis

- PHP 8.4 ou supérieur
- Composer
- Symfony CLI, recommandé pour lancer le serveur local

## Installation

```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

La commande de fixtures vide les tables avant de charger les données de démonstration.

## Configuration

La configuration locale se fait dans `.env.local`.

## Lancement

```bash
symfony server:start
```

URLs principales :

| Zone | URL |
| --- | --- |
| Accueil | `http://127.0.0.1:8000/` |
| Tournois | `http://127.0.0.1:8000/tournois` |
| Connexion | `http://127.0.0.1:8000/login` |
| Inscription | `http://127.0.0.1:8000/inscription` |
| Administration | `http://127.0.0.1:8000/admin` |
| API Platform | `http://127.0.0.1:8000/api` |

## Comptes de démonstration

Les fixtures créent un administrateur :

| Identifiant | Mot de passe | Rôle |
| --- | --- | --- |
| `admin` | `password` | `ROLE_ADMIN` |

Les joueurs générés par Faker utilisent aussi le mot de passe `password`.

## Routes web

| Méthode | Route | Accès | Description |
| --- | --- | --- | --- |
| `GET` | `/` | Public | Page d'accueil |
| `GET` | `/tournois` | Public | Liste des tournois |
| `GET` | `/tournois/{id}` | Public | Détail d'un tournoi |
| `GET`, `POST` | `/tournois/nouveau` | `ROLE_USER` | Création d'un tournoi |
| `GET`, `POST` | `/tournois/{id}/modifier` | `ROLE_ADMIN` | Modification d'un tournoi |
| `GET`, `POST` | `/inscription` | Public | Création d'un compte |
| `GET`, `POST` | `/login` | Public | Connexion |
| `GET` | `/logout` | Utilisateur connecté | Déconnexion |
| `GET` | `/admin` | `ROLE_ADMIN` | Dashboard EasyAdmin |

## API REST

Toutes les routes API sont préfixées par `/api`.

### Joueurs

| Méthode | Route | Accès |
| --- | --- | --- |
| `GET` | `/api/players` | Public |
| `GET` | `/api/players/{id}` | Public |
| `POST` | `/api/register` | Public |
| `POST` | `/api/players` | `ROLE_ADMIN` |
| `PUT` | `/api/players/{id}` | Propriétaire ou `ROLE_ADMIN` |
| `DELETE` | `/api/players/{id}` | Propriétaire ou `ROLE_ADMIN` |

### Tournois

| Méthode | Route | Accès |
| --- | --- | --- |
| `GET` | `/api/tournaments` | Public |
| `GET` | `/api/tournaments/{id}` | Public |
| `POST` | `/api/tournaments` | `ROLE_USER` |
| `PUT` | `/api/tournaments/{id}` | Organisateur ou `ROLE_ADMIN` |
| `DELETE` | `/api/tournaments/{id}` | Organisateur ou `ROLE_ADMIN` |

### Inscriptions

| Méthode | Route | Accès |
| --- | --- | --- |
| `GET` | `/api/tournaments/{tournamentId}/registrations` | Public |
| `POST` | `/api/tournaments/{tournamentId}/registrations` | `ROLE_USER` |
| `DELETE` | `/api/tournaments/{tournamentId}/registrations/{id}` | Joueur inscrit ou `ROLE_ADMIN` |

Une contrainte d'unicité empêche un joueur de s'inscrire plusieurs fois au même tournoi.

### Matchs

| Méthode | Route | Accès |
| --- | --- | --- |
| `GET` | `/api/tournaments/{tournamentId}/sport-matchs` | Public |
| `GET` | `/api/tournaments/{tournamentId}/sport-matchs/{id}` | Public |
| `POST` | `/api/tournaments/{tournamentId}/sport-matchs` | `ROLE_ADMIN` |
| `PUT` | `/api/tournaments/{tournamentId}/sport-matchs/{id}` | Joueur du match ou `ROLE_ADMIN` |
| `DELETE` | `/api/tournaments/{tournamentId}/sport-matchs/{id}` | `ROLE_ADMIN` |

## Modèle de données

### User

| Champ | Description |
| --- | --- |
| `username` | Identifiant unique de connexion |
| `emailAddress` | Adresse email unique |
| `password` | Mot de passe hashé |
| `firstName`, `lastName` | Identité du joueur |
| `status` | Statut utilisateur, par défaut `actif` |
| `roles` | Rôles Symfony, au minimum `ROLE_USER` |

### Tournament

| Champ | Description |
| --- | --- |
| `tournamentName` | Nom du tournoi |
| `startDate`, `endDate` | Dates de début et de fin |
| `location` | Lieu optionnel |
| `description` | Description du tournoi |
| `maxParticipants` | Nombre maximum de participants |
| `sport` | Sport concerné |
| `organizer` | Utilisateur organisateur |
| `winner` | Vainqueur optionnel |
| `games` | Matchs du tournoi |
| `status` | Champ calculé, non stocké en base |

### Registration

| Champ | Description |
| --- | --- |
| `player` | Joueur inscrit |
| `tournament` | Tournoi concerné |
| `registrationDate` | Date d'inscription |
| `status` | `en attente` par défaut, ou `confirmée` |

### SportMatch

| Champ | Description |
| --- | --- |
| `tournament` | Tournoi concerné |
| `player1`, `player2` | Joueurs du match |
| `matchDate` | Date du match |
| `scorePlayer1`, `scorePlayer2` | Scores optionnels |
| `status` | `en attente` par défaut, ou `terminé` |

## Commande de statistiques

Afficher les statistiques globales d'un utilisateur :

```bash
php bin/console app:user:stats <userId>
```

Afficher les statistiques d'un utilisateur pour un tournoi :

```bash
php bin/console app:user:stats <userId> <tournamentId>
```

La commande affiche les victoires, défaites, matchs nuls et le nombre de matchs joués.

## Tests

```bash
php bin/phpunit
```

Tests présents :

- configuration des sous-ressources API ;
- calcul du statut d'un tournoi ;
- logique du processor de matchs ;
- commande `app:user:stats`.

## Commandes utiles

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console cache:clear
php bin/console debug:router
php bin/console debug:container
php bin/phpunit
```
