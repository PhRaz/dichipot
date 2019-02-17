# Gestion de compte

Application de gestion des dépenses de plusieures personnes autour d'un évènement.
Cette application permet de gérer, par exemple, les dépenses lors d'un weekend entre amis ou des vacances en famille.

## modèle de donnée
   * user
     * id
     * date
     * name
     * mail
     * users_events (1-n)
   * user_event
     * id
     * date
     * administrator
     * event (n-1)
     * user (n-1)
   * event
     * id
     * date
     * name
     * users_events (1-n)
     * operations (1-n)
   * operation
     * id
     * user
     * date
     * description
     * category
     * expenses (1-n)
     * payments (1-n)
     * event (n-1)
   * expense
     * id
     * user (1-1)
     * amount
   * payment
     * id
     * user (1-1)
     * amount
