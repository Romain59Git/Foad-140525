<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}