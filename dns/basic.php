<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$dns = $factory->create('8.8.8.8', $loop);

$resolve = $dns->resolve('php.net')
    ->then(function ($ip) {
        echo "php.net: $ip\n";
    })
    ->otherwise(function (\React\Dns\RecordNotFoundException $e) {
        echo 'Cannot resolve: ' . $e->getMessage();
    });

$loop->run();
