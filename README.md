# Arcadia Zoo Application

Bienvenue dans le projet Arcadia, une application web conçue pour gérer et présenter les informations du zoo Arcadia, mettant en avant son engagement écologique et ses services .

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants sur votre machine :

- Symfony LCI
- **Git** pour cloner le projet
- **MySQL**  pour la gestion des données
- Un éditeur de texte tel que **Visual Studio Code**
- **Postman** (optionnel) pour tester les API si nécessaire

## Étapes pour déployer l'application en local

### 1. Cloner le dépôt

Clonez ce dépôt sur votre machine locale à l'aide de la commande suivante :

```bash
git clone (https://github.com/LucArronne/Zoo-Arcadia-Raj.git)

````
Accédez au dossier du projet :

```bash
cd Zoo-Arcadia
```
- Configurer la base de données
Assurez-vous que votre serveur MySQL est en cours d'exécution.
Créez une base de données pour l'application :

```bash
CREATE DATABASE

```
Importer le fichier MySQL

```bash
mysql -u votre-utilisateur -p arcadia_zoo < arcadia_zoo.sql
```
-Installer Xampp (derniere version de préference pour l'integration de php 8.1 ou supérieur)

Installer Symphony
PHP : Version 8.1 ou supérieure.
Composer : Gestionnaire de dépendances PHP

- verifier l'installation 
```
symfony -v
```

- lancer Symfony 
```bash
symfony serve
```

- Tester l'application
- L'application sera disponible à l'adresse : http://localhost:3000.
* Accédez à l'application depuis votre navigateur pour vérifier le bon fonctionnement des différentes fonctionnalités.
* Utilisez des outils comme Postman pour tester les routes API si besoin.
