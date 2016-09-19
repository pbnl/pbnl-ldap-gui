<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 22:52
 */

namespace AppBundle\model\login;


use AppBundle\model\ldapCon\LDAPService;
use Symfony\Component\HttpFoundation\Session\Session;

class loggedInUserService
{

    public $ldapFrontend;

    public function __construct(LDAPService $LDAPService)
    {
        $this->ldapFrontend = $LDAPService;
    }

    public function getDN()
    {
        $session = new Session();
        return $session->get("dn");
    }
}