<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/showtime', name: 'showtime')]
    public function index(): Response
    {
        $user = $this->getUser();


        $role = in_array('ROLE_ADMIN', $user->getRoles()) ? 'admin' : 'user';

        return $this->render('home/index.html.twig', [
            'role' => $role,
        ]);
    }
}
