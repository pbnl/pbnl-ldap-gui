<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.11.16
 * Time: 18:55
 */

namespace Tests\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class LoginControllerTest extends WebTestCase
{
    public function testLogout()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');

        $crawler = $client->request('GET', '/logout');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        var_dump($client->getResponse()->getContent());
    }

    public function testLogin()
    {
        //Correct login
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');

        var_dump($client->getResponse()->getContent());

        $crawler = $client->request('GET', '/login');
        var_dump($client->getResponse()->getContent());
        $form = $crawler->selectButton('Login')->form();

        $form['form[name]'] = 'Paul';
        $form['form[password]'] = 'passwd';

        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        //Uncorrect login
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form();

        $form['form[name]'] = 'Paul';
        $form['form[password]'] = 'hans';

        $crawler = $client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Name oder Passwort falsch!")')->count()
        );

    }

    public function testPermissionsError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/startPage');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("permissionError")')->count()
        );
    }
}