<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Service\CartService;

class CartTest extends FunctionalTestCase
{
    public function testAddProductToCart(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    // public function testCartQuantityIsUpdated(): void
    // {
    //     $this->login('client@example.com');

    //     $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
    //     $this->assertNotNull($product);

    //     $this->client->request('POST', '/cart/add/'.$product->getId(), [
    //         'quantity' => 1,
    //     ]);

    //     $this->client->request('POST', '/cart/items/'.$product->getId().'/update', [
    //         'quantity' => 3,
    //     ]);

    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertSelectorTextContains('input', '3');
    // }

    // public function testRemoveProductFromCart(): void 
    // {
    //     $this->login('client@example.com');

    //     $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
    //     $this->assertNotNull($product);

    //     $this->client->request('POST', '/cart/add/'.$product->getId(), [
    //         'quantity' => 1,
    //     ]);

    //     $this->client->request('POST', '/cart/items/'.$product->getId().'/remove');

    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertSelectorTextContains('body', 'Votre panier est vide.');
    // }
    // Test qui passe mais faux ?

    public function testCartQuantityIsUpdated(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $item = $this->repository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        $this->assertNotNull($item);

        $this->client->request('POST', '/cart/items/'.$item->getId().'/update', [
            'quantity' => 3,
        ]);

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $input = $crawler->filter('input.js-cart-quantity[data-url*="/cart/items/'.$item->getId().'/update-json"]');
        $this->assertSame('3', $input->attr('value'));
    }

    public function testRemoveProductFromCart(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $item = $this->repository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        $this->assertNotNull($item);

        $this->client->request('POST', '/cart/items/'.$item->getId().'/remove');

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Votre panier est vide.');
    }

    public function testCartShowsCorrectTotal(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 2);

        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#cart-total');
    }
}