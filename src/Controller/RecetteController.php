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
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class RecetteController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'app_recette', methods:['GET'])]
    public function index(RecetteRepository $recetteRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $recettes = $paginator->paginate(
            $recetteRepository->findByUser($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('/pages/recette/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/recette/new', name: 'app_recette_new', methods:['GET','POST'])]
    public function new(Request $request, RecetteRepository $recetteRepository): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteTypeForm::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette->setUser($this->getUser());
            $recetteRepository->save($recette, true);

            $this->addFlash('success', 'La recette a été créée avec succès.');
            return $this->redirectToRoute('app_recette');
        }

        return $this->render('pages/recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/recette/edit/{id}', name: 'app_recette_edit', methods:['GET','POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(RecetteTypeForm::class, $recette, [
            'submit_label' => 'Modifier la recette'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'La recette a été modifiée avec succès.');
            return $this->redirectToRoute('app_recette');
        }

        return $this->render('pages/recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/recette/delete/{id}', name: 'app_recette_delete', methods:['DELETE'])]
    public function delete(Request $request, Recette $recette, RecetteRepository $recetteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->request->get('_token'))) {
            $recetteRepository->remove($recette, true);

            $this->addFlash('success', 'La recette a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_recette');
    }
}