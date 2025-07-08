<?php

namespace App\Controller;

use App\Entity\Festival;
use App\Entity\FestivalArtist;
use App\Repository\ArtistRepository;
use App\Repository\FestivalArtistRepository;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\FestivalForm;

final class FestivalController extends AbstractController
{
    #[Route('/festivals', name: 'festivals_list', methods: ['GET'])]
    public function index(
        FestivalRepository $festivalRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $query = $festivalRepository
            ->createQueryBuilder('f')
            ->orderBy('f.start_date', 'ASC')
            ->getQuery();

        $festivals = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $this->ItemsPerPage ?? 12
        );

        return $this->render('festival/index.html.twig', [
            'festivals' => $festivals,
        ]);
    }

    #[Route('/festival/{id}', name: 'festival_view', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view(EntityManagerInterface $em, int $id): Response
    {
        $festival = $em->getRepository(Festival::class)->find($id);

        if (!$festival) {
            throw $this->createNotFoundException('Festival not found');
        }

        return $this->render('festival/view.html.twig', [
            'festival' => $festival
        ]);
    }

    #[Route('/festival/delete/{id}', name: 'festival_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $festival = $em->getRepository(Festival::class)->find($id);

        if (!$festival) {
            $this->addFlash('error', 'Not found.');
            return $this->redirectToRoute('festivals_list');
        }

        foreach ($festival->getFestivalArtists() as $relation) {
            $em->remove($relation);
        }

        $em->remove($festival);
        $em->flush();

        $this->addFlash('success', 'Festival deleted successfully.');
        return $this->redirectToRoute('festivals_list');
    }

    #[Route('/festival/create', name: 'festival_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $festival = new Festival();
        $festival->setStartDate(new \DateTime());
        $festival->setEndDate(new \DateTime());
        $form = $this->createForm(FestivalForm::class, $festival);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($festival);
            $em->flush();

            $this->addFlash('success', 'Festival creat.');
            return $this->redirectToRoute('festivals_list');
        }

        return $this->render('festival/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/festival/edit/{id}', name: 'festival_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Festival $festival, EntityManagerInterface $em, ArtistRepository $artistRepo): Response
    {
        $linkedArtistIds = $festival->getFestivalArtists()
            ->map(fn($fa) => $fa->getArtist()?->getId())
            ->filter(fn($id) => $id !== null)
            ->toArray();

        $form = $this->createForm(FestivalForm::class, $festival, [
            'include_artist' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artistIdsToRemove = $request->request->all('remove_artists') ?? [];
            $artistIdsToAdd = $request->request->all('linked_artists') ?? [];

            foreach ($festival->getFestivalArtists() as $fa) {
                if (in_array($fa->getArtist()->getId(), $artistIdsToRemove)) {
                    $em->remove($fa);
                }
            }

            foreach ($artistIdsToAdd as $artistId) {
                if (!in_array((int)$artistId, $linkedArtistIds) && !in_array($artistId, $artistIdsToRemove)) {
                    $artist = $artistRepo->find($artistId);
                    if ($artist) {
                        $newLink = new \App\Entity\FestivalArtist();
                        $newLink->setFestival($festival);
                        $newLink->setArtist($artist);
                        $em->persist($newLink);
                    }
                }
            }

            $em->flush();

            $this->addFlash('success', 'Festival updated');
            return $this->redirectToRoute('festival_view', ['id' => $festival->getId()]);
        }

        $allArtists = $artistRepo->findAll();
        $availableArtists = array_filter($allArtists, fn($artist) => !in_array($artist->getId(), $linkedArtistIds));

        return $this->render('festival/edit.html.twig', [
            'form' => $form->createView(),
            'festival' => $festival,
            'linked_artists' => $festival->getFestivalArtists(),
            'available_artists' => $availableArtists
        ]);
    }


    #[Route('/festival/{festival_id}/add-artist/{artist_id}', name: 'festival_add_artist', methods: ['POST'])]
    public function addArtist(
        int $festival_id,
        int $artist_id,
        EntityManagerInterface $em,
        FestivalRepository $festivalRepo,
        ArtistRepository $artistRepo
    ): Response {
        $festival = $festivalRepo->find($festival_id);
        $artist = $artistRepo->find($artist_id);

        if ($festival && $artist) {
            $exists = $festival->getFestivalArtists()->exists(fn($i, $fa) => $fa->getArtist()->getId() === $artist_id);
            if (!$exists) {
                $fa = new FestivalArtist();
                $fa->setFestival($festival);
                $fa->setArtist($artist);
                $em->persist($fa);
                $em->flush();
            }
        }

        return $this->redirectToRoute('festival_edit', ['id' => $festival_id]);
    }

    #[Route('/festivals/search', name: 'festival_list', methods: ['GET'])]
    public function list(Request $request, FestivalRepository $festivalRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('q');

        $qb = $festivalRepository->createQueryBuilder('f');

        if ($searchTerm) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(f.name)', ':search'),
                    $qb->expr()->like('LOWER(f.location)', ':search')
                )
            )
                ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        $qb->orderBy('f.start_date', 'ASC');

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('festival/index.html.twig', [
            'festivals' => $pagination
        ]);
    }

}
