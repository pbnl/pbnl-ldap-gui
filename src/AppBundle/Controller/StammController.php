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
        $session = new Session();

        //Security stuff
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $this->denyAccessUnlessGranted('ROLE_STAMM_'.$request->get("stamm",$authUser->getStamm()), null, 'Unable to access this page!');


        $org = $this->get("organisation");
        $userManager= $org->getUserManager();

        //Search users
        $peopleList = $userManager->getAllUsers($request->get("stamm",$session->get("stamm")),"");

        return$this->render(":default:showUsersInOneTabel.html.twig",array(
            "users"=>$peopleList,
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
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $this->denyAccessUnlessGranted('ROLE_STAMM_'.$request->get("stamm",$authUser->getStamm()), null, 'Unable to access this page!');
        $this->denyAccessUnlessGranted('ROLE_STAVO', null, 'Unable to access this page!');

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
        //The stavo the user will get removed from
        $stammName = $request->get("stamm");

        //Security stuff
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $this->denyAccessUnlessGranted('ROLE_STAMM_'.$stammName, null, 'Unable to access this page!');
        $this->denyAccessUnlessGranted('ROLE_STAVO', null, 'Unable to access this page!');

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
            $this->addFlash("success","User wurde aus dem Stavo gelöscht");
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

        //Security stuff
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $this->denyAccessUnlessGranted('ROLE_STAMM_'.$request->get('form[stamm]'), null, 'Unable to access this page!');
        $this->denyAccessUnlessGranted('ROLE_STAVO', null, 'Unable to access this page!');

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
            try
            {
                $stavo->addMember($user->dn);
                $this->addFlash("success","Benutzer zum Stavo hinzugefügt");
            }
            catch (Exception $e)
            {
                $this->addFlash("error",$e->getMessage());
            }
        }
        return$this->redirectToRoute("Zeige Stavo");
    }
}