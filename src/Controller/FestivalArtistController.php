<?php

namespace App\Controller;

use App\Entity\FestivalArtist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FestivalArtistController extends AbstractController
{
    #[Route('/festival/artist', name: 'festival_artist_list', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $connections = $em->getRepository(FestivalArtist::class)->findAll();

        $data = array_map(function ($conn) {
            return [
                'festival' => [
                    'id' => $conn->getFestival()?->getId(),
                    'name' => $conn->getFestival()?->getName(),
                ],
                'artist' => [
                    'id' => $conn->getArtist()?->getId(),
                    'name' => $conn->getArtist()?->getName(),
                ],
            ];
        }, $connections);

        return $this->render('festival_artist/index.html.twig', [
            'connections' => $data,
        ]);
    }
}
