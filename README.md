## TACT - plateforme de Transcription et d'Annotation de Corpus Textuels
---------------------

### Requirements
* a web server (apache, nginx, etc.)
* git
* docker
* docker-compose
* openssl
* make

### Install
```bash
git clone https://gitlab.com/litt-arts-num/tact.git
cd tact
make init
```

By default, Symfony app should be available @ http://localhost:8082/ or https://localhost:8443/
and adminer should be available @ http://localhost:8088/


### Https
By default, `make init` cmd (see `Makefile`) generates a self-signed certificate (.key & crt). You can replace it, by a production one, here : `docker-files/certs/`

### Usefull commands
> all this commands should be run inside docker container (`docker-compose exec apache bash`)

- create a new user (use `-a` if you want to create an admin user)
`bin/console app:create-user [-a]`

- create a new project status (public / private are created via data fixtures)
`bin/console app:create-project-status`

- create a new user status (manager / transcriber / validator are created via data fixtures)
`bin/console app:create-user-status`

- update exposed routes
`make routes`

- load fixtures
`make fixtures`

- other usefull commands can be found in `application/Makefile`


### Host config.
- we use apache and reverseproxy to target multiple applications in various docker containers. See this apache conf. example.

```
<IfModule mod_ssl.c>
 <VirtualHost *:443>
     ServerName tact.demarre-shs.fr
     SSLEngine on
     SSLProxyEngine on
     ProxyPreserveHost On
         ProxyPass / https://localhost:8443/
         ProxyPassReverse / https://localhost:8443/
     SSLCertificateFile /etc/ssl/certs/star_demarre-shs_fr.crt
     SSLCertificateKeyFile /etc/ssl/private/demarre-shs.fr.key
     SSLCertificateChainFile /etc/ssl/certs/DigiCertCA.crt
 </VirtualHost>
</IfModule>

```

### Code contributors
[Gitlab graph](https://gitlab.com/litt-arts-num/tact/-/graphs/master)

### Thanks
Myriam EL HELOU & Sami BOUHOUCHE for their [useful work](https://github.com/elheloum/TEI2JSON) not implemented in this app yet. A maintained version is available [here](https://gitlab.com/litt-arts-num/tei2json).

#Changer les variables#

Petit mémo sur l'ordre des fichiers afin de personnaliser TACT.

##Changer le nom du site##

La variable du nom du site se trouve dans tact/application/src/DataFixtures/AppFixtures.php

Une fois la modification faite, ouvrez un terminal à la racine (/tact/), lancez la commande : 

docker-compose exec apache bash

Chargez les fixtures avec la commande :

make fixtures

Puis quittez la console (commande "exit"). Rechargez votre page et le nouveau nom doit apparaître.

## Changer les noms affichés ##

La correspondance entre les variables et le texte affiché se trouve dans tact/application/translations/messages.fr.yaml pour le français.

#Changer l'aspect graphique du site#

##CSS##

Il faut aller dans /tact/application/assets/css/global.scss et modifier les valeurs correspondantes. Il peut être nécessaire de relancer l'installation pour que les modifications prennent effet (make init dans /tact/).

N. B. : Il peut être nécessaire également de répercuter les modifications dans tact/application/public/build/css/app[...].css .

##La page d'accueil##

Le template de la page d'accueil se trouve dans /tact/application/templates/home/home.html.twig

###Ajouter les manuels du contributeur et du gestionnaire###

Il faut créer un fichier "platform" dans /tact/application/public et y insérer les fichiers manuel\_contributeur.pdf et manuel\_gestionnaire.pdf .


##Le footer##

Le footer se trouve dans /tact/application/templates/base-include/footer.html.twig

##Le menu##

La navbar a son template dans /tact/application/templates/base-include/navbar.html.twig

##Changer la police##

Le changement des fonts se fait dans le fichier /tact/application/public/build/css/app.0406dcc00f5977e0d3a3ba9abe6b2604.css .

##A propos##

Pour changer la page à propos, il au faut aller dans /tact/application/templates/home/about.thml.twig et modifier directement le contenu.

#Changer les images#

##Les logos du footer##

Ils se trouvent dans /tact/application/public/img/logos . Il est également possible de trouver les placeholders des projets et des avatars des contributeurs dans /tact/application/public/img/  .

#Actualités du projet#

Tout semble géré à partir d'un flux rss dans le fichier /tact/application/src/Controller/HomeController.php avec la fonction "actu". Le fichier rss est récupéré depuis un blog hypothèse : https://elan/hypotheses.org/category/tact/feed .

#Utilitaire#

Pour chercher dans tact l'occurence d'un terme :  grep -r '/platform/' 




