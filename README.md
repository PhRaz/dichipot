# Account management

This application allows to manage the expenses of several people during an event by calculating the debts and the credits of each participants against each other. It may be used for any event like a weekend with friends or a family holidays.

The user interface is responsive and follows the "mobile first" rule, it is based on bootstrap4.

## Cognito 

User authentication and authorisation is managed with AWS Cognito. 
The AWS COgnito documentation is not oriented to server side authentication.
Here are the blogs entries I find usefull to understand server side Cognito usage :
- https://tech.mybuilder.com/managing-authentication-in-your-symfony-project-with-aws-cognito/
- https://sanderknape.com/2017/02/getting-started-with-aws-cognito/

## Docker

I use the docker configuration as provided here 
https://github.com/romaricp/kit-starter-symfony-4-docker 

## Data model

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
     * user (n-1)
     * date
     * description
     * category
     * expenses (1-n)
     * payments (1-n)
     * event (n-1)
   * expense
     * id
     * user (n-1)
     * amount
     * operation (n-1)
   * payment
     * id
     * user (n-1)
     * amount
     * operation (n-1)
