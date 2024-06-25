<?php

/**
 * Comment repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PostRepositoryTest.
 */
class PostRepositoryTest extends KernelTestCase
{
    /**
     * @var PostRepository|null post repository instance
     */
    private ?PostRepository $postRepository;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $container = self::getContainer();
        $this->postRepository = $container->get(PostRepository::class);
    }

    /**
     * Test counting posts by category.
     */
    public function testCountByCategory(): void
    {
        $category = $this->createCategory('test category');
        $count = $this->postRepository->countByCategory($category);

        $this->assertIsInt($count);
    }

    /**
     * Create a new category entity for testing purposes.
     *
     * @param string $title the title of the category
     *
     * @return Category the created category entity
     */
    protected function createCategory(string $title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
}
