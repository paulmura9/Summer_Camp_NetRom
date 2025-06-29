<?php

namespace App\Controller;

use App\Entity\UserDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserDetailsController extends AbstractController
{
    #[Route('/users/details', name: 'user_details_list', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $details = $em->getRepository(UserDetails::class)->findAll();

        $data = array_map(fn($d) => [
            'user_id' => $d->getUser()->getId(),
            'name' => $d->getName(),
            'age' => $d->getAge()
        ], $details);

        return $this->render('user_details/index.html.twig', [
            'details' => $data
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
            'details' => $details
        ]);
    }
}
