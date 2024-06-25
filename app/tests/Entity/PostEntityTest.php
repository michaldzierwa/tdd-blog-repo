<?php

/**
 * Post entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

/**
 * Post Entity Test.
 */
class PostEntityTest extends TestCase
{
    /**
     * @return void Void
     */
    public function testCanGetAndSetData(): void
    {
        $post = new Post();

        $reflection = new \ReflectionClass($post);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($post, 1);

        $title = 'test post';
        $post->setTitle($title);

        $content = 'Test content for the post';
        $post->setContent($content);

        $createdAt = new \DateTimeImmutable();
        $post->setCreatedAt($createdAt);

        $updatedAt = new \DateTimeImmutable();
        $post->setUpdatedAt($updatedAt);

        $category = new Category();
        $category->setTitle('Test Category');
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setAccessible(true);
        $categoryIdProperty->setValue($category, 1);
        $post->setCategory($category);

        self::assertSame($title, $post->getTitle());
        self::assertSame($content, $post->getContent());
        self::assertSame($createdAt, $post->getCreatedAt());
        self::assertSame($updatedAt, $post->getUpdatedAt());
        self::assertSame(1, $post->getId());
    }
}
