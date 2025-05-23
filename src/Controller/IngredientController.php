<?php

namespace App\Controller;

use App\Form\IngredientType;
use App\Form\IngredientTypeForm;
use App\Repository\IngredientRepository;
use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorAwarePass;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




final class IngredientController extends AbstractController
{
  /**
     * this function display all ingredients
     * 
     * @param IngredientRepository $IngredientRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     * 
     */

    #[Route('/ingredient', name: 'app_ingredient', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $IngredientRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $IngredientRepository->findByUser($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );
        $totalIngredients = $IngredientRepository->count(['user' => $this->getUser()]);

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
            'totalIngredients' => $totalIngredients,
        ]);
    }
    #[Route('/ingredient/nouveau',name: 'app_new_ingredient', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientTypeForm::class, $ingredient);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            $manager->persist($ingredient);
            $manager->flush();
            $this ->addFlash(
                'success',
                'Votre ingrédient a bien été créé avec succès'
            );
            return $this->redirectToRoute('app_ingredient');
        }
        return $this->render('pages/ingredient/new.html.twig',[
            'form' => $form->createView()
        ]);
    }
    #[Route('/ingredient/edition/{id}', name: 'app_ingredient_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(IngredientRepository $ingredientRepository, int $id, request $request, EntityManagerInterface $manager) : Response
    { 
        $ingredient = $ingredientRepository ->findOneBy(['id' => $id]);
        $form = $this->createForm(IngredientTypeForm::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this ->addFlash(
                'success',
                'Votre ingrédient a bien été modifié avec succès'
            );

            return $this->redirectToRoute('app_ingredient');
        }

        return $this->render('pages/ingredient/edit.html.twig',
        [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ingredient/suppression/{id}', name: 'app_ingredient_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(EntityManagerInterface $manager, int $id, IngredientRepository $ingredientRepository, Request $request): Response
    {
        $ingredient = $ingredientRepository->findOneBy(['id' => $id]);

        if ($ingredient) {
            $submittedToken = $request->request->get('_token');
            if ($this->isCsrfTokenValid('delete' . $ingredient->getId(), $submittedToken)) {
                $manager->remove($ingredient);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre ingrédient a été supprimé avec succès !'
                );
            } else {
                $this->addFlash('danger', 'Token CSRF invalide. Suppression refusée.');
            }
        }

        return $this->redirectToRoute('app_ingredient');
    }
}