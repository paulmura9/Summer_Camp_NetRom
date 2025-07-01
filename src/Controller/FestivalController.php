<?php

namespace App\Controller;

use App\Entity\Festival;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
}
