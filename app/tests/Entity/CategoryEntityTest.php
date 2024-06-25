<?php

/**
 * Category entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Category Entity Test.
 */
class CategoryEntityTest extends TestCase
{
    /**
     * @return void Void
     */
    public function testCanGetAndSetTitle(): void
    {
        $category = new Category();
        $category->setTitle('test category');

        self::assertSame('test category', $category->getTitle());
    }

    /**
     * @return void Void
     */
    public function testCanSetAndGetCreatedAt(): void
    {
        $category = new Category();
        $createdAt = new \DateTimeImmutable();
        $category->setCreatedAt($createdAt);

        self::assertSame($createdAt, $category->getCreatedAt());
    }

    /**
     * @return void Void
     */
    public function testCanSetAndGetUpdatedAt(): void
    {
        $category = new Category();
        $updatedAt = new \DateTimeImmutable();
        $category->setUpdatedAt($updatedAt);

        self::assertSame($updatedAt, $category->getUpdatedAt());
    }

    /**
     * @return void Void
     */
    public function testCanSetAndGetSlug(): void
    {
        $category = new Category();
        $category->setSlug('test-category');

        self::assertSame('test-category', $category->getSlug());
    }
}
