<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    public function testMultipleItems(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $product2 = new Product();
        $product2->setName('CodeName');
        $product2->setPrice(20.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $item2 = new CartItem($product2);
        $item2->setQuantity(1);
        $item2->setUnitPrice(20.00);
        $cart->addItem($item2);

        $this->assertSame(45.00, $this->service->getTotal($cart));
    }

    public function testQuantityMultiplier(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(3);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(75.00, $this->service->getTotal($cart));
    }

    private function createProductWithPromo(float $price = 50.00, float $promoPrice = 35.00, int $stock = 5): Product
    {
        $product = new Product();
        $product->setName('Catan');
        $product->setPrice($price);
        $product->setPromoPrice($promoPrice);
        $product->setStock($stock);
        $product->setPromoStartsAt(new \DateTimeImmutable('2026-01-01T00:00:00'));
        $product->setPromoEndsAt(new \DateTimeImmutable('2099-01-01T00:00:00'));

        return $product;
    }

    public function testPromotionalPriceIsUsed(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);
        $product = $this->createProductWithPromo();

        $this->service->addProduct($cart, $product, 2);

        $this->assertSame(70.00, $this->service->getTotal($cart));
    }
}
