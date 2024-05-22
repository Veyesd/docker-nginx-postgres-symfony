# docker symfony postgres starter pack

## installation & configuration

Clonez simplement le repo dans un répertoire.

puis dans le terminal dans le répertoire de symfony lancez:
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
Vous pourriez avoir d'autres réseaux qui gênent la création de celui qu'on souhaite avoir.

Vous pouvez clean vos réseaux.
```bash
$ docker network prune
```
vous pourrez créer le réseau bridge virtuel nécessaire à la communication des container entre eux.

ensuite venez dans le répertoire docker et lancez:
```bash
$ docker-compose up --build
```

Vous pourrez constater le fonctionnement en allant simplement sur localhost, vous verrez la page d'accueil générique de symfony.


Le postgres est configuré sur 127.0.0.1:5432
Vous pouvez utiliser Dbeaver pour check le contenu de votre bdd.

/!\ Si vous constatez des problèmes lors de l'utilisation de Doctrine et du CLI vis à vis de la bdd, ou que vous désirez paramètrer la connexion à la bdd de symfony,
N'oubliez pas de faire un tour aussi par le fichier .env de docker.



## que trouverez vous sur la partie symfony?

Les petites choses pratiques que j'ai pu voir pour se faciliter la vie.

Comment on se dispense des injections de l'ORM et des services pour les rendre dispo via this et avoir des controller plus lisibles?
Une contrôleur user basique. Il faudra activer le user en bdd pour pouvoir vous connecter une fois que vous l'aurez enregistré.
L'api est testable et vous avez quelques endpoint de base, hors connexion:

**/register**
```json
{
    "email":"mail@gmail.com",
    "password":"aze",
    "firstname":"John",
    "lastname":"Doe",
    "admin":true
}
```
**/login**
```json
{
    "email":"mail@gmail.com",
    "password":"aze",
}
```
vous recevrez un token que vous devrez utiliser une fois connecté,
vous devrez utiliser le mode Bearer Token de votre outil.

**/api/user/all**

**/api/user/{id}**

**/api/user/{id}/roles**


D'autres améliorations à venir, je réfléchis à rendre cette API la plus facile à scale en faisant les fonctions les plus sèches possible.