<?php

namespace App\Controller;

use App\Entity\UserDetails;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserDetailsController extends AbstractController
{
    #[Route('/users/details', name: 'user_details_list', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $rawDetails = $em->getRepository(UserDetails::class)->findAll();

        $data = array_map(fn($d) => [
            'user_id' => $d->getUser()->getId(),
            'name' => $d->getName(),
            'age' => $d->getAge()
        ], $rawDetails);

        $details = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user_details/index.html.twig', [
            'details' => $details
        ]);
    }

    #[Route('/user/details/{id}', name: 'user_details_view', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view(EntityManagerInterface $em, int $id): Response
    {
        $details = $em->getRepository(UserDetails::class)->findOneBy(['user' => $id]);

        if (!$details) {
            throw $this->createNotFoundException('UserDetails not found');
        }

        return $this->render('user_details/view.html.twig', [
            'details' => $details,
        ]);
    }

    #[Route('/user/details/delete/{id}', name: 'user_details_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $details = $em->getRepository(UserDetails::class)->findOneBy(['user' => $id]);

        if (!$details) {
            $this->addFlash('error', 'Detaliile utilizatorului nu au fost găsite.');
            return $this->redirectToRoute('user_details_list');
        }

        $em->remove($details);
        $em->flush();

        $this->addFlash('success', 'Detaliile utilizatorului au fost șterse cu succes.');
        return $this->redirectToRoute('user_details_list');
    }
}
