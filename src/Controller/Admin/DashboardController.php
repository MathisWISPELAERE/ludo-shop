<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_admin', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_products');
    }
}
