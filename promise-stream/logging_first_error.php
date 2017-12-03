<?php

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Stream\ReadableResourceStream;

require '../vendor/autoload.php';

class Logger
{
    public function onClosed(\React\Promise\PromiseInterface $promise)
    {
        $promise->then(function($error) {
            echo 'Connection was closed' . PHP_EOL;
        });
    }
}

class Provider {
    /**
     * @param string $path
     * @param LoopInterface $loop
     * @return PromiseInterface
     */
    public function get($path, LoopInterface $loop)
    {
        $stream = new ReadableResourceStream(
            fopen($path, 'r'), $loop
        );
        $stream->close();
        return \React\Promise\Stream\first($stream, 'close');
    }
}

$loop = \React\EventLoop\Factory::create();

$logger = new Logger();
$provider = new Provider();

$logger->

$processor->process($provider->get('file.txt', $loop))->then(function($data) {
    echo $data . PHP_EOL;
    echo 'Done' . PHP_EOL;
});

$loop->run();
