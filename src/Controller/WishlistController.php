<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/wishlist')]
class WishlistController extends AbstractController
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route('', name: 'app_wishlist', methods: ['GET'])]
    public function index(): Response
    {
        $items = $this->wishlistService->findByUser($this->getUserOrThrow());

        return $this->render('wishlist/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/add/{id}', name: 'app_wishlist_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Product $product): Response
    {
        try {
            $this->wishlistService->add($this->getUserOrThrow(), $product);
            $this->addFlash('success', 'Produit ajouté à votre liste de souhaits.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('app_catalog');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/remove/{id}', name: 'app_wishlist_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(Product $product): Response
    {
        try {
            $this->wishlistService->remove($this->getUserOrThrow(), $product);
            $this->addFlash('success', 'Produit retiré de votre liste de souhaits.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $request = $this->requestStack->getCurrentRequest();
        $referer = $request?->headers->get('referer');
        if ($referer && str_contains($referer, '/wishlist')) {
            return $this->redirectToRoute('app_wishlist');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    private function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User not authenticated.');
        }

        return $user;
    }
}
