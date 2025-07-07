<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistForm;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
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
            12
        );

        return $this->render('artist/index.html.twig', [
            'artists' => $artists
        ]);
    }
    #[Route('/artist/{id}', name: 'artist_view', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $artist = $em->getRepository(Artist::class)->find($id);

        if (!$artist) {
            throw $this->createNotFoundException('Artist not found');
        }

        $form = $this->createForm(ArtistForm::class, $artist);

        return $this->render('artist/view.html.twig', [
            'artist' => $artist,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/artist/delete/{id}', name: 'artist_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $artist = $em->getRepository(Artist::class)->find($id);

        if (!$artist) {
            $this->addFlash('error', 'Artist not found');
            return $this->redirectToRoute('artists_list');
        }

        foreach ($artist->getFestivalArtists() as $relation) {
            $em->remove($relation);
        }

        $em->remove($artist);
        $em->flush();

        $this->addFlash('success', 'Artist deleted successfully.');
        return $this->redirectToRoute('artists_list');
    }

    #[Route('/artist/create', name: 'artist_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistForm::class, $artist);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);
            $em->flush();

            $this->addFlash('success', 'Artist created successfully.');
            return $this->redirectToRoute('artists_list');
        }

        return $this->render('artist/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/artist/edit/{id}', name: 'artist_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Artist $artist,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ArtistForm::class, $artist);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Artist updated successfully.');
            return $this->redirectToRoute('artist_view', ['id' => $artist->getId()]);
        }

        return $this->render('artist/edit.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist,
        ]);
    }
}
