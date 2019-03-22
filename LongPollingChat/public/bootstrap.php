<?php

define('APPLICATION_PATH', dirname(__FILE__) . '/..');

// configure include path
set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    APPLICATION_PATH
)));

// configure autoloader
require 'vendor/autoload.php';

// init app
$app = new \Slim\Slim(array(
    'debug'             => true,
    'view'  => new Sokil\Slim\View,
    'templates.path'    => APPLICATION_PATH . '/views',
));

// middlewares
$app->add(new \Slim\Middleware\SessionCookie(array(
    'secret'    => 'gendolynojujshlasteginojy'
)));

// identifying
$app->map('/', function() use($app)
{
    // already idintified
    if(!empty($_SESSION['nick']))
        $app->redirect ('/chat');
    
    // identity specified
    $nick = $app->request()->post('nick');
    if($nick)
    {
        $_SESSION['nick'] = $nick;
        $app->redirect('/chat');
    }
    
    $app->render('index.php');
})->via(\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_POST);

// chat window
$app->get('/chat', function() use($app)
{
    if(empty($_SESSION['nick']))
        $app->redirect('/');
    
    $app->render('chat.php');
});

// send message
$app->post('/send', function() use($app)
{
    $message = array(
        'text'  => $app->request()->post('message'),
        'nick'  => $_SESSION['nick'],
        'time'  => date('d.m.Y H:i'),
    );
    
    $c = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/publish?id=1');
    curl_setopt_array($c, array(
        CURLOPT_POST            => 1,
        CURLOPT_POSTFIELDS      => json_encode($message),
        CURLOPT_RETURNTRANSFER  => 1,
    ));
    
    $response = curl_exec($c);
    echo $response;
});

$app->get('/logout', function() use($app) {
    $_SESSION['nick'] = null;
    
    $app->redirect('/');
});

// start app
$app->run();