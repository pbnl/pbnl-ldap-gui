<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 20:51
 */

namespace AppBundle\Controller;

use AppBundle\model\usersLDAP\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TeamController extends Controller
{

    /**
     * @Route("/team/showTeams", name="Alle Teams")
     * @param Request $request
     * @return Response
     */
    public function getAllUsers(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        $org = new Organisation($this->get("ldap.frontend"));
        $teamManager = $org->getTeamManager();
        $teams = $teamManager->getAllTeams("");
        $myTeams = array();
        foreach ($teams as $team)
        {
            if($team->isDNMember($this->get("login.User")->getDN())) array_push($myTeams,$team);
        }

        return $this->render(":default:showTeams.html.twig",array(

            "teamList" => $myTeams,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage
        ));
    }
}