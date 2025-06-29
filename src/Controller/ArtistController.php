<?php

namespace App\Controller;

use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistController extends AbstractController
{
    #[Route('/artists', name: 'artists_list', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $artists = $em->getRepository(Artist::class)->findAll();

        $data = array_map(fn($a) => [
            'id' => $a->getId(),
            'name' => $a->getName(),
            'genre' => $a->getMusicalGenre(),
        ], $artists);

        return $this->render('artist/index.html.twig', [
            'artists' => $data
        ]);
    }

    #[Route('/artist/{id}', name: 'artist_view', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function view(EntityManagerInterface $em, int $id): Response
    {
        $artist = $em->getRepository(Artist::class)->find($id);

        if (!$artist) {
            throw $this->createNotFoundException('Artist not found');
        }

        return $this->render('artist/view.html.twig', [
            'artist' => $artist
        ]);
    }
}
