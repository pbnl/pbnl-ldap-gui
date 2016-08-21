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
    public function getAllUsers($groupFilterName = "")
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";

        //Search filters
        $filter="(&(objectClass=inetOrgPerson))";

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

    public function addAUser(User $user)
    {
        $userForLDAP = Array();
        $userForLDAP["objectClass"][0] = "inetOrgPerson";
        $userForLDAP["objectClass"][1] = "posixAccount";
        $userForLDAP["objectClass"][2] = "pbnlAccount";
        $userForLDAP["cn"] = $user->firstName;
        $userForLDAP["gidNumber"] = "501";
        $userForLDAP["uidNumber"] = $this->getHighestUidNumber()+1;
        $userForLDAP["homeDirectory"] = "/home/".$user->givenName;
        $userForLDAP["sn"] = $user->secondName;
        $userForLDAP["uid"] = $user->givenName;
        $userForLDAP["l"] = "Hamburg";
        $userForLDAP["mail"] =  strtolower($user->givenName)."@pbnl.de";
        $userForLDAP["mobile"] = "0";
        $userForLDAP["postalCode"] = "0";
        $userForLDAP["street"] = "0";
        $userForLDAP["telephoneNumber"] = "0";
        $userForLDAP["userPassword"] = SSHA::ssha_password_gen($user->clearPassword);

        $ldaptree = "ou=People,dc=pbnl,dc=de";
        $ou = $user->ouGroup;


        ldap_add($this->ldapCon, "givenName=$user->givenName, ou=$ou, $ldaptree", $userForLDAP);
        return $userForLDAP;



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
        $filter="(&(objectClass=organizationalUnit))";

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

}