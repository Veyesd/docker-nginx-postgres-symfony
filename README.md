# docker symfony postgres - secure api starter pack

## pré-requis

- le CLI docker et le client
- un outils de requete POSTMAN ou INSOMNIA
- un outils de visualisation de base de données comme DBeaver

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


Un fois le bousin lancé, n'oublié de faire un petit coup de:
```bash
$ php bin/console doctrine:database:create
```
pour initialiser la bdd puis: 
```bash
$ php bin/console d:s:u --force
```
pour créer les tables nécessaires.

Vous pourrez voir les petites choses pratiques que j'ai pu voir pour se faciliter la vie.
Comment on se dispense des injections de l'ORM et des services pour les rendre dispo via this et avoir des controller plus lisibles?
Il y'a un contrôleur user basique que j'étofferai plus tard,
en attendant pour activer l'utilisateur pour pouvoir accès à l'api, il faut toogle le is_active du user sur true directement en BDD.
Il faut d'abord enregistrer l'utilisateur:
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
Puis l'activer en bdd avant de vous connecter via:
**/login**
```json
{
    "email":"mail@gmail.com",
    "password":"aze",
}
```
Vous recevrez un token que vous devrez utiliser une fois connecté,

Vous devrez utiliser le mode Bearer Token de votre outil.
La sécurité de l'api est contrôlé par un handler qui check un token Bearer,
celui ci rafraîchit le token si il est valide, sinon il le détruit et demande la reconnexion.

**/api/user/all**

**/api/user/{id}**

**/api/user/{id}/roles**

J'envisage aussi un scheduler qui passerai toute les minutes vérifier la validité des tokens et garder la table la plus clean possible.
Il se lance dans le terminal avec la command:

```bash
$ php bin/console messenger:consume -v scheduler_default
```
