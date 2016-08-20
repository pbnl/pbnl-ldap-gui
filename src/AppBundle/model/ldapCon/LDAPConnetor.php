<?php
namespace AppBundle\model\ldapCon;
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 04.08.16
 * Time: 12:20
 */

class LDAPConnetor
{

    private $ldap;
    private $ds;

    /**
     * @return
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    /**
     *Opens a connection to a LDAP-Server and binds to it with the data of the getenv
     *
     */
    public function intiLDAPConnection()
    {
        if(getenv("LDAP_IP") != FALSE && getenv("LDAP_BIND_DN") != FALSE && getenv("LDAP_BIND_PWD") != FALSE) {
            $ip = getenv("LDAP_IP");
            $bindDn = getenv("LDAP_BIND_DN");
            $bindPasswd = getenv("LDAP_BIND_PWD");
        } 
        else
        {
            throw new NoLDAPBindDataException("No Data in the ENV-Vars", 1);
        }

        $this->ds = ldap_connect($ip);
        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        $this->ldap = ldap_bind($this->ds, $bindDn, $bindPasswd);
    }

    public function closeLDAPConnection()
    {
        ldap_close($this->ldap);
    }
    public function getCon()
    {
        return $this->ds;
    }
}