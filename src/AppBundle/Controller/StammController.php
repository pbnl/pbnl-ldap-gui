<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 12.08.16
 * Time: 20:08
 */

namespace AppBundle\Controller;


use AppBundle\model\ArrayMethods;
use AppBundle\model\usersLDAP\People;
use AppBundle\model\usersLDAP\Stavo;
use AppBundle\model\usersLDAP\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class StammController extends Controller
{
    /**
     * @Route("/stamm/showAll", name="Stamm")
     */
    public function getStamm(Request $request)
    {
        $errorMessage = Array();
        $successMessage = Array();

        $session = new Session();

        //Security stuff
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inStamm:".$request->get("stamm",$session->get("stamm")))) return $this->redirectToRoute("PermissionError");

        //Get all users and search for name and groupq if wanted
        $people = new People($this->get("ldap.frontend"));

        //Search users
        $peopleList = $people->getAllUsers($request->get("stamm",$session->get("stamm")),"");

        return$this->render(":default:showUsersInOneTabel.html.twig",array(
            "users"=>$peopleList,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage

        ));
    }

    /**
     * @Route("/stamm/showStavo", name="Zeige Stavo")
     */
    public function showStavo(Request $request)
    {
        $org = $this->get("organisation");
        $userManager = $org->getUserManager();
        $groupManager = $org->getGroupManager();
        $session = new Session();
        $stamm = $session->get("stamm");

        //Security stuff
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inStamm:".$request->get("stamm",$stamm))) return $this->redirectToRoute("PermissionError");

        $group = $groupManager->getStavo($request->get("stamm",$stamm))->getMembersUser();

        $stammesMember = $groupManager->getAllGroups($request->get("stamm",$stamm))[0]->getListWithDNAndName();

        //Create add stavo member form
        $user = $userManager->getEmptyUser();
        $addstavoMemberForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addStavoMemberForm']])
            ->add('dn', ChoiceType::class, array(
                'choices'  => $stammesMember,
            ))
            ->add('stamm',HiddenType::class, array(
            ))
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Stavo Mitglied hinzufügen"))
            ->getForm();


        return$this->render(":default:showStavo.html.twig",array(
            "addStavoMemberForm"=> $addstavoMemberForm->createView(),
            "users" => $group
        ));
    }

    /**
     * @Route("/stamm/stavo/delMember", name="Loesche Stavo Mitglied")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delStavoMember(Request $request)
    {
        $errorMessage = Array();
        $successMessage = Array();

        //The stavo the user will get removed from
        $stammName = $request->get("stamm");

        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inStamm:$stammName,inGroup:stavo")) return $this->redirectToRoute("PermissionError");

        //Get the stamm
        $org = $this->get("organisation");
        $userManager = $org->getUserManager();
        $groupManager = $org->getGroupManager();
        $stamm = $groupManager->getStavo($stammName);

        //The uid of the user who should get deleted
        $uidNumber = $request->get("uidNumber");

        try
        {
            $stamm->removeMember($userManager->getUserByUid($uidNumber)->dn);
            $this->addFlash("succsses","User wurde aus dem Stavo gelöscht");
        }
        catch (Exception $e)
        {
            $this->addFlash("error",$e->getMessage());
        }

        return$this->redirectToRoute("Zeige Stavo");
    }

    /**
     * @Route("/stamm/stavo/addMember", name="Stavo Mitglied hinzufügen")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addStavoMember(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inStamm:".$request->get('form[stamm]').",inGroup:stavo")) return $this->redirectToRoute("PermissionError");

        $org = $this->get("organisation");
        $people = $org->getUserManager();
        $groupManager = $org->getGroupManager();
        $stammesMember = $groupManager->getGroupByName($request->get("form[stamm]"))->getListWithDNAndName();

        //Create add stavo member form
        $user = $people->getEmptyUser();
        $addstavoMemberForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addStavoMemberForm']])
            ->add('dn', ChoiceType::class, array(
                'choices'  => $stammesMember,
            ))
            ->add('stamm',HiddenType::class, array(
            ))
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Stavo Mitglied hinzufügen"))
            ->getForm();

        //Handel the form input
        $addstavoMemberForm->handleRequest($request);
        if($addstavoMemberForm->isSubmitted() && $addstavoMemberForm->isValid())
        {
            //Create the new user
            $stavo = $groupManager->getStavo($request->get("form[stamm]"));
            $stavo->addMember($user->dn);
            $this->addFlash("succsses","Benutzer zum Stavo hinzugefügt");
        }
        return$this->redirectToRoute("Zeige Stavo");
    }
}