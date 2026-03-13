# TechSupport360 - API de Ticketing

API REST de gestion de tickets de support technique, construite avec Symfony 7.3 et API Platform 4.2.

## Stack technique

- **PHP 8.4** (FrankenPHP)
- **Symfony 7.3**
- **API Platform 4.2**
- **MySQL 8.0**
- **Docker Compose**

## Installation

```bash
# Lancer les containers
make start

# Installer Symfony (première fois uniquement)
make setup

# Se connecter au container
make connect

# Créer la base de données
php bin/console doctrine:migrations:migrate

# Créer un utilisateur admin
php bin/console app:create-user admin@example.com motdepasse123 -r ROLE_ADMIN
```

## Services disponibles

| Service | URL |
|---------|-----|
| API | http://localhost/api |
| Documentation Swagger | http://localhost/api/docs |
| Adminer | http://localhost:8001 |
| Mailcatcher | http://localhost:1080 |

## Authentification

L'API utilise un système de token custom (pas JWT).

### 1. Obtenir un token

```
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "motdepasse123"
}
```

Retourne :
```json
{
    "token": "eyJ...",
    "user": "admin@example.com"
}
```

### 2. Utiliser le token

Ajouter le header `Authorization` avec le token recu :

```
GET /api/tickets
Authorization: <token>
```

## Endpoints

### Utilisateurs

| Methode | URL | Description | Acces |
|---------|-----|-------------|-------|
| POST | `/api/register` | Creer un compte | Public |
| GET | `/api/users` | Lister les utilisateurs | Authentifie |
| GET | `/api/users/{uuid}` | Detail d'un utilisateur | Authentifie |
| PATCH | `/api/users/{uuid}` | Modifier un utilisateur | Admin |
| DELETE | `/api/users/{uuid}` | Supprimer un utilisateur | Admin |

### Tickets

| Methode | URL | Description | Acces |
|---------|-----|-------------|-------|
| GET | `/api/tickets` | Lister les tickets | Authentifie (filtre par role) |
| GET | `/api/tickets/{uuid}` | Detail d'un ticket | Authentifie (filtre par role) |
| POST | `/api/tickets` | Creer un ticket | Authentifie |
| PATCH | `/api/tickets/{uuid}` | Modifier un ticket | Proprietaire / Agent assigne / Admin |
| DELETE | `/api/tickets/{uuid}` | Supprimer un ticket | Admin uniquement |

**Filtres disponibles :**
- `?title=bug` — Recherche partielle sur le titre
- `?status=ouvert` — Filtrer par statut (ouvert, en_cours, resolu, ferme)
- `?priority=haute` — Filtrer par priorite (faible, normale, haute)
- `?category={uuid}` — Filtrer par categorie (UUID)
- `?client={uuid}` — Filtrer par client (UUID)
- `?agent={uuid}` — Filtrer par agent (UUID)

**Visibilite selon le role :**
- Client : voit uniquement ses tickets
- Agent : voit uniquement ses tickets assignes
- Admin : voit tous les tickets

### Categories

| Methode | URL | Description | Acces |
|---------|-----|-------------|-------|
| GET | `/api/categories` | Lister les categories | Authentifie |
| GET | `/api/categories/{uuid}` | Detail d'une categorie | Authentifie |
| POST | `/api/categories` | Creer une categorie | Admin |
| PATCH | `/api/categories/{uuid}` | Modifier une categorie | Admin |
| DELETE | `/api/categories/{uuid}` | Supprimer une categorie | Admin |

**Filtres disponibles :**
- `?name=tech` — Recherche partielle sur le nom
- `?onlyWithTodo=true` — Uniquement les categories avec des tickets ouverts ou en cours

### Commentaires

| Methode | URL | Description | Acces |
|---------|-----|-------------|-------|
| GET | `/api/comments` | Lister les commentaires | Authentifie |
| GET | `/api/comments/{uuid}` | Detail d'un commentaire | Authentifie |
| POST | `/api/comments` | Creer un commentaire | Authentifie |
| PATCH | `/api/comments/{uuid}` | Modifier un commentaire | Auteur / Admin |
| DELETE | `/api/comments/{uuid}` | Supprimer un commentaire | Admin |

### Divers

| Methode | URL | Description | Acces |
|---------|-----|-------------|-------|
| GET | `/api/version` | Version de l'API | Authentifie |
| POST | `/api/login` | Connexion | Public |
| POST | `/api/register` | Inscription | Public |

## Roles

| Role | Description |
|------|-------------|
| `ROLE_USER` | Client — cree des tickets, commente, voit ses propres tickets |
| `ROLE_AGENT` | Agent — voit et traite ses tickets assignes |
| `ROLE_ADMIN` | Admin — acces complet, gere utilisateurs et categories |

## Regles metier

- Un client ne peut pas avoir plus de **10 tickets ouverts** simultanement
- Seul un **admin** peut supprimer un ticket
- Les clients ne peuvent modifier que **leurs** tickets
- Les agents ne peuvent modifier que les tickets qui leur sont **assignes**

## Tests

```bash
# Dans le container
php bin/phpunit
```

## Commandes utiles

```bash
# Creer un utilisateur
php bin/console app:create-user email@example.com password [-r ROLE_ADMIN|ROLE_AGENT]

# Vider le cache
php bin/console cache:clear

# Mettre a jour le schema
php bin/console doctrine:migrations:migrate
```
