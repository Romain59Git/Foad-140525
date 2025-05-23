<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(UserRepository $userRepository, int $id, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $userRepository->find($id);
        if (!$this->getUser() || $this->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Les informations de votre compte ont bien été modifiées');
            return $this->redirectToRoute('app_recette');
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mdp/{id}', name: 'user_edit_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editPassword(UserRepository $userRepository, int $id, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = $userRepository->find($id);
        if (!$this->getUser() || $this->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            if ($hasher->isPasswordValid($user, $oldPassword)) {
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPlainPassword($plainPassword);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Le mot de passe a bien été modifié.');
                return $this->redirectToRoute('app_recette');
            } else {
                $this->addFlash('warning', 'Le mot de passe est incorrect');
            }
        }
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 