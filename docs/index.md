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

## CGU 

The document 'conditions générale d'utilisation' of the site (CGU) have been produce 
by a tool provided online :

https://www.legalplace.fr/contrats/conditions-generales-d-utilisation/

## Cognito 

User authentication and authorisation is managed with AWS Cognito. 
Here are some blogs entries usefull to understand server side Cognito usage :
- <https://tech.mybuilder.com/managing-authentication-in-your-symfony-project-with-aws-cognito/>
- <https://sanderknape.com/2017/02/getting-started-with-aws-cognito/>

## favicon

Favicon files for web and mobile webapp have been produce by a tool provided online :

https://realfavicongenerator.net/

## Docker

Dichipot is developed and deployed with the docker configuration as provided here 
<https://github.com/romaricp/kit-starter-symfony-4-docker>

## SSL

Installation d'un certificat SSL gratuit avec letsencrypt
https://www.developercookies.net/?p=400

## installation

Here is the procedure to install the application on a server.
- log in server
- install git and docker
  ```
  sudo yum update
  sudo yum install git -y
  sudo yum install docker -y
  sudo usermod -a -G docker ec2-user
  sudo service docker start
  sudo curl -L https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  ```
  Logout and re-login for the ec2-user new group to apply.
  
- create swap
  swap problem on VPS installation :
  ```
  https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors
  ```
  Here is a command from SO : 
  ```
  https://serverfault.com/questions/218750/why-dont-ec2-ubuntu-images-have-swap
  
  sudo dd if=/dev/zero of=/var/swapfile bs=1M count=2048 &&
  sudo chmod 600 /var/swapfile &&
  sudo mkswap /var/swapfile &&
  echo /var/swapfile none swap defaults 0 0 | sudo tee -a /etc/fstab &&
  sudo swapon -a
  ```
  
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
  composer install --optimize-autoloader --no-dev
  yarn install
  
  yarn install problem :
  
  apt remove cmdtest
  apt remove yarn
  curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
  echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
  apt-get update && apt-get install yarn
  yarn install
  
  yarn encore production
  ```
- update DB schema
  ```
  php bin/console doctrine:schema:update --force
  
  TODO PB privilege
  mysql> create user sf4@'172.20.0.4' identified by 'sf4';
  Query OK, 0 rows affected (0.01 sec)

  mysql> GRANT ALL PRIVILEGES ON sf4.* TO sf4@'172.20.0.4';
  Query OK, 0 rows affected (0.00 sec)
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

## reset the DB

This procedure recreate a empty DB schema.

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

## backup the DB

Command to backup the DB to S3 bucket :

```
docker exec -it sf4_mysql mysqldump -uroot -proot sf4 2>/dev/null | gzip - | aws s3 cp - s3://dichipot/$(date +%Y%m%d%H%M).sql.gz
```

To set this command as a cron job you must remove the `-it` option.

TODO : fix password on command line

## restore the DB

```
cd /home/ec2-user/dichipot
aws s3 cp s3://dichipot/xxx.sql.gz .
gunzip xxx.sql.gz
sudo mv xxx.sql .docker/data/db/
docker exec -it sf4_mysql bash
mysql -uroot -proot sf4 </var/lib/mysql/xxx.sql
```
## production

Build container for production.

```
docker build -t prod/dichipot_apache  -f ./.docker/prod/apache/Dockerfile .
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
