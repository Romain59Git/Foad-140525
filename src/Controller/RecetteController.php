<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RecetteTypeForm;
use App\Entity\Recette;

class RecetteController extends AbstractController
{
    #[Route('/recette', name: 'app_recette', methods:['GET'])]
    public function index(RecetteRepository $recetteRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $recettes = $paginator->paginate(
            $recetteRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('/pages/recette/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[Route('/recette/new', name: 'app_recette_new', methods:['GET','POST'])]
    public function new(Request $request, RecetteRepository $recetteRepository): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteTypeForm::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recetteRepository->save($recette, true);

            $this->addFlash('success', 'La recette a été créée avec succès.');
            return $this->redirectToRoute('app_recette');
        }

        return $this->render('pages/recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form->createView(),
        ]);
    }
}