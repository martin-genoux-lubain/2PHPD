# Plateforme de Gestion de Tournois Sportifs

Projet final Symfony/API Platform permettant de gérer des tournois sportifs, des joueurs, des inscriptions et des parties/matches.

L'application expose une API REST et fournit une interface d'administration sécurisée réservée aux administrateurs.

---

## Sommaire

- [Technologies](#technologies)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Installation](#installation)
- [Configuration](#configuration)
- [Base de données et migrations](#base-de-données-et-migrations)
- [Fixtures](#fixtures)
- [Lancement du projet](#lancement-du-projet)
- [Authentification et sécurité](#authentification-et-sécurité)
- [Interface d'administration](#interface-dadministration)
- [Modèle de données](#modèle-de-données)
- [API REST](#api-rest)
- [Règles métier](#règles-métier)
- [Notifications](#notifications)
- [Commande Symfony de statistiques utilisateur](#commande-symfony-de-statistiques-utilisateur)
- [Tests](#tests)
- [Commandes Symfony utiles](#commandes-symfony-utiles)
- [Critères d'évaluation couverts](#critères-dévaluation-couverts)

---

## Technologies

- PHP
- Symfony
- API Platform
- Doctrine ORM
- Symfony Security
- EasyAdmin
- PHPUnit
- Doctrine Fixtures
- Faker

---

## Fonctionnalités principales

La plateforme permet de :

- Créer, consulter, modifier et supprimer des tournois sportifs.
- Créer et gérer des utilisateurs/joueurs.
- Inscrire des joueurs à des tournois.
- Confirmer ou gérer les inscriptions.
- Créer des parties entre joueurs inscrits à un tournoi.
- Saisir les scores des matches.
- Déterminer automatiquement le statut d'un match.
- Définir un vainqueur de tournoi.
- Envoyer des notifications selon les événements métier.
- Administrer les tournois, joueurs, inscriptions et matches via une interface dédiée.
- Afficher les statistiques de victoires/défaites d'un joueur via une commande Symfony.

---

## Installation

### 1. Cloner le projet

```bash
git clone [<url-du-repository>](https://github.com/martin-genoux-lubain/2PHPD)
cd 2PHPD
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances nécessaires si elles ne sont pas déjà présentes

```bash
composer require api
composer require doctrine
composer require symfony/security-bundle
composer require easycorp/easyadmin-bundle
composer require --dev symfony/maker-bundle
composer require --dev doctrine/doctrine-fixtures-bundle fakerphp/faker
```

## Configuration

Créer ou modifier le fichier .env.local :

```bash
APP_ENV=dev
APP_SECRET=change_me

DATABASE_URL="mysql://user:password@127.0.0.1:3306/tournament_platform?serverVersion=8.0&charset=utf8mb4"
```

Adapter les informations de connexion selon votre environnement local.

Exemple avec PostgreSQL :

```bash
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/tournament_platform?serverVersion=16&charset=utf8"
```

## Base de données et migrations

Créer la base de données :

```bash
php bin/console doctrine:database:create
```

Générer une migration :

```bash
php bin/console make:migration
```

Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

## Fixtures

Installer les fixtures si nécessaire :

```bash
composer require --dev doctrine/doctrine-fixtures-bundle fakerphp/faker
```

Créer une classe de fixtures :

```bash
php bin/console make:fixtures
```

Charger les données de test :

```bash
php bin/console doctrine:fixtures:load
```
Attention : cette commande vide généralement les tables avant d'insérer les données de test.

## Lancement du projet

Lancer le serveur Symfony :

```bash
symfony server:start
```

L'API est ensuite accessible à l'adresse :

```bash
http://127.0.0.1:8000/api
```

La documentation API Platform est accessible à l'adresse :

## Authentification et sécurité

Le projet utilise le composant Security de Symfony.

Commandes conseillées pour créer l'utilisateur et l'authentification :

```bash
php bin/console make:user
php bin/console make:auth
```

Les mots de passe des utilisateurs sont hashés avec le service Symfony dédié.

Les rôles utilisés sont :

ROLE_USER : utilisateur standard / joueur
ROLE_ADMIN : administrateur

## Interface d'administration

L'administration est réalisée avec EasyAdmin.

Installation :

```bash
composer require easycorp/easyadmin-bundle
```

Création du dashboard :

```bash
php bin/console make:admin:dashboard
```

Création des CRUD d'administration :

```bash
php bin/console make:admin:crud User
php bin/console make:admin:crud Tournament
php bin/console make:admin:crud Registration
php bin/console make:admin:crud SportMatch
```

L'interface d'administration doit être accessible uniquement aux utilisateurs ayant le rôle ROLE_ADMIN

URL typique :

```bash
https://127.0.0.1/admin
```

L'administration permet de gérer de manière centralisée :

Les utilisateurs
Les tournois
Les inscriptions
Les parties / matches

## Modèle de données

### User

Entité utilisateur créée via make:user.

Champs :

| Champ | Type | Contraintes |
| --- | --- | --- |
| lastName | string(100) | non null |
| firstName | string(100) | non null |
| username | string(100) | unique, non null |
| emailAddress | string(180) | unique, non null |
| password | string | hashé |
| status | string(20) | actif, suspendu, banni |
| roles | json | ROLE_USER, ROLE_ADMIN |

### Tournament

| Champ | Type | Contraintes |
| --- | --- | --- |
| tournamentName | string(255) | non null |
| startDate | date_immutable | non null |
| endDate | date_immutable | non null |
| location | string(255) | nullable |
| description | text | non null |
| maxParticipants | integer | non null |
| sport | string(100) | non null |
| organizer | ManyToOne vers User | non null |
| winner | ManyToOne vers User | nullable |
| games | OneToMany vers SportMatch | mappedBy tournament |

Le champ status du tournoi ne doit pas être stocké en base de données.

Il doit être calculé dynamiquement selon les dates :

avant la date de début : à venir
entre la date de début et la date de fin : en cours
après la date de fin : terminé

### Registration

| Champ | Type | Contraintes |
| --- | --- | --- |
| player | ManyToOne vers User | non null |
| tournament | ManyToOne vers Tournament | non null |
| registrationDate | datetime_immutable | non null |
| status | string(20) | confirmée, en attente |

Une contrainte d'unicité doit être ajoutée sur le couple :

```bash
(player, tournament)
```

Cela empêche un même joueur de s'inscrire plusieurs fois au même tournoi.

### SportMatch

| Champ | Type | Contraintes |
| --- | --- | --- |
| tournament | ManyToOne vers Tournament | non null |
| player1 | ManyToOne vers User | non null |
| player2 | ManyToOne vers User | non null |
| matchDate | datetime_immutable | non null |
| scorePlayer1 | integer | nullable |
| scorePlayer2 | integer | nullable |
| status | string(20) | en attente, en cours, terminé |
