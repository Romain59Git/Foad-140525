<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Attribute\IsGranted;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\IngredientRepository;
use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\RecetteRepository;
use App\Entity\Recette;
use App\Form\RecetteType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_home')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository, IngredientRepository $ingredientRepository, RecetteRepository $recetteRepository): Response
    {
        $stats = [
            'users' => $userRepository->count([]),
            'ingredients' => $ingredientRepository->count([]),
            'recettes' => $recetteRepository->count([])
        ];
        
        return $this->render('pages/admin/index.html.twig', [
            'stats' => $stats
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $userRepository->createQueryBuilder('u');
        
        // Recherche
        if ($request->query->has('search')) {
            $search = $request->query->get('search');
            $query->where('u.email LIKE :search OR u.pseudo LIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }
        
        // Tri
        $sort = $request->query->get('sort', 'u.id');
        $direction = $request->query->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/admin/users.html.twig', [
            'users' => $users,
            'search' => $request->query->get('search', ''),
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(UserRepository $userRepository, int $id, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = $userRepository->find($id);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPlainPassword()) {
                $hashedPassword = $hasher->hashPassword($user, $user->getPlainPassword());
                $user->setPassword($hashedPassword);
            }
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }
        return $this->render('pages/admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/ingredients', name: 'admin_ingredients')]
    #[IsGranted('ROLE_ADMIN')]
    public function ingredients(IngredientRepository $ingredientRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $ingredientRepository->createQueryBuilder('i');
        
        // Recherche
        if ($request->query->has('search')) {
            $search = $request->query->get('search');
            $query->where('i.name LIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }
        
        // Tri
        $sort = $request->query->get('sort', 'i.id');
        $direction = $request->query->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $ingredients = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/admin/ingredients.html.twig', [
            'ingredients' => $ingredients,
            'search' => $request->query->get('search', ''),
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/admin/ingredient/edit/{id}', name: 'admin_ingredient_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editIngredient(IngredientRepository $ingredientRepository, int $id, Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = $ingredientRepository->find($id);
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ingredient);
            $manager->flush();
            $this->addFlash('success', 'Ingrédient modifié avec succès');
            return $this->redirectToRoute('admin_ingredients');
        }
        return $this->render('pages/admin/edit_ingredient.html.twig', [
            'form' => $form->createView(),
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/admin/ingredient/delete/{id}', name: 'admin_ingredient_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteIngredient(Ingredient $ingredient, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete_ingredient_' . $ingredient->getId(), $request->request->get('_token'))) {
            $manager->remove($ingredient);
            $manager->flush();
            $this->addFlash('success', 'Ingrédient supprimé avec succès');
        }
        return $this->redirectToRoute('admin_ingredients');
    }

    #[Route('/admin/recettes', name: 'admin_recettes')]
    #[IsGranted('ROLE_ADMIN')]
    public function recettes(RecetteRepository $recetteRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $recetteRepository->createQueryBuilder('r');
        
        // Recherche
        if ($request->query->has('search')) {
            $search = $request->query->get('search');
            $query->where('r.name LIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }
        
        // Tri
        $sort = $request->query->get('sort', 'r.id');
        $direction = $request->query->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $recettes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/admin/recettes.html.twig', [
            'recettes' => $recettes,
            'search' => $request->query->get('search', ''),
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/admin/recette/edit/{id}', name: 'admin_recette_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editRecette(RecetteRepository $recetteRepository, int $id, Request $request, EntityManagerInterface $manager): Response
    {
        $recette = $recetteRepository->find($id);
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($recette);
            $manager->flush();
            $this->addFlash('success', 'Recette modifiée avec succès');
            return $this->redirectToRoute('admin_recettes');
        }
        return $this->render('pages/admin/edit_recette.html.twig', [
            'form' => $form->createView(),
            'recette' => $recette,
        ]);
    }

    #[Route('/admin/recette/delete/{id}', name: 'admin_recette_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteRecette(Recette $recette, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete_recette_' . $recette->getId(), $request->request->get('_token'))) {
            $manager->remove($recette);
            $manager->flush();
            $this->addFlash('success', 'Recette supprimée avec succès');
        }
        return $this->redirectToRoute('admin_recettes');
    }

    #[Route('/admin/user/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newUser(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $hasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users');
        }
        return $this->render('pages/admin/new_user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/ingredient/new', name: 'admin_ingredient_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newIngredient(Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ingredient);
            $manager->flush();
            $this->addFlash('success', 'Ingrédient créé avec succès');
            return $this->redirectToRoute('admin_ingredients');
        }
        return $this->render('pages/admin/new_ingredient.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/recette/new', name: 'admin_recette_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newRecette(Request $request, EntityManagerInterface $manager): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($recette);
            $manager->flush();
            $this->addFlash('success', 'Recette créée avec succès');
            return $this->redirectToRoute('admin_recettes');
        }
        return $this->render('pages/admin/new_recette.html.twig', [
            'form' => $form->createView()
        ]);
    }
} 