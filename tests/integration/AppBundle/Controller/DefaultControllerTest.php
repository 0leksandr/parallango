<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains(
            'Learn English by reading books.',
            $crawler->filter('title')->text()
        );
        $this->assertContains('[Alpha]', $crawler->filter('#alpha')->text());
    }
}
