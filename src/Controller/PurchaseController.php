<?php

namespace App\Controller;

use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseController extends AbstractController
{
    #[Route('/purchases', name: 'purchase_list', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $em->createQueryBuilder()
            ->select('p, u, f')
            ->from(Purchase::class, 'p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.festival', 'f')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $purchases = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'useOutputWalkers' => true
            ]
        );

        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchases
        ]);
    }
}
