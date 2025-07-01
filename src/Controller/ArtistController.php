<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistController extends AbstractController
{
    #[Route('/artists', name: 'artists_list', methods: ['GET'])]
    public function index(
        ArtistRepository $artistRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $artistRepository
            ->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->getQuery();

        $artists = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('artist/index.html.twig', [
            'artists' => $artists
        ]);
    }

    #[Route('/artist/{id}', name: 'artist_view', requirements: ['id' => '\d+'], methods: ['GET'])]
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

    #[Route('/artist/delete/{id}', name: 'artist_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $artist = $em->getRepository(Artist::class)->find($id);

        if (!$artist) {
            $this->addFlash('error', 'Artistul nu a fost găsit.');
            return $this->redirectToRoute('artists_list');
        }

        $em->remove($artist);
        $em->flush();

        $this->addFlash('success', 'Artistul a fost șters cu succes.');
        return $this->redirectToRoute('artists_list');
    }
}
