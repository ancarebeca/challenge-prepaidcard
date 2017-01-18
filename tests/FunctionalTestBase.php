<?php

namespace Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestBase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        parent::tearDown();

        static::bootKernel();

        $purge = new ORMPurger(static::$kernel->getContainer()->get('doctrine')->getManager());
        $purge->purge();
    }
}