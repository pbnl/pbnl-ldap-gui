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
        //TODO: Only asserting 302 is not enough
    }

    public function testLogin()
    {
        //Correct login
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');


        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();

        $form['_username'] = 'Paul';
        $form['_password'] = 'passwd';

        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $crawler = $client->followRedirect();
        $this->assertContains('Startpage', $client->getResponse()->getContent());

        //Uncorrect login
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form();

        $form['_username'] = 'Paul';
        $form['_password'] = 'hans';

        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertContains('Invalid credentials.', $client->getResponse()->getContent());


    }

    public function testPermissionsError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/startPage');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}