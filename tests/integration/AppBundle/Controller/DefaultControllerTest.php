<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     */
    public function testIndex()
    {
        $crawler = $this->getCrawler('/');

        $this->assertContains(
            'Learn English by reading books.',
            $crawler->filter('title')->text()
        );
        $this->assertContains('[Alpha]', $crawler->filter('#alpha')->text());
    }

    /**
     * @param string $uri
     * @return Crawler
     */
    private function getCrawler($uri)
    {
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        return $crawler;
    }
}
