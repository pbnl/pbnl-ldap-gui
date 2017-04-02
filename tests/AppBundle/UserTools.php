<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 10.12.16
 * Time: 17:47
 */

namespace Tests\AppBundle;


class  UserTools
{
    static function doesUserExist($name, $client,$tester)
    {
        $crawler = $client->request('GET', '/user/schowAllUsers');

        $form = $crawler->selectButton('Suchen')->form();
        $form['form[userFilter]'] = $name;
        $crawler = $client->submit($form);

        $tester->assertEquals(200, $client->getResponse()->getStatusCode());
        $tester->assertGreaterThan(
            0,
            $crawler->filter('a:contains("'.$name.'")')->count()
        );
    }
}