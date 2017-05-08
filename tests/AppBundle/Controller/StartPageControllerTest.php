<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.11.16
 * Time: 23:58
 */

namespace Tests\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Tests\AppBundle\LoginAction;


class StartPageControllerTest extends WebTestCase
{
    public function testStartPage()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());
        $crawler = $client->request('GET', '/startPage');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}