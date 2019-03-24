<?php

define('APPLICATION_PATH', __DIR__ . '/..');

// configure include path
set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    APPLICATION_PATH
)));

// configure autoloader
require __DIR__ . '/../vendor/autoload.php';

// session
session_start();

// init app
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

// Get container
$container = $app->getContainer();

// Register view component on container
$container['view'] = function () {
    $view = new \Slim\Views\Twig(APPLICATION_PATH . '/templates');
    return $view;
};

// entry point
$app->get(
    '/',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        // already identified
        if(!empty($_SESSION['nick'])) {
            return $response->withRedirect('/chat');
        } else {
            return $response->withRedirect('/login');
        }
    }
);

// show login form
$app->get(
    '/login',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        return $this->view->render($response, 'login.html.twig');
    }
);

// identify user
$app->post(
    '/login',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        $nick = $request->getParam('nick');
        if (empty($nick)) {
            return $response->withRedirect('/login');
        }

        $_SESSION['nick'] = $nick;
        return $response->withRedirect('/chat');
    }
);

// show chat window
$app->get(
    '/chat',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        if(empty($_SESSION['nick'])) {
            return $response->withRedirect('/login');
        }

        return $this->view->render($response, 'chat.html.twig');
    }
);

// send message
$app->post(
    '/send',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response) {
        $channel = $request->getParam('channel');

        $message = array(
            'text'  => $request->getParam('message'),
            'nick'  => $_SESSION['nick'],
            'time'  => date('d.m.Y H:i'),
        );

        $publishUrl = sprintf(
            'http://%s/publish?channel=%s',
            $_SERVER['HTTP_HOST'],
            $channel
        );

        $curl = \curl_init($publishUrl);

        \curl_setopt_array(
            $curl,
            [
                CURLOPT_POST            => 1,
                CURLOPT_POSTFIELDS      => \json_encode($message),
                CURLOPT_RETURNTRANSFER  => 1,
            ]
        );

        $curlResult = \curl_exec($curl);

        return $response->withJson([
            'status' => $curlResult !== false,
            'curl' => [
                'errors' => curl_error($curl),
                'info' => curl_getinfo($curl),
            ],
        ]);
    }
);

$app->get(
    '/logout',
    function(\Slim\Http\Request $request, \Slim\Http\Response $response) {
        $_SESSION['nick'] = null;
    
        return $response->withRedirect('/login');
});

// start app
$app->run();