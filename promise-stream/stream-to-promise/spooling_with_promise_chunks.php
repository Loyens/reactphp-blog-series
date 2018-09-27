<?php

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Stream\ReadableResourceStream;

require __DIR__ . '/../../vendor/autoload.php';

class Processor {
    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    public function process(PromiseInterface $promise)
    {
        return $promise->then(function(array $chunks) {
            echo 'Total chunks: ' . count($chunks) . PHP_EOL;
            foreach ($chunks as $index => $chunk) {
                echo 'Chunk ' . ($index + 1) . ': ' . $chunk . PHP_EOL;
            }
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
        $stream = new ReadableResourceStream(fopen($path, 'r'), $loop);
        return \React\Promise\Stream\all($stream);
    }
}

$loop = \React\EventLoop\Factory::create();

$processor = new Processor();
$provider = new Provider();

$processor
    ->process($provider->get('file.txt', $loop))
    ->then(function() {
        echo 'Done' . PHP_EOL;
    });

$loop->run();
