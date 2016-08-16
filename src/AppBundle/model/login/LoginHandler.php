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

    /**
     * Tries to find a user in the LDAP with a password and a name stored in the object fields
     * If there is a correct user -> login
     *
     * @return bool
     */
    public function login()
    {
        //Open a connection to the LDAP
        $ldapConnector = new LDAPConnetor();
        $ldapConnector->intiLDAPConnection();
        $ldapCon = $ldapConnector->getCon();

        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $filter="(|(givenname=$this->name))";

        //Search
        $result = ldap_search($ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapCon));
        $data = ldap_get_entries($ldapCon, $result);

        //There can be only one user with a the name
        if ($data["count"] != 1) return FALSE;
        //Correct password an name?
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

    /**
     * Sets some variables in the session to see if someone is logged in
     */
    private function loginSuccess()
    {
        $session = new Session();
        $session->set("loggedIn",TRUE);
        $session->set("name",$this->name);
    }
}