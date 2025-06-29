<?php

namespace App\Controller;

use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseController extends AbstractController
{
    #[Route('/purchases', name: 'purchase_list', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $purchases = $em->getRepository(Purchase::class)->findAll();

        $data = array_map(fn($p) => [
            'id' => $p->getId(),
            'user' => [
                'id' => $p->getUser()?->getId(),
                'email' => $p->getUser()?->getEmail()
            ],
            'festival' => [
                'id' => $p->getFestival()?->getId(),
                'name' => $p->getFestival()?->getName()
            ],
        ], $purchases);

        return $this->render('purchase/index.html.twig', [
            'purchases' => $data
        ]);
    }
}
