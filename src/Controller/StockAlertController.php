<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Service\StockAlertService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StockAlertController extends AbstractController
{
    #[Route('/products/{id}/stock-alert', name: 'app_stock_alert', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function subscribe(Product $product, StockAlertService $stockAlertService): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $stockAlertService->request($user, $product);
            $this->addFlash('success', 'Vous serez notifié(e) quand ce produit sera de retour en stock.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }
}
