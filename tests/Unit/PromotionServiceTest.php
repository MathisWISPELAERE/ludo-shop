<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\PromotionService;
use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testReturnsNormalPriceWhenNoPromotion(): void
    {
        $product = $this->createProduct(50.00);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testReturnsPromoPriceDuringPeriod()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $hier = $aujourdhui->modify('-1 day');
        $demain = $aujourdhui->modify('+1 day');

        $product->setPromoStartsAt($hier);
        $product->setPromoEndsAt($demain);

        $this->assertSame(40.00, $this->service->getCurrentPrice($product));
        $this->assertTrue($this->service->isOnPromotion($product));
    }

    public function testReturnsNormalPriceBeforePromotionPeriod()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $demain = $aujourdhui->modify('+1 day');
        $ademain = $aujourdhui->modify('+2 day');

        $product->setPromoStartsAt($demain);
        $product->setPromoEndsAt($ademain);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testReturnsNormalPriceAfterPromotionPeriod()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $ahier = $aujourdhui->modify('-2 day');
        $hier = $aujourdhui->modify('-1 day');

        $product->setPromoStartsAt($ahier);
        $product->setPromoEndsAt($ahier);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testPromoPriceEqualToNormalIsNotActive()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(50.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $hier = $aujourdhui->modify('-1 day');
        $demain = $aujourdhui->modify('+1 day');

        $product->setPromoStartsAt($hier);
        $product->setPromoEndsAt($demain);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testPromoPriceGreaterThanNormalIsNotActive()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(60.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $hier = $aujourdhui->modify('-1 day');
        $demain = $aujourdhui->modify('+1 day');

        $product->setPromoStartsAt($hier);
        $product->setPromoEndsAt($demain);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testInvertedDatesAreNotActive()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $hier = $aujourdhui->modify('-1 day');
        $demain = $aujourdhui->modify('+1 day');

        $product->setPromoStartsAt($demain);
        $product->setPromoEndsAt($hier);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testBoundaryStartIsIncluded()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $demain = $aujourdhui->modify('+1 day');

        $product->setPromoStartsAt($aujourdhui);
        $product->setPromoEndsAt($demain);

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $aujourdhui));
        $this->assertTrue($this->service->isOnPromotion($product, $aujourdhui));
    }

    public function testBoundaryEndIsIncluded()
    {
        $product = $this->createProduct(50.00);
        $product->setPromoPrice(40.00);
        $aujourdhui = new \DateTimeImmutable('now');

        $hier = $aujourdhui->modify('-1 day');

        $product->setPromoStartsAt($hier);
        $product->setPromoEndsAt($aujourdhui);

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $aujourdhui));
        $this->assertTrue($this->service->isOnPromotion($product, $aujourdhui));
    }

    private function createProduct(float $price, ?float $promoPrice = null): Product
    {
        $product = new Product();
        $product->setName('Test Product')->setPrice($price);

        if (null !== $promoPrice) {
            $product->setPromoPrice($promoPrice);
            $product->setPromoStartsAt(new \DateTimeImmutable('2026-08-01 00:00:00'));
            $product->setPromoEndsAt(new \DateTimeImmutable('2026-08-31 23:59:59'));
        }

        return $product;
    }
}
