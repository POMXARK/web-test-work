<?php

namespace App\tests\Controller;

use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DecodeUrlControllerTest extends WebTestCase
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
    public function decode_url_by_hash()
    {
        $urlEncode = 'https://ya.ru';
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', '/encode-url?url=' . $urlEncode);
        $hash = json_decode($client->getResponse()->getContent())->hash;

        $client->request('GET', '/decode-url?hash=' . $hash);
        $this->assertResponseStatusCodeSame($client->getResponse()->getStatusCode());
        $urlDecode = json_decode($client->getResponse()->getContent())->url;

        $this->assertSame($urlEncode,$urlDecode);
    }
}
