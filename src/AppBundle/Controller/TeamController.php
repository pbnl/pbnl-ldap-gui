<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 20:51
 */

namespace AppBundle\Controller;

use AppBundle\model\formDataClasses\AddTeamMemberFormData;
use AppBundle\model\ldapCon\GroupNotFoundException;
use AppBundle\model\ldapCon\UserNotInGroupException;
use AppBundle\model\usersLDAP\Organisation;
use AppBundle\model\usersLDAP\UserNotUnique;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
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
        $org = $this->get("organisation");
        $teamManager = $org->getTeamManager();
        $teams = $teamManager->getAllTeams("");
        $myTeams = array();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        foreach ($teams as $team)
        {
            if($team->isDNMember($authUser->getDN())) array_push($myTeams,$team);
        }

        return $this->render(":default:showTeams.html.twig",array(

            "teamList" => $myTeams,
        ));
    }

    /**
     * @Route("/team/detailTeams", name="Team Detail")
     * @param Request $request
     * @return Response
     */
    public function getTeamDetails(Request $request)
    {
        //Id of the group we are looking for
        $gid = $request->get("gid","");

        $org = $this->get("organisation");
        $teamManager = $org->getTeamManager();
        try
        {
            $team = $teamManager->getAllTeams($gid)[0];
        }
        catch (GroupNotFoundException $e)
        {
            $this->addFlash("notice","Group not found");
            return $this->redirectToRoute("Alle Teams");
        }
        //Security stuff
        $this->denyAccessUnlessGranted('ROLE_TEAM_'.$team->name, null, 'Unable to access this page!');


        $team->fetchUserData();
        
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
        ));
    }

    /**
     * @Route("/team/addMemberToTeam", name="Team Mitglied hinzufügen")
     * @param Request $request
     * @return Response
     */
    public function addMemberToTeam(Request$request)
    {
        //TODO: Jonathan kann nicht hinzugefügt werden!!!!!
        $org = $this->get("organisation");
        $logger = $this->get("logger");

        try
        {
            //Get Team
            $team = $org->getTeamManager()->getAllTeams($request->get("gid", ""))[0];
        }
        catch (GroupNotFoundException $e)
        {
            $this->addFlash("notice","Group not found");
        }

        //Security stuff
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inTeam:".$team->name)) return $this->redirectToRoute("PermissionError");

        //Get all users witch are not in the group $gid
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
            try
            {
                $user = $org->getUserManager()->getUserByName($formDataUser->givenName);
                $team = $org->getTeamManager()->getTeamByGid($formDataUser->gid);
                //Security stuff
                if (!$loginHandler->checkPermissions("inTeam:" . $team->name)) return $this->redirectToRoute("PermissionError");

                $team->addMember($user->dn);
                $this->addFlash("success","Benutzer wurde hinzugefügt");
            }
            catch (Exception $e)
            {
                $this->addFlash("error",$e->getMessage());
            }

        }


        return $this->redirectToRoute("Alle Teams");
    }

    /**
     * @Route("/team/delMemberFromTeam", name="Team Mitglied löschen")
     * @param Request $request
     * @return Response
     */
    public function delTeamMember(Request $request)
    {
        $org = $this->get("organisation");

        //get the team
        $team = $org->getTeamManager()->getAllTeams($request->get("gid",""))[0];

        //Security stuff
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inTeam:".$team->name)) return $this->redirectToRoute("PermissionError");

        //Del the user from the team
        try
        {
            $user = $org->getUserManager()->getUserByUid($request->get("uidNumber",""));
            $team->removeMember($user->dn);
            $this->addFlash("success","Benutzer aus dem Team entfernt");
        }
        catch (Exception $e)
        {
            $this->addFlash("error",$e->getMessage());
        }

        return $this->redirectToRoute("Alle Teams");
    }
}