<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Ingredient;
use App\Form\IngredientTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use App\Form\RecetteType;
use App\Entity\Recette;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home', methods: ['GET'])]

    public function index(): Response
{
    return $this->render('pages/home.html.twig');
}
}

?>