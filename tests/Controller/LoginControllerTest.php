<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testShowIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/login/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}