
# TODO

* devops
  * update container for encore/yarn installation
  * re test the installation from scratch
  * update doc for ec2 installation
  * backup DB
  * utilisation de Amazon RDS
  * nom de domaine 
  * https
* user story
  * modification/suppression opération
  * modification/suppression évènement
  * modification/suppression participant
  * intégration Cognito
    * intégration participants sur un évènement
    * signup / login
    * page de gestion de configuration du compte
  * revoir le design en mobile first
    * revoir la navigation, utilisation d'icones
    * utiliser une font responsive
    * prévoir la version tablette et desktop
* code
  * check non optimal queryBuilder returned data as array[]
  * mettre le code dans un service
  * merger les entitées expense et payment
  * contrôle autorisation des URLs
* PWA
  * mode offline
    * modification opération sans requête àpartir des données de la liste, 
      une requête pour mise à jour uniquement
    * idem pour modification des évènements
