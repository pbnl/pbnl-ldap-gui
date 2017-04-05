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
    static function doesUserExist($name, $client,$tester,$shouldHe = true)
    {
        $crawler = $client->request('GET', '/user/schowAllUsers');

        $form = $crawler->selectButton('Suchen')->form();
        $form['form[userFilter]'] = $name;
        $crawler = $client->submit($form);

        $tester->assertEquals(200, $client->getResponse()->getStatusCode());
        if($shouldHe)
        {
            $tester->assertGreaterThan(
                0,
                $crawler->filter('a:contains("'.$name.'")')->count()
            );
        }
        else{
            $tester->assertEquals(
                0,
                $crawler->filter('a:contains("'.$name.'")')->count()
            );
        }

    }

    static function delUser($name,$client)
    {
        $crawler = $client->request('GET', '/user/schowAllUsers');
        $link = $crawler->filter('tr:contains("'.$name.'")')->filter('div:contains("Wollen sie wirklich")')->selectLink('Löschen')->link();
        $crawler = $client->click($link);
    }
}