<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

/**
 * Covers UC-21 / RG-32: stock alert subscription, single-use email,
 * only on out-of-stock products.
 */
class StockAlertTest extends FunctionalTestCase
{
    private Product $catan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);
    }

    public function testSubscribeToStockAlertOnOOSProduct(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('GET', '/products/'.$this->catan->getId());
        $this->assertSelectorTextContains('body', "M'avertir au retour en stock");

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'notifié');
    }

    public function testSubscribeRejectedWhenProductInStock(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'actuellement en stock');
    }

    public function testDuplicateSubscribeRejected(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'déjà une alerte');
    }

    public function testAlertShowsOnProductPage(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->client->request('GET', '/products/'.$this->catan->getId());
        $this->assertSelectorTextContains('body', 'Alerte active');
    }

    private function setStock(Product $product, int $stock): void
    {
        $product->setStock($stock);
        $this->entityManager()->flush();
    }
}
