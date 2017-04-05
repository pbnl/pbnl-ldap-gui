<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.03.17
 * Time: 23:32
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\LoginAction;
use Tests\AppBundle\UserTools;


class DelUserTest extends WebTestCase
{
    public function testDelUser()
    {
        $client = LoginAction::getLoggedInClient(static::createClient());

        $crawler = $client->request('GET', '/user/addAUser');

        $form = $crawler->selectButton('Erstellen')->form();
        $form['form[firstName]'] = 'ToDel';
        $form['form[secondName]'] = 'ToDel';
        $form['form[givenName]'] = 'ToDel';
        $form['form[clearPassword]'] = 'passwd';
        $form['form[ouGroup]'] = 'testStamm';
        $form['form[stamm]'] = 'testStamm';
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('ToDel', $client->getResponse()->getContent());
        $this->assertGreaterThan(
            0,
            $crawler->filter('td:contains("ToDel")')->count()
        );


        UserTools::doesUserExist("ToDel",$client,$this,true);

        UserTools::delUser("ToDel",$client);

        UserTools::doesUserExist("ToDel",$client,$this,False);
    }
}