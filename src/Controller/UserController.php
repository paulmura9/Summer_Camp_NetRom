<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Form\UserDetailsForm;
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
            10
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
            $this->addFlash('success', 'Datele au fost actualizate.');
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
            $this->addFlash('error', 'Userul nu a fost găsit.');
            return $this->redirectToRoute('users_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Userul a fost șters cu succes.');
        return $this->redirectToRoute('users_list');
    }

    #[Route('/user/edit/{id}', name: 'user_edit', methods: ['POST'])]
    public function edit(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        $details = $user->getDetails();

        $details->setName($request->request->get('name'));
        $details->setAge((int) $request->request->get('age'));

        $em->flush();

        $this->addFlash('success', 'Profilul a fost actualizat.');
        return $this->redirectToRoute('user_view', ['id' => $user->getId()]);
    }
}
