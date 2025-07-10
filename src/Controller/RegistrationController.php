<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {
        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $user->getEmail(),
            ]);
            if ($existing) {
                $this->addFlash('error', 'Email already exists!');
                return $this->redirectToRoute('app_register');
            }

            $plain = $form->get('plainPassword')->getData();
            $confirm = $form->get('confirmPassword')->getData();

            if ($plain !== $confirm) {
                $this->addFlash('error', 'Passwords do not match!');
                return $this->redirectToRoute('app_register');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $plain));

            //$user->setRole($form->get('role')->getData());
            $user->setRole('ROLE_USER');

            $entityManager->persist($user->getDetails());
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
