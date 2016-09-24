<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 20:51
 */

namespace AppBundle\Controller;

use AppBundle\model\formDataClasses\AddTeamMemberFormData;
use AppBundle\model\usersLDAP\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class TeamController extends Controller
{

    /**
     * @Route("/team/showTeams", name="Alle Teams")
     * @param Request $request
     * @return Response
     */
    public function getAllTeams(Request $request)
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

    /**
     * @Route("/team/detailTeams", name="Team Detail")
     * @param Request $request
     * @return Response
     */
    public function getTeamDetails(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        //Id of the group we are looking for
        $gid = $request->get("gid","");

        $org = new Organisation($this->get("ldap.frontend"));
        $teamManager = $org->getTeamManager();
        $team = $teamManager->getAllTeams($gid)[0];
        $team->fetchUserData();
        if(!$team->isDNMember($this->get("login.User")->getDN())) return $this->redirectToRoute("PermissionError");

        //Create the form
        $user = new AddTeamMemberFormData();
        $addMemberForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addTeamMemberForm']])
            ->add("givenName",ChoiceType::class)
            ->add("gid",HiddenType::class,array("data"=>$gid))
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-primary"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Team Mitglied hinzufügen"))
            ->getForm();

        return $this->render(":default:teamDetail.html.twig",array(
            "addTeamMemberForm" => $addMemberForm->createView(),
            "team" => $team,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage
        ));
    }

    /**
     * @Route("/team/addMemberToTeam", name="Team Mitglied hinzufügen")
     * @param Request $request
     * @return Response
     */
    public function addMemberToTeam(Request$request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        $org = new Organisation($this->get("ldap.frontend"));

        //Get all users witch are not in the group $gid
        $team = $org->getTeamManager()->getAllTeams($request->get("gid",""))[0];

        if(!$team->isDNMember($this->get("login.User")->getDN())) return $this->redirectToRoute("PermissionError");

        $userManager = $org->getUserManager();
        $users = $userManager->getAllUsers("",$request->get("form[givenName]"));
        $usersNotInGroup = array();
        $team = $org->getTeamManager()->getAllTeams($request->get("form[gid]"))[0];
        foreach ($users as $user)
        {
            if(!$team->isDNMember($user->dn)) array_push($usersNotInGroup,$user->givenName);
        }

        //Create the form
        $formDataUser = new AddTeamMemberFormData();
        $addMemberForm = $this->createFormBuilder($formDataUser,['attr' => ['class' => 'form-addTeamMemberForm']])
            ->add("givenName",ChoiceType::class,array(
                'choices'  => $usersNotInGroup,
            ))
            ->add("gid",HiddenType::class)
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Team Mitglied hinzufügen"))
            ->getForm();

        //Handel the form input
        $addMemberForm->handleRequest($request);
        if($addMemberForm->isSubmitted() && $addMemberForm->isValid())
        {
            //Add the user to the team
            $user = $org->getUserManager()->getUserByName($formDataUser->givenName);
            $team = $org->getTeamManager()->getTeamByGid($formDataUser->gid);
            if (!$loginHandler->checkPermissions("inStamm:$team->name")) return $this->redirectToRoute("PermissionError");

            $team->addMember($user->dn);
            array_push($successMessage,"Benutzer hinzugefügt");
        }


        return $this->render(":default:easyMessage.html.twig",array(
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage
        ));
    }

    /**
     * @Route("/team/delMemberFromTeam", name="Team Mitglied löschen")
     * @param Request $request
     * @return Response
     */
    public function delTeamMember(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        $org = new Organisation($this->get("ldap.frontend"));


        $team = $org->getTeamManager()->getAllTeams($request->get("gid",""))[0];

        if(!$team->isDNMember($this->get("login.User")->getDN())) return $this->redirectToRoute("PermissionError");

        //Del the user from the team
        $user = $org->getUserManager()->getUserByUid($request->get("uidNumber",""));
        $team->removeMember($user->dn);


        array_push($successMessage,"Benutzer aus dem Team entfert");


        return $this->render(":default:easyMessage.html.twig",array(
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage
        ));
    }
}