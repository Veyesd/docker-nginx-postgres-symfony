# docker

## installation & configuration

Clonez simplement le repo dans un répertoire.

puis dans le terminal dnass le répertoire de symfony lancez:
```bash
$ composer install
```
Pour docker, il faut s'assurer que le réseau nommé dans le docker-compose.yml soit bien créé

Pour s'assurer que le networks cité soit bien présent,
avec le terminal, aller dans le répertoire "docker",
et pour lister vos réseaux, lancez:
```bash
$ docker network ls
```
Si il n'y est pas il faut le créer.
```bash
# docker network create --driver=bridge --subnet=192.168.2.0/24 nom-networks
# ici le réseau est "symfony-networks"
$ docker network create --driver=bridge --subnet=192.168.2.0/24 symfony-networks
```
Vous pourriez avoir d'autres réseaux qui gênent la création de celui qu'on souhaite avoir. Vous auriez l'erreur.

Vous pouvez réinitialiser vos réseaux.
```bash
$ docker network prune
```
vous pourrez créer le réseau bridge virtuel nécessaire à la communication des container entre eux.

ensuite venez dans le répertoire docker et lancez:
```bash
$ docker-compose up --build
```

Vous pourrez constater le fonctionnement en allant simplement sur localhost, vous verrez la page d'accueil générique de symfony.
