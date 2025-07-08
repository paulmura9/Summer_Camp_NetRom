<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistForm;
use App\Repository\ArtistRepository;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        EntityManagerInterface $em,
        FestivalRepository $festivalRepo,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ArtistForm::class, $artist);
        $form->handleRequest($request);

        $linkedFestivalIds = $artist->getFestivalArtists()
            ->map(fn($fa) => $fa->getFestival()?->getId())
            ->filter(fn($id) => $id !== null)
            ->toArray();

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('artist_images_directory'),
                        $newFilename
                    );
                    $artist->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Failed to upload image: ' . $e->getMessage());
                }
            }

            $festivalIdsToRemove = $request->request->all('remove_festivals') ?? [];
            $festivalIdsToAdd = $request->request->all('linked_festivals') ?? [];

            foreach ($artist->getFestivalArtists() as $fa) {
                if (in_array($fa->getFestival()->getId(), $festivalIdsToRemove)) {
                    $em->remove($fa);
                }
            }

            foreach ($festivalIdsToAdd as $festivalId) {
                if (!in_array((int)$festivalId, $linkedFestivalIds) && !in_array($festivalId, $festivalIdsToRemove)) {
                    $festival = $festivalRepo->find($festivalId);
                    if ($festival) {
                        $newLink = new \App\Entity\FestivalArtist();
                        $newLink->setArtist($artist);
                        $newLink->setFestival($festival);
                        $em->persist($newLink);
                    }
                }
            }

            $em->flush();

            $this->addFlash('success', 'Artist updated successfully.');
            return $this->redirectToRoute('artist_view', ['id' => $artist->getId()]);
        }

        $allFestivals = $festivalRepo->findAll();
        $availableFestivals = array_filter($allFestivals, fn($festival) => !in_array($festival->getId(), $linkedFestivalIds));

        return $this->render('artist/edit.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist,
            'linked_festivals' => array_map(fn($fa) => $fa->getFestival(), $artist->getFestivalArtists()->toArray()),
            'available_festivals' => $availableFestivals,
        ]);
    }


    #[Route('/artists/search', name: 'artist_list', methods: ['GET'])]
    public function list(Request $request, ArtistRepository $artistRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('q');

        $qb = $artistRepository->createQueryBuilder('a');

        if ($searchTerm) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(a.name)', ':search'),
                    $qb->expr()->like('LOWER(a.musical_genre)', ':search')
                )
            )
                ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        $qb->orderBy('a.name', 'ASC');

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('artist/index.html.twig', [
            'artists' => $pagination
        ]);
    }
}
