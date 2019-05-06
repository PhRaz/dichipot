_Dichipot is a simple solution to share expenses._

# Dichipot

## Presentation

This web application manages the expenses of several people during an event by calculating the share of each participants in the global budget. It may be used for any event like a weekend with friends or a family holidays.

The user interface is responsive and works well on any device.

This application is available online at <http://dichipot.com>.

## Usage

1. create an event
2. invite participants
3. participants can record expenses and share payments on the group

# Technical information

## Cognito 

User authentication and authorisation is managed with AWS Cognito. 
Here are some blogs entries usefull to understand server side Cognito usage :
- <https://tech.mybuilder.com/managing-authentication-in-your-symfony-project-with-aws-cognito/>
- <https://sanderknape.com/2017/02/getting-started-with-aws-cognito/>

## Docker

Dichipot is developed and deployed with the docker configuration as provided here 
<https://github.com/romaricp/kit-starter-symfony-4-docker>

## installation

Here is the procedure to install the application on a server.
- log in server
- install git and docker
  ```
  sudo yum install git -y
  sudo yum install docker -y
  sudo service docker start
  sudo usermod -a -G docker ec2-user
  sudo curl -L https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  ```
  Logout and re-login for the ec2-user new group to apply.
  
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
  yarn install
  
  yarn install problem :
  
  apt remove cmdtest
  apt remove yarn
  curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
  echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
  apt-get update && apt-get install yarn
  yarn install
  
  yarn encore production
  
  swap problem on installation :
  https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors
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

## deployement of a new release

Log in server and then :

```
docker exec -ti sf4_php bash
cd sf4
git pull
composer install --no-dev --optimize-autoloader
yarn encore production
php bin/console doctrine:schema:update --force
php bin/console cache:clear
```

## reset the db

This procedure recreate a empty DB schema.

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

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
