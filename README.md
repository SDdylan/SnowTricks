
# Snow Tricks / Projet 6 OpenClassrooms

Ce projet est un site communautaire présentant des figures de snowboard en PHP/Symfony.

## Fonctionnalités

Le visiteur peut naviguer sur les différentes pages recensant les figures.

Un système d'authentification permet aux utilisateurs inscrits de commenter mais également de modifier, ajouter et supprimer des figures.
Les administrateurs peuvent de plus accèder a des pages de gestion des commentaires et des utilisateurs.

## Technologies

* WampServer 3.2.6
    * Apache 2.4.51
    * PHP 7.4.26
    * MySQL 5.7.36
* Bootstrap 5
* JavaScript
* Composer 2.1.14 
* Symfony 5.3.4

## Installation

### Configuration de l'environnement

Il est nécessaire d'avoir un environnement local avec MySQL, PHP et Apache.  
Pour la configuration d'un VirtualHost je vous laisse le soin de consulter la documentation de votre plateforme de développement web (par exemple: [WAMP](https://www.wampserver.com/) ou [XAMPP](https://doc.ubuntu-fr.org/xampp)).

### Déploiement du projet

Téléchargez manuellement le contenu de ce dépôt GitHub dans un dossier de votre système.
Vous pouvez également utiliser Git avec un terminal à la racine d'un dossier de votre choix :
```
git clone https://github.com/SDdylan/SnowTricks.git
```
Pour la prochaine étapes, vous aurez besoin de [**Composer**](https://getcomposer.org/download/), veillez à l'installer si vous ne disposez pas déjà de ce dernier sur votre système.  
Installez ensuite les librairies de ce projet à l'aide d'un terminal à la racine de l'application avec la commande ci-dessous :
```
composer install
```

### Base de données

Veillez dans un premier temps à changer la valeur de la connexion dans le fichier **.env**, il s'agit de la variable *DATABASE_URL*.

Dans un terminal à la racine du projet executez la commande suivante pour créer la base de donnée :
```
php bin/console doctrine:database:create
```
Créez ensuite la structure de cette base de donnée :
```
php bin/console doctrine:migrations:migrate
```

Et enfin pour remplir la base avec des donnée en utilisant la commande suivante :
```
php bin/console doctrine:fixtures:load
```

Pour vous connecter en tant qu'administrateur vous pouvez récupérer le pseudo du 2ème utilisateur entré dans la base de donnée. Son mot de passe est écrit en clair dans le fichier **AppFixtures.php** à la ligne 54.
### Configuration du mailer

La variable *MAILER_DSN* dans le fichier **.env** doit être modifier en fonction de votre configuration au niveau de l'accès a votre système SMTP, nous vous recommandons l'utilisation de *[MailTrap](https://mailtrap.io/)* pour l'envoi de mail de confirmation d'inscription ou la modification de mot de passe.

## Auteurs

[@SDdylan](https://github.com/SDdylan) sous la supervision de [@julienrusso-oc](https://github.com/julienrusso-oc) et de [@aurelienk](https://github.com/aurelienk).
