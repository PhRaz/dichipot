<?php

namespace App\Bridge;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Result;

class AwsCognitoClient
{
    private $client;

    private $poolId;

    private $clientId;

    public function __construct(
        string $poolId,
        string $clientId,
        string $region = 'eu-central-1',
        string $version = 'latest'
    )
    {
        $this->client = CognitoIdentityProviderClient::factory([
            'region' => $region,
            'version' => $version
        ]);
        $this->poolId = $poolId;
        $this->clientId = $clientId;
    }

    public function signUp($username, $password): Result
    {
        return $this->client->signUp([
            'ClientId' => $this->clientId,
            'Username' => $username,
            'Password' => $password,
            'UserAttributes' => [
                [
                    'Name' => 'name',
                    'Value' => $username
                ],
                [
                    'Name' => 'email',
                    'Value' => $username
                ]
            ]
        ]);
    }

    public function confirmSignUp($username, $code): Result
    {
        return $this->client->confirmSignUp([
            'ClientId' => $this->clientId,
            'Username' => $username,
            'ConfirmationCode' => $code,
        ]);
    }

    public function findByUsername(string $username): Result
    {
        return $this->client->listUsers([
            'UserPoolId' => $this->poolId,
            'Filter' => "email=\"" . $username . "\""
        ]);
    }

    public function checkCredentials($username, $password): Result
    {
        return $this->client->adminInitiateAuth([
            'UserPoolId' => $this->poolId,
            'ClientId' => $this->clientId,
            'AuthFlow' => 'ADMIN_NO_SRP_AUTH', // this matches the 'server-based sign-in' checkbox setting from earlier
            'AuthParameters' => [
                'USERNAME' => $username,
                'PASSWORD' => $password
            ]
        ]);
    }

    public function getRolesForUsername(string $username): Result
    {
        return $this->client->adminListGroupsForUser([
            'UserPoolId' => $this->poolId,
            'Username' => $username
        ]);
    }

    public function forgotPassword($username): Result
    {
        return $this->client->forgotPassword([
            'ClientId' => $this->clientId,
            'Username' => $username
        ]);
    }

    public function confirmForgotPassword($username, $password, $code): Result
    {
        return $this->client->confirmForgotPassword([
            'ClientId' => $this->clientId,
            'ConfirmationCode' => $code,
            'Password' => $password,
            'Username' => $username
        ]);
    }

    public function adminCreateUser($username)
    {
        return $this->client->adminCreateUser([
            'DesiredDeliveryMediums' => ['EMAIL'],
            'UserAttributes' => [
                [
                    'Name' => 'name',
                    'Value' => $username
                ],
                [
                    'Name' => 'email',
                    'Value' => $username
                ]
            ],
            'UserPoolId' => $this->poolId,
            'Username' => $username
        ]);
    }
}
