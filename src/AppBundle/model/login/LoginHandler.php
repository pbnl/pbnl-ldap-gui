<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 06.08.16
 * Time: 18:42
 */

namespace AppBundle\model\login;


use AppBundle\model\ldapCon\LDAPConnetor;
use AppBundle\model\SSHA;
use NoLDAPBindDataException;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginHandler
{
    public $name = "";
    public $password = "";
    public $hash = "";
    public $rememberme = FALSE;

    public function login()
    {
        $ldapConnector = new LDAPConnetor();
        $ldapConnector->intiLDAPConnection();
        $ldapCon = $ldapConnector->getCon();

        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $filter="(|(givenname=$this->name))";


        $result = ldap_search($ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapCon));
        $data = ldap_get_entries($ldapCon, $result);

        if ($data["count"] == 0) return FALSE;
        if ($data[0]["givenname"][0] == $this->name && SSHA::ssha_password_verify($data[0]["userpassword"][0],$this->password))
        {
            $this->loginSuccess();
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    private function loginSuccess()
    {
        $session = new Session();
        $session->set("loggedIn",TRUE);
        $session->set("name",$this->name);
    }
}