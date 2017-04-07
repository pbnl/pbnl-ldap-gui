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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add("materialPieceID",IntegerType::class, array("attr"=>["placeholder"=>"Materialnummer"],'label' => "Materialnummer"))
            ->add("date",DateType::class ,array("attr"=>["readonly"=>"","placeholder"=>"Datum"],"label"=>"Erstellungsdatum"))
            ->add("requestYear",IntegerType::class ,array("attr"=>["readonly"=>"","placeholder"=>"Jahr"],"label"=>"Erstellungsjahr"))
            ->add("save",SubmitType::class, array("label" => "Antrag stellen"))
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
            ->add("offersIds",TextType::class, array("attr"=>["readonly"=>"","placeholder"=>"Ids"],"label"=>"Materialofferids"))
            ->add("save",SubmitType::class, array("label" => "Materialstück erstellen"))
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
}