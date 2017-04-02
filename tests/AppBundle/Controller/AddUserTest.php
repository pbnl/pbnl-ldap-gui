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
use Tests\AppBundle\UserTools;


class AddUserTest extends WebTestCase
{
    public function testAddUser()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/addAUser');

        $form = $crawler->selectButton('Erstellen')->form();
        $form['form[firstName]'] = 'TestUser22';
        $form['form[secondName]'] = 'TestUser22S';
        $form['form[givenName]'] = 'TestUser22';
        $form['form[clearPassword]'] = 'passwd';
        $form['form[ouGroup]'] = 'testStamm';
        $form['form[stamm]'] = 'testStamm';
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('td:contains("TestUser22")')->count()
        );


        UserTools::doesUserExist("TestUser22",$client,$this);
    }

public function testAddUserAlreadyExists()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/addAUser');

        $form = $crawler->selectButton('Erstellen')->form();
        $form['form[firstName]'] = 'Paul';
        $form['form[secondName]'] = 'Paul';
        $form['form[givenName]'] = 'Paul';
        $form['form[clearPassword]'] = 'passwd';
        $form['form[ouGroup]'] = 'testStamm';
        $form['form[stamm]'] = 'testStamm';
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('div:contains("The user already exists!")')->count()
        );


        UserTools::doesUserExist("Paul",$client,$this);
    }
}