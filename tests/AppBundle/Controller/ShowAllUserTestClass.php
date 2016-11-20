<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.11.16
 * Time: 19:44
 */

namespace Tests\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\LoginAction;


class ShowAllUserTestClass extends WebTestCase
{
    public function testShowAllUsers()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/schowAllUsers');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            1,
            $crawler->filter('td:contains("givenName")')->count()
        );
    }
    public function testSearchForName()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/schowAllUsers');

        $form = $crawler->selectButton('Suchen')->form();
        $form['form[userFilter]'] = 'Paul';
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('td:contains("givenName=Paul,ou=Ambronen,ou=People,dc=pbnl,dc=de")')->count()
        );
    }

    public function testSearchForGroup()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/schowAllUsers');

        $form = $crawler->selectButton('Suchen')->form();
        $form['form[groupFilter]'] = 'tronjer';
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('td:contains("givenName=TestTronjerMember,ou=Hagen von Tronje,ou=People,dc=pbnl,dc=de")')->count()
        );
    }
}