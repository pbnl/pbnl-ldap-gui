<?php

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.11.16
 * Time: 19:30
 */
namespace Tests\AppBundle;
use Symfony\Bundle\FrameworkBundle\Client;

class LoginAction
{
    static function getLoggedInClient(Client $client)
    {
        //Correct login
        $crawler = $client->request('GET', '/logout');

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'Paul';
        $form['_password'] = 'passwd';

        $crawler = $client->submit($form);
        return $client;
    }
}