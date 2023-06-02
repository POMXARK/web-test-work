<?php


use App\Entity\Url;
use App\Repository\UrlRepository;

class UnitUrlTest extends \App\Tests\DatabaseDependantTestCase
{
    /** @test */
    public function a_url_record_can_be_created_in_database()
    {
        $_url = 'http://url-shortener.loc';
        $url = new Url();
        $url->setUrl($_url);
        $this->entityManager->persist($url);
        $this->entityManager->flush();
        /** @var  UrlRepository $urlRepository */
        $urlRepository = $this->entityManager->getRepository(Url::class);
        $urlRecord = $urlRepository->findOneBy(['url' => $_url]);

        $this->assertEquals($_url, $urlRecord->getUrl());
    }
}