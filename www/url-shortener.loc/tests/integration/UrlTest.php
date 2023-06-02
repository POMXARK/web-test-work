<?php

namespace App\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IntegrationUrlTest extends DatabaseDependantTestCase
{
    /** @test
     * @throws GuzzleException
     */
    public function encodeUrl() {
        $this->assertTrue(true);
    }
}