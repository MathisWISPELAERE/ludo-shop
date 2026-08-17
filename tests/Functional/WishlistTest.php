<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

/**
 * Covers UC-20 / RG-31: wishlist add/remove, no duplicates,
 * mature product blocked for minors.
 */
class WishlistTest extends FunctionalTestCase
{
    private Product $catan;
    private Product $limite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);

        $this->limite = $this->repository(Product::class)->findOneBy(['reference' => 'LIM-001']);
        $this->assertNotNull($this->limite);
    }

    public function testAddToWishlist(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'ajouté à votre liste de souhaits');

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testRemoveFromWishlist(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/remove/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Catan');
    }

    public function testDuplicateAddShowsFlash(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'déjà dans votre liste de souhaits');
    }

    public function testMatureProductBlockedForMinor(): void
    {
        $this->login('minor@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->limite->getId());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'majeur');

        $this->client->request('GET', '/wishlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Limite Limite');
    }

    public function testWishlistRequiresLogin(): void
    {
        $this->client->request('GET', '/wishlist');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location'));
    }
}
