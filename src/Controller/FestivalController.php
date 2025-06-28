<?php

namespace App\Controller;

use App\Entity\Festival;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FestivalController extends AbstractController
{
    #[Route('/festivals', name: 'festivals_list', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $festivals = $em->getRepository(Festival::class)->findAll();

        $data = array_map(fn($festival) => [
            'id' => $festival->getId(),
            'name' => $festival->getName()
        ], $festivals);

        return $this->render('festival/index.html.twig', [
            'ids' => $data
        ]);
    }
}
