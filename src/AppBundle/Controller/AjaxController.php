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


        $gid = $request->query->get('gid');
        $searchedUserName = $request->query->get('searchedUserName');

        $org = $this->get("organisation");

        //Security stuff
        $teamManager = $org->getTeamManager();
        $team = $teamManager->getAllTeams($request->get("gid",""))[0];


        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        if(!$team->isDNMember($authUser->getDN())) throw $this->createAccessDeniedException();

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