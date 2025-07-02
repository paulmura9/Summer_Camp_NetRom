<?php

namespace App\Controller;

use App\Entity\Festival;
use App\Entity\FestivalArtist;
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
            $this->ItemsPerPage ?? 10
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
            $this->addFlash('error', 'Festivalul nu a fost găsit.');
            return $this->redirectToRoute('festivals_list');
        }

        $em->remove($festival);
        $em->flush();

        $this->addFlash('success', 'Festivalul a fost șters cu succes.');
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
    public function edit(Request $request, Festival $festival, EntityManagerInterface $em): Response
    {
        $linkedArtistIds = $festival->getFestivalArtists()
            ->map(fn($fa) => $fa->getArtist()?->getId())
            ->filter(fn($id) => $id !== null)
            ->toArray();

        $form = $this->createForm(FestivalForm::class, $festival, [
            'include_artist' => true,
            'linked_artists' => $linkedArtistIds,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist = $form->get('artist')->getData();

            if ($artist !== null) {
                $alreadyLinked = $festival->getFestivalArtists()->exists(fn($i, $rel) => $rel->getArtist() === $artist);

                if (!$alreadyLinked) {
                    $festivalArtist = new FestivalArtist();
                    $festivalArtist->setFestival($festival);
                    $festivalArtist->setArtist($artist);
                    $em->persist($festivalArtist);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Festival updated');
            return $this->redirectToRoute('festival_view', [
                'id' => $festival->getId(),
            ]);
        }

        return $this->render('festival/edit.html.twig', [
            'form' => $form->createView(),
            'festival' => $festival,
            'linked_artists' => $festival->getFestivalArtists(),
        ]);
    }

    #[Route('/festival/{festival_id}/remove-artist/{artist_id}', name: 'festival_remove_artist', methods: ['POST'])]
    public function removeArtist(
        int $festival_id,
        int $artist_id,
        EntityManagerInterface $em,
        FestivalRepository $festivalRepo,
        FestivalArtistRepository $faRepo
    ): Response {
        $festival = $festivalRepo->find($festival_id);
        $link = $faRepo->findOneBy([
            'festival' => $festival_id,
            'artist' => $artist_id,
        ]);

        if ($festival && $link) {
            $em->remove($link);
            $em->flush();
        }

        return $this->redirectToRoute('festival_edit', [
            'id' => $festival_id
        ]);
    }

}
