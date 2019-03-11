
# TODO

## devops

  * update container for encore/yarn installation
  * re test the installation from scratch
  * update doc for ec2 installation
  * backup DB
  * use Amazon RDS
  * domain name 
  * https

## user story

  * validation des données, format et longueur des champs
  * ~~modification~~/suppression opération
  * formulaire évènement sur une page pour gestion de l'évènement et des participants 
    * modification/suppression évènement
    * modification/suppression participant
  * version imprimable d'un compte
  * envoie de mail pour les participants
  * intégration Cognito
    * intégration participants sur un évènement
      * envoie d'un mail pour autorisation avec un token
      * gestion des droits (le créateur de l'event est administrateur)
    * signup / login
    * page de gestion de configuration du compte
  * revoir le design en mobile first
    * revoir la navigation, utilisation d'icones
    * utiliser une font responsive
    * prévoir la version tablette et desktop
      * ~~mettre total et balance dans une 2ème colonne~~

## code

  * ~~check non optimal queryBuilder returned data as array[]~~
  * mettre le code dans un service
  * merger les entitées expense et payment
  * contrôle autorisation des URLs

## PWA
  
  * mode offline
    * modification opération sans requête àpartir des données de la liste, 
      une requête pour mise à jour uniquement
    * idem pour modification des évènements
