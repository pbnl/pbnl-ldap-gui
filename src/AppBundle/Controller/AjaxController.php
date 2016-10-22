<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 23:19
 */

namespace AppBundle\Controller;


use AppBundle\model\usersLDAP\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{

    /**
     * @Route("/ajax/usersNotInGroup", name="Benutzer die nicht in einer Gruppe")
     * @param Request $request
     * @return Response
     */
    public function getUsersNotInGroup(Request $request){
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $gid = $request->query->get('gid');
        $searchedUserName = $request->query->get('searchedUserName');

        $org = $this->get("organisation");

        //Security stuff
        $teamManager = $org->getTeamManager();
        $team = $teamManager->getAllTeams($request->get("gid",""))[0];

        if(!$team->isDNMember($this->get("login.User")->getDN())) return $this->redirectToRoute("PermissionError");

        $userManager = $org->getUserManager();
        $users = $userManager->getAllUsers("",$searchedUserName);
        $usersNotInGroup = array();
        $team = $teamManager->getAllTeams($gid)[0];
        foreach ($users as $user)
        {
            if(!$team->isDNMember($user->dn)) array_push($usersNotInGroup,$user->givenName);
        }

        $response = array("code" => 100, "success" => true, "users"=>$usersNotInGroup);

        return new Response(json_encode($response));
    }
}