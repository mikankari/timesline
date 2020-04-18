<?php

require_once 'config.php';
require_once 'env.php';

session_start();

if (! isset($_SESSION['state']) || $_SESSION['state'] == 0) {
    $_SESSION['state'] = 1;
    header('Location: https://slack.com/oauth/authorize?' . http_build_query([
        'client_id'     => $config['client_id'],
        'state' => 'unyara',
        'team' => $config['team'],
        'scope' => 'users:read channels:history chat:write:user',
        'redirect_uri' => $env['baseuri'] . '/auth.php',
    ]));
} else if ($_SESSION['state'] == 1) {
    $result = json_decode(file_get_contents('https://slack.com/api/oauth.access?' . http_build_query([
        'client_id'     => $config['client_id'],
        'client_secret'     => $config['client_secret'],
        'code' => $_REQUEST['code'],
        'redirect_uri' => $env['baseuri'] . '/auth.php',
    ])));
    if ($result->ok) {
        $_SESSION['state'] = 2;
        $_SESSION['user_id'] = $result->user_id;
        $_SESSION['access_token'] = $result->access_token;
        header('Location: .');
    } else {
        $_SESSION['state'] = 0;
        print 'signing in failed';
    }
} else {
    $_SESSION['state'] = 0;
    print 'signed out';
}

//var_dump($result);
