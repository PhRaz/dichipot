## Account management

This web application manages the expenses of several people during an event by calculating the debts and the credits of each participants against each other. It may be used for any event like a weekend with friends or a family holidays.

The user interface is responsive and follows the "mobile first" rule, it is based on bootstrap4.

## Cognito 

User authentication and authorisation is managed with AWS Cognito. 
Here are some blogs entries usefull to understand server side Cognito usage :
- https://tech.mybuilder.com/managing-authentication-in-your-symfony-project-with-aws-cognito/
- https://sanderknape.com/2017/02/getting-started-with-aws-cognito/

## Docker

Dichipot is developed and deployed with the docker configuration as provided here 
https://github.com/romaricp/kit-starter-symfony-4-docker 

## installation

Here is the procedure to install the application on a server.

- log in server
- deploy the git repo
    ```
    git clone https://github.com/PhRaz/dichipot.git
    ```
- launch the containers
    ```
    cd dichipot
    docker-compose build
    docker-compose up -d
    ```
- log in Symfony container
    ```
    docker exec -it sf4_php bash
    ```
- install dependancies
    ```
    cd sf4
    composer install
    ```
- update DB schema
    ```
    php bin/console doctrine:schema:update --force
    ```
- done
    ```
    exit
    ```
- create a cognito user pool 
- update .env file with cognito user pool configuration

## Data model

  ```
  * user                             a user may be included in many events
    * id
    * date
    * name
    * mail
    * users_events (1-n)             
  * user_event                       many to many relation with attributes
    * id
    * date
    * administrator                  the administrator creates the event
    * pseudo                         each user has a pseudo in an event
    * event (n-1)
    * user (n-1)
  * event                            an event may include many user
    * id                             an event embeds many operations
    * date
    * name
    * users_events (1-n)
    * operations (1-n)
  * operation                        an operation is created by an user (of the event)
    * id                             an operation has many expenses
    * user (n-1)
    * date
    * description
    * category
    * expenses (1-n)
    * event (n-1)
  * expense                          an expense is done by an user (of the event)
    * id
    * user (n-1)
    * expense
    * payment
    * operation (n-1)
  ```
