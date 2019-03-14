# Account management

This application is used to manage expenses of several peoples during an event.
It allows you to manage, for example, spending on a weekend with friends or a family vacation.

The user interface design follows the "mobile first" rule, it is based on bootstrap4.

## Cognito 

User authentication and authorisation management is done with AWS Cognito.
I implemented the solution as explained in this article :
https://tech.mybuilder.com/managing-authentication-in-your-symfony-project-with-aws-cognito/

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
