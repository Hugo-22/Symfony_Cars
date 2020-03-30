<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureType;
use App\Entity\RechercheVoiture;
use App\Form\RechercheVoitureType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(VoitureRepository $repo, PaginatorInterface $paginatorInterface, HttpFoundationRequest $request)
    {
        $rechercheVoiture = new RechercheVoiture();

        $form = $this->createForm(RechercheVoitureType::class, $rechercheVoiture);
        $form->handleRequest($request);

        $voitures = $paginatorInterface->paginate(
            $repo->findAllWithPagination($rechercheVoiture),
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('voitures/voitures.html.twig', [
            "voitures" => $voitures,
            "form" => $form->createView(),
            "admin" => true
        ]);
    }

    /**
     * @Route("/admin/creation", name="creationVoiture")
     * @Route("/admin/{id}", name="updateVoiture", methods="GET|POST")
     */
    public function update(Voiture $voiture = null, HttpFoundationRequest $request, EntityManagerInterface $objectManager) {

        if(!$voiture) {
         $voiture = new Voiture();   
        }

        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $objectManager->persist($voiture);
            $objectManager->flush();
            $this->addFlash('success', "L'action a été effectué");
            return $this->redirectToRoute("admin");
        }

        return $this->render('admin/update.html.twig', [
            "voiture" => $voiture,
            "form" => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/{id}", name="supVoiture", methods="SUP")
     */
    public function delete(Voiture $voiture, HttpFoundationRequest $request, EntityManagerInterface $objectManager) {

       if($this->isCsrfTokenValid("SUP".$voiture->getId(), $request->get('_token'))) {
           $objectManager->remove($voiture);
           $objectManager->flush();
           $this->addFlash('success', "L'action a été effectué");
            return $this->redirectToRoute("admin");
       }
    }
}



