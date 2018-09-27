<?php

require __DIR__ . '/../../vendor/autoload.php';

use Clue\React\Buzz\Browser;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use Symfony\Component\DomCrawler\Crawler;

class QueueableParser
{
    /**
     * @var Browser
     */
    private $client;

    /**
     * @var array
     */
    private $parsed = [];

    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(Browser $client, LoopInterface $loop)
    {
        $this->client = $client;
        $this->loop = $loop;
    }

    public function parse(array $urls = [], $timeout = 5, $concurrencyLimit = 10)
    {
        $queue = $this->initQueue($concurrencyLimit);

        foreach ($urls as $url) {
            /** @var Promise $promise */
            $promise = $queue($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) {
                    $this->parsed[] = $this->extractFromHtml((string)$response->getBody());
                }
            );

            $this->loop->addTimer($timeout, function () use ($promise) {
                $promise->cancel();
            });
        }
    }

    public function extractFromHtml($html)
    {
        $crawler = new Crawler($html);

        $title = trim($crawler->filter('h1')->text());
        $genres = $crawler->filter('[itemprop="genre"] a')->extract(['_text']);
        $description = trim($crawler->filter('[itemprop="description"]')->text());

        $crawler->filter('#titleDetails .txt-block')->each(
            function (Crawler $crawler) {
                foreach ($crawler->children() as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        );

        $releaseDate = trim($crawler->filter('#titleDetails .txt-block')->eq(3)->text());

        return [
            'title'        => $title,
            'genres'       => $genres,
            'description'  => $description,
            'release_date' => $releaseDate,
        ];
    }

    public function getMovieData()
    {
        return $this->parsed;
    }

    /**
     * @param int $concurrencyLimit
     * @return \Clue\React\Mq\Queue
     */
    protected function initQueue($concurrencyLimit)
    {
        return new Clue\React\Mq\Queue($concurrencyLimit, null, function ($url) {
            return $this->client->get($url);
        });
    }
}
