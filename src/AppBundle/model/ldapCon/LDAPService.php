<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 17.08.16
 * Time: 12:02
 */

namespace AppBundle\model\ldapCon;


use AppBundle\model\SSHA;
use AppBundle\model\usersLDAP\Group;
use AppBundle\model\usersLDAP\User;

class LDAPService
{

    private $LDAPConnector = null;
    private $ldapCon = null;

    function __construct()
    {
        $this->LDAPConnector = new LDAPConnetor();
        $this->LDAPConnector->intiLDAPConnection();
        $this->ldapCon = $this->LDAPConnector->getCon();
    }

    /**
     * Returns all users of a group or every user
     *
     * @param $groupFilterName
     * group we are looking for. All if $groupFilter = ""
     *
     * @return array
     */
    public function getAllUsers($groupFilterName = "",$userFilterName="")
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";

        //Search filters
        if($userFilterName != "") $filter="(&(objectClass=inetOrgPerson) (givenname=*$userFilterName*))";
        else $filter="(&(objectClass=inetOrgPerson))";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $people = Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++) {
                $user = new User($data[$i]);
                array_push($people,$user);
            }
        }

        //if you look for a special group
        if ($groupFilterName != "")
        {
            $filteredPeople = Array();
            //get the group
            $filterGroup = $this->getAllGroups($groupFilterName)[0];
            //who is in the group?
            foreach ($people as $onePerson)
            {
                if($onePerson->memberOF($filterGroup))
                {
                    array_push($filteredPeople,$onePerson);
                }
            }
            return $filteredPeople;
        }
        else
        {
            return $people;
        }
    }

    /**
     * Returns all groups or a group which name is equal to the $groupFilterName string
     *
     * @param string $groupFilterName
     * @return array
     * An array with all groups
     */
    public function getAllGroups($groupFilterName = "")
    {
        //Search options
        $ldaptree = "ou=Group,dc=pbnl,dc=de";

        //Search filters
        if ($groupFilterName != "")
        {
            $filter="(&(objectClass=posixGroup)(cn=$groupFilterName))";
        }
        else
        {
            $filter="(&(objectClass=posixGroup))";
        }

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $groups = Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++) {
                $group = new Group();
                $group->name = $data[$i]["cn"][0];
                $group->dn = $data[$i]["dn"];
                $group->gidNumber =$data[$i]["gidnumber"][0];
                $member = $data[$i]["memberuid"];
                for ($j = 0;$j < $member["count"];$j++)
                {
                    $group->addMember($member[$j]);
                }
                array_push($groups,$group);
            }
        }

        return $groups;
    }

    /**
     * Adds a new user to the LDAP
     * @param User $user
     * @return array with all user attributes
     */
    public function addAUser(User $user)
    {
        $userForLDAP = Array();
        $userForLDAP["objectclass"][0] = "inetOrgPerson";
        $userForLDAP["objectclass"][1] = "posixAccount";
        $userForLDAP["objectclass"][2] = "pbnlAccount";
        $userForLDAP["cn"][0] = $user->firstName;
        $userForLDAP["gidnumber"][0] = "501";
        $userForLDAP["uidnumber"][0] = $this->getHighestUidNumber()+1;
        $userForLDAP["homedirectory"][0] = "/home/".$user->givenName;
        $userForLDAP["sn"][0] = $user->secondName;
        $userForLDAP["uid"][0] = $user->givenName;
        $userForLDAP["l"][0] = "Hamburg";
        $userForLDAP["mail"][0] =  strtolower($user->givenName)."@pbnl.de";
        $userForLDAP["mobile"][0] = "0";
        $userForLDAP["postalcode"][0] = "0";
        $userForLDAP["street"][0] = "0";
        $userForLDAP["telephonenumber"][0] = "0";
        $userForLDAP["userpassword"][0] = SSHA::ssha_password_gen($user->clearPassword);
        $userForLDAP["givenname"][0] = $user->givenName;

        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $ou = $user->ouGroup;

        ldap_add($this->ldapCon, "givenName=$user->givenName, ou=$ou, $ldaptree", $userForLDAP);

        //We need this for later
        $userForLDAP["dn"] = "givenName=$user->givenName, ou=$ou, $ldaptree";

        return new User($userForLDAP);



    }

    public function getUserByName($name)
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $filter="(|(givenname=$name))";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        //There can be only one user with the name we are looking for
        if ($data["count"] != 1) return FALSE;
        return new User($data[0]);
    }

    public function getUserByUidNumber($uidNumber)
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $filter="(|(uidnumber=$uidNumber))";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        //There can be only one user with the name we are looking for
        if ($data["count"] != 1) return FALSE;
        return new User($data[0]);
    }

    public function getUserByDN($dn)
    {
        //Search options
        $ldaptree = $dn;
        $filter="(objectClass=*)";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        //There can be only one user with the dn we are looking for
        if ($data["count"] != 1) return FALSE;
        return new User($data[0]);
    }

    public function getHighestUidNumber()
    {
        $users = $this->getAllUsers();
        $highestUidNumber = 0;
        foreach ($users as $oneUser)
        {
            if($oneUser->uidNumber >= $highestUidNumber) $highestUidNumber = $oneUser->uidNumber;
        }
        return $highestUidNumber;
    }

    /**
     * Returns the name of the ou in the people folder
     * @return array
     */
    public function getOUGroupsNames()
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";

        //Search filters
        $filter="(&(objectClass=organizationalUnit) (!(ou=people)))";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $ou= Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++)
            {
                array_push($ou,$data[$i]["ou"][0]);
            }
        }
        return $ou;
    }

    /**
     * Adds a user with his DN to an Group
     * @param $userDN
     * @param $group
     */
    public function addUserDNToGroup($userDN ,$group)
    {
        //Add options
        $ldaptree = "ou=Group,dc=pbnl,dc=de";
        $group_info['memberuid'] = str_replace(", ",",",$userDN);

        //Add
        ldap_mod_add($this->ldapCon,"cn=$group,$ldaptree",$group_info);
    }

    /**
     * Adds a mail with his address to a forward
     * @param $mail
     * @param $forward
     */
    public function addMailToForward($mail,$forward)
    {
        //Add options
        $ldaptree = "ou=Forward,dc=pbnl,dc=de";
        $forward_info['forward'] = $mail;

        //Add
        ldap_mod_add($this->ldapCon,"mail=$forward,$ldaptree",$forward_info);
    }

    /**
     * Removes a user with his DN from an Group
     * @param $userDN
     * @param $group
     */
    public function removeUserDNFromGroup($userDN,$group)
    {
        //Del options
        $ldaptree = "ou=Group,dc=pbnl,dc=de";
        $group_info['memberuid'] = str_replace(", ",",",$userDN);

        //Del
        ldap_mod_del($this->ldapCon, "cn=$group,$ldaptree", $group_info);

    }

    /**
     * Removes a mail with his address from a forward
     * @param $mail
     * @param $forward
     */
    public function removeMailFromForward($mail,$forward)
    {
        //Del options
        $ldaptree = "ou=Forward,dc=pbnl,dc=de";
        $forward_info['forward'] = $mail;

        //Del
        ldap_mod_del($this->ldapCon,"mail=$forward,$ldaptree",$forward_info);
    }

    /**
     * Removes a user with the DN $userDN from the LDAP
     * @param $userDN
     */
    public function removeUserWithDN($userDN)
    {
        //Del
        ldap_delete($this->ldapCon,$userDN);
    }
}