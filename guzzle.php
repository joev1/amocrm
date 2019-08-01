<?php

// composer:
//        "guzzlehttp/guzzle": "^6.3",
//        "monolog/monolog": "^1.24",

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

require_once './vendor/autoload.php';

// USER_LOGIN & USER_HASH & subdomain
$login = '';
$userHash = '';
$subDomain = '';

$logger = new Logger('example', [new ErrorLogHandler()]);
$stack = HandlerStack::create();
$messageFormatter = new \GuzzleHttp\MessageFormatter('{request} - {response}');
$stack->push(
    Middleware::log(
        $logger,
        $messageFormatter
    )
);

$client = new Client([
    'base_uri' => 'https://'.$subDomain.'.amocrm.ru',
    'cookies' => true,
    'headers' => [
        'Content-Type' => 'application/json'
    ],
    'handler' => $stack
]);

$body = json_encode([
    'USER_LOGIN' => $login,
    'USER_HASH' => $userHash,
]);

$authRequest = new Request('POST', '/private/api/auth.php?type=json', [], $body);
$response = $client->send($authRequest);

$leads = new Request('GET', '/api/v2/leads?filter[tasks]=1&entity=leads');
$results = json_decode($client->send($leads)->getBody(), true);
$item = [];

if(!empty($results))
{
    foreach ($results['_embedded']['items'] as $key => $item) {
        $task['add'] = array(
            array(
                'element_id' => $item['id'],
                'element_type' => 2,
                'text' => 'Сделка без задачи',
            ),
        );
        $leadWithoutTask = new Request('POST', '/api/v2/tasks', [], json_encode($task));
        $response = $client->send($leadWithoutTask);
        echo 'Задача успешно добавлена к сделке №' . $item['name'] . '. ' . PHP_EOL;
    }
} else {
    return [];
}
