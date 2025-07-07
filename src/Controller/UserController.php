<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Form\UserDetailsForm;
use App\Form\UserEditForm;
use App\Form\UserForm;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC')
            ->getQuery();

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}', name: 'user_view', methods: ['GET', 'POST'])]
    public function view(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $details = $user->getDetails();
        $form = $this->createForm(UserDetailsForm::class, $details);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Update successful');
            return $this->redirectToRoute('user_view', ['id' => $id]);
        }

        return $this->render('user/view.html.twig', [
            'user' => $user,
            'details' => $details,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('users_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User has been deleted.');
        return $this->redirectToRoute('users_list');
    }

    #[Route('/user/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('users_list');
        }

        $details = $user->getDetails();

        $form = $this->createForm(UserEditForm::class, $user, [
            'data' => $user,
        ]);

        $form->get('name')->setData($details->getName());
        $form->get('age')->setData($details->getAge());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $details->setName($form->get('name')->getData());
            $details->setAge($form->get('age')->getData());
            $em->flush();

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('user_view', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    #[Route('/user/update-details/{id}', name: 'user_update_details', methods: ['POST'])]
    public function updateDetails(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $details = $user->getDetails();
        $form = $this->createForm(UserDetailsForm::class, $details);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Updated profile');
        } else {
            $this->addFlash('error', 'Error updating profile');
        }

        return $this->redirectToRoute('user_view', ['id' => $id]);
    }
}
