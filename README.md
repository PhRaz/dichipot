# Gestion de compte

Application de gestion des dépenses de plusieures personnes autour d'un évènement.
Cette application permet de gérer par exemple les dépenses lors d'un weekend entre amis ou des vacances en famillle.

## modèle de donnée
   * user
     * id
     * date
     * name
     * mail
     * users_events (1-n)
   * users_events
     * id
     * date
     * administrator
     * event (1-1)
     * user (1-1)
   * event
     * id
     * date
     * name
     * users_event (1-n)
     * operations (1-n)
   * operation
     * id
     * event (n-1)
     * user
     * date
     * description
     * category
     * expenses (1-n)
     * payments (1-n)
   * expense
     * id
     * user (1-1)
     * amount
   * payment
     * id
     * user (1-1)
     * amount
