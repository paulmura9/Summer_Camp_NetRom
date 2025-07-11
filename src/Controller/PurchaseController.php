<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\FestivalRepository;

final class PurchaseController extends AbstractController
{
    #[Route('/purchases', name: 'purchase_list', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getUser();

        $query = $em->createQueryBuilder()
            ->select('p', 'f')
            ->from(Purchase::class, 'p')
            ->leftJoin('p.festival', 'f')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.start_date', 'ASC')
            ->getQuery();

        $purchases = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchases
        ]);
    }

    #[Route('/purchase/create/{id}', name: 'purchase_create', methods: ['POST'])]
    public function create(
        int $id,
        FestivalRepository $festivalRepository,
        Security $security,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $security->getUser();
        $festival = $festivalRepository->find($id);

        if (!$festival || !$user) {
            throw $this->createNotFoundException('Festival or User not found.');
        }

        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1 || $quantity > $festival->getCapacity()) {
            $this->addFlash('warning', 'Not enough tickets available.');
            return $this->redirectToRoute('festival_view', ['id' => $festival->getId()]);
        }

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setFestival($festival);
        $purchase->setQuantity($quantity);
        $purchase->setCreatedAt(new \DateTime());

        $festival->setCapacity($festival->getCapacity() - $quantity);

        $em->persist($purchase);
        $em->flush();

        $this->addFlash('success', 'You bought ' . $quantity . ' ticket(s) successfully!');

        return $this->redirectToRoute('purchase_list', ['id' => $festival->getId()]);
    }

    #[Route('/all/purchases', name: 'all_purchases_list')]
    public function allPurchases(
        PaginatorInterface $paginator,
        Request $request,
        PurchaseRepository $purchaseRepo,
        Security $security
    ): Response {
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Access denied. Only admins can view this page.');
        }

        $query = $purchaseRepo->createQueryBuilder('p')
            ->join('p.user', 'u')->addSelect('u')
            ->join('p.festival', 'f')->addSelect('f')
            ->orderBy('p.id', 'DESC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('purchase/purchases.html.twig', [
            'purchases' => $pagination,
        ]);
    }
    #[Route('/purchase/delete/{id}', name: 'purchase_delete', methods: ['GET'])]
    public function deletePurchase(int $id, EntityManagerInterface $em): RedirectResponse
    {
        $purchase = $em->getRepository(Purchase::class)->find($id);

        if (!$purchase) {
            $this->addFlash('error', 'Purchase not found.');
            return $this->redirectToRoute('all_purchases_list');
        }

        $em->remove($purchase);
        $em->flush();

        $this->addFlash('success', 'Purchase deleted successfully.');
        return $this->redirectToRoute('all_purchases_list');
    }

}
