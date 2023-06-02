<?php

namespace App\tests\Controller;

use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlControllerTest extends WebTestCase
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        DatabasePrimer::prime($kernel);
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testUrl(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/encode-url?url=https://ya.ru');
        $this->assertResponseStatusCodeSame($client->getResponse()->getStatusCode());
        $hash = json_decode($client->getResponse()->getContent())->hash;
        $this->assertIsNumeric($hash);
    }
}
