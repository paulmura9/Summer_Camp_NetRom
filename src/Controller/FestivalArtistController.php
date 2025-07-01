<?php

namespace App\Controller;

use App\Entity\FestivalArtist;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FestivalArtistController extends AbstractController
{
    #[Route('/festival/artist', name: 'festival_artist_list', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $em->createQueryBuilder()
            ->select('fa', 'f', 'a')
            ->from(FestivalArtist::class, 'fa')
            ->leftJoin('fa.festival', 'f')
            ->leftJoin('fa.artist', 'a')
            ->orderBy('f.id', 'ASC')
            ->getQuery();

        $connections = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            ['useOutputWalkers' => true]
        );

        return $this->render('festival_artist/index.html.twig', [
            'connections' => $connections,
        ]);
    }
}
