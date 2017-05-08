<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 05.04.17
 * Time: 15:45
 */

namespace AppBundle\Controller;

use AppBundle\Entity\material\MaterialOffer;
use AppBundle\Entity\material\MaterialPiece;
use AppBundle\Entity\material\MaterialRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;

class MaterialRequestController extends Controller
{
    /**
     * @Route("/material/materialRequests/show", name="Zeige alle Materialanträge")
     */
    public function showAllMaterialRequests(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:material\MaterialRequest');
        $materialRequests = $repository->findAll();

        $repository = $this->getDoctrine()->getRepository('AppBundle:material\MaterialPiece');
        $materialPieces = $repository->findAll();

        $materialPiecesByID = array();
        foreach ($materialPieces as $materialPiece)
        {
            $id = $materialPiece->getId();
            $materialPiecesByID[$id] = $materialPiece;
        }

        return $this->render(":default:schowAllMaterialRequests.html.twig",array(
            "materialRequests" => $materialRequests,
            "materialPieces" => $materialPiecesByID,
        ));
    }

    /**
     * @Route("/material/materialRequests/addRequest", name="Erstelle einen Materialantrag")
     */
    public function addMaterialRequest(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        $materialRequest = new MaterialRequest();
        $materialRequest->setStamm($authUser->getStamm());
        $materialRequest->setDate(new DateTime());
        $materialRequest->setRequestYear(getdate()["year"]);

        $addRequestForm = $this->createFormBuilder($materialRequest)
            ->add("stamm",TextType::class ,array("attr"=>["readonly"=>"","placeholder"=>"Stamm"],"label"=>"Stamm"))
            ->add("quantity",IntegerType::class, array("attr"=>["placeholder"=>"Anzahl"],'label' => "Anzahl"))
            ->add("materialPieceID",IntegerType::class, array("attr"=>["placeholder"=>"Von unten aussuchen"],'label' => "Material ID"))
            ->add("date",DateType::class ,array("attr"=>["readonly"=>"","placeholder"=>"Datum"],"label"=>"Erstellungsdatum"))
            ->add("requestYear",IntegerType::class ,array("attr"=>["readonly"=>"","placeholder"=>"Jahr"],"label"=>"Erstellungsjahr"))
            ->add("save",SubmitType::class, array("label" => "Antrag stellen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $addRequestForm->handleRequest($request);

        if ($addRequestForm->isSubmitted() && $addRequestForm->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $materialRequest = $addRequestForm->getData();

            $materialRequest->setStamm($authUser->getStamm());
            $materialRequest->setDate(new DateTime());
            $materialRequest->setRequestYear(getdate()["year"]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($materialRequest);
            $em->flush();

            return $this->redirectToRoute('Zeige alle Materialanträge');
        }

        $repository = $this->getDoctrine()->getRepository('AppBundle:material\MaterialPiece');
        $materialPieces = $repository->findAll();

        $materialPiecesByID = array();
        foreach ($materialPieces as $materialPiece)
        {
            $id = $materialPiece->getId();
            $materialPiecesByID[$id] = $materialPiece;
        }

        return $this->render(":default:addMaterialRequest.html.twig",array(
            "addRequestForm" => $addRequestForm->createView(),
            "materialPiecesByID" => $materialPiecesByID,
        ));
    }

    /**
     * @Route("/material/materialRequests/addPiece", name="Erstelle eine Materialstück")
     */
    public function addMaterialPiece(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        $materialPiece = new MaterialPiece();

        $addPieceForm = $this->createFormBuilder($materialPiece)
            ->add("name",TextType::class ,array("attr"=>["placeholder"=>"Name"],"label"=>"Name"))
            ->add("description",TextType::class,array("attr"=>["placeholder"=>"Beschreibung"],"label"=>"Beschreibung"))
            ->add("offersIds",TextType::class, array("attr"=>["class"=>"readonly","pattern"=>"[0-9]+( ; [0-9]+)*","placeholder"=>"Ids"],"label"=>"Materialofferids"))
            ->add("save",SubmitType::class, array("label" => "Materialstück erstellen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $addPieceForm->handleRequest($request);

        if ($addPieceForm->isSubmitted() && $addPieceForm->isValid()) {

            $materialPiece = $addPieceForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($materialPiece);
            $em->flush();

            return $this->redirectToRoute('Erstelle einen Materialantrag');
        }

        return $this->render(":default:addMaterialPiece.html.twig",array(
            "addPieceForm" => $addPieceForm->createView(),
        ));
    }

    /**
     * @Route("/material/materialRequests/delRequest", name="Loesche einen Materialantrag")
     */
    public function delMaterialRequest(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        try {
            $id = $request->get("id");
        }catch (Exception $e)
        {
            $this->addFlash("error","Keine ID");
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $em = $this->getDoctrine()->getManager();
        $materialRequest = $em->getRepository('AppBundle:material\MaterialRequest')
            ->find($id);

        if (!$materialRequest) {
            $this->addFlash("error",'Kein Antrag gefunden: id '.$id);
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $this->denyAccessUnlessGranted('ROLE_STAMM_'.$materialRequest->getStamm(), null, 'Unable to access this page!');
        $em->remove($materialRequest);
        $em->flush();
        $this->addFlash("success",'Antrag gelöscht: id '.$id);

        return $this->redirectToRoute("Zeige alle Materialanträge");
    }

    /**
     * @Route("/material/materialRequests/delPiece", name="Loesche einen Materialstueck")
     */
    public function delMaterialPiece(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        try {
            $id = $request->get("id");
        }catch (Exception $e)
        {
            $this->addFlash("error","Keine ID");
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $em = $this->getDoctrine()->getManager();
        $materialPiece = $em->getRepository('AppBundle:material\MaterialPiece')
            ->find($id);

        if (!$materialPiece) {
            $this->addFlash("error",'Kein Materialstück gefunden: id '.$id);
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $this->denyAccessUnlessGranted('ROLE_STAVO', null, 'Unable to access this page!');
        $em->remove($materialPiece);
        $em->flush();
        $this->addFlash("success",'Materialstück gelöscht: id '.$id);

        return $this->redirectToRoute("Zeige alle Materialanträge");
    }

    /**
     * @Route("/material/materialRequests/showRequest", name="Zeige einen Antrag")
     */
    public function showMaterialRequest(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        try {
            $id = $request->get("id");
        }catch (Exception $e)
        {
            $this->addFlash("error","Keine ID");
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $em = $this->getDoctrine()->getManager();
        $materialRequest = $em->getRepository('AppBundle:material\MaterialRequest')
            ->find($id);

        if (!$materialRequest) {
            $this->addFlash("error",'Kein Materialantrag gefunden: id '.$id);
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $editRequestForm = $this->createFormBuilder($materialRequest)
            ->add("quantity",IntegerType::class, array("attr"=>["placeholder"=>"Anzahl"],'label' => "Anzahl"))
            ->add("materialPieceID",IntegerType::class, array("attr"=>["placeholder"=>"Von unten aussuchen"],'label' => "Material ID"))
            ->add("save",SubmitType::class, array("label" => "Daten speichern","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $editRequestForm->handleRequest($request);

        if ($editRequestForm->isSubmitted() && $editRequestForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_STAMM_'.$materialRequest->getStamm(), null, 'Unable to access this page!');
            $materialRequest = $editRequestForm->getData();

            $em->persist($materialRequest);
            $em->flush();
            $this->addFlash("success","Änderungen gespeichert");
            return $this->redirectToRoute('Zeige alle Materialanträge');
        }

        $editRequestForm = $editRequestForm->createView();

        if(!$this->get('security.authorization_checker')->isGranted('ROLE_STAMM_'.$materialRequest->getStamm())) $editRequestForm = false;

        return $this->render(":default:showMaterialRequest.html.twig",array(
            "editRequestForm" => $editRequestForm,
            "request" => $materialRequest,
        ));
    }

    /**
     * @Route("/material/materialRequests/showPiece", name="Zeige ein Materialstück")
     */
    public function showMaterialPiece(Request $request)
    {
        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        try {
            $id = $request->get("id");
        }catch (Exception $e)
        {
            $this->addFlash("error","Keine ID");
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }

        $em = $this->getDoctrine()->getManager();
        $materialPiece = $em->getRepository('AppBundle:material\MaterialPiece')
            ->find($id);

        if (!$materialPiece) {
            $this->addFlash("error",'Kein Materialstück gefunden: id '.$id);
            return $this->redirectToRoute("Zeige alle Materialanträge");
        }


        $offerIdsArray = explode(" ; ",$materialPiece->getOffersIds());
        $offers = array();
        foreach ($offerIdsArray as $offer)
        {
            $materialOffer = $em->getRepository('AppBundle:material\MaterialOffer')
                ->find($offer);
            array_push($offers,$materialOffer);
        }
        $editMaterialPieceForm = $this->createFormBuilder($materialPiece)
            ->add("name",TextType::class, array("attr"=>["placeholder"=>"Name"],'label' => "Name"))
            ->add("description",TextareaType::class, array("attr"=>["placeholder"=>"Beschreibung"],'label' => "Beschreibung"))
            ->add("offersIds",TextType::class, array("attr"=>["class"=>"readonly","pattern"=>"[0-9]+( ; [0-9]+)*","placeholder"=>"Angebotsnummern"],'label' => "Angebotsnummern"))
            ->add("save",SubmitType::class, array("label" => "Daten speichern","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $editMaterialPieceForm->handleRequest($request);

        if ($editMaterialPieceForm->isSubmitted() && $editMaterialPieceForm->isValid()) {
            $materialPiece = $editMaterialPieceForm->getData();

            $em->persist($materialPiece);
            $em->flush();
            $this->addFlash("success","Änderungen gespeichert");
            return $this->redirectToRoute('Zeige alle Materialanträge');
        }

        $editMaterialPieceForm = $editMaterialPieceForm->createView();

        return $this->render(":default:showMaterialPiece.html.twig",array(
            "editMaterialPieceForm" => $editMaterialPieceForm,
            "materialPiece" => $materialPiece,
            "materialOffers" => $offers,
        ));
    }
}