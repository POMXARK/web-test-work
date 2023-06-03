<?php

namespace App\tests\Controller;

use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EncodeUrlControllerTest extends WebTestCase
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

    /** @test  */
    public function different_urls_received_at_the_same_time_different_hash()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', '/encode-url?url=https://ya.ru');
        $this->assertResponseStatusCodeSame($client->getResponse()->getStatusCode());
        $hashFirst = json_decode($client->getResponse()->getContent())->hash;
        $client->request('GET', '/encode-url?url=https://google.com');
        $hashSecond = json_decode($client->getResponse()->getContent())->hash;

        $this->assertNotSame($hashFirst, $hashSecond);
    }

    /** @test  */
    public function identical_urls_identical_hash()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', '/encode-url?url=https://ya.ru');
        $this->assertResponseStatusCodeSame($client->getResponse()->getStatusCode());
        $hashFirst = json_decode($client->getResponse()->getContent())->hash;
        $client->request('GET', '/encode-url?url=https://ya.ru');
        $hashSecond = json_decode($client->getResponse()->getContent())->hash;

        $this->assertSame($hashFirst, $hashSecond);
    }

    /** @test  */
    public function identical_urls_delayed_identical_hash()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', '/encode-url?url=https://ya.ru');
        $this->assertResponseStatusCodeSame($client->getResponse()->getStatusCode());
        $hashFirst = json_decode($client->getResponse()->getContent())->hash;
        sleep(1);
        $client->request('GET', '/encode-url?url=https://ya.ru');
        $hashSecond = json_decode($client->getResponse()->getContent())->hash;

        $this->assertSame($hashFirst, $hashSecond);
    }

}
