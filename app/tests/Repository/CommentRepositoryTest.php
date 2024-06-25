<?php

/**
 * Comment repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CommentRepositoryTest.
 */
class CommentRepositoryTest extends KernelTestCase
{
    /**
     * @var CommentRepository|null comment repository instance
     */
    private ?CommentRepository $commentRepository;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $container = self::getContainer();
        $this->commentRepository = $container->get(CommentRepository::class);
    }

    /**
     * Test finding comments by post.
     */
    public function testFindByPost(): void
    {
        $post = $this->createPost('test post');
        $commentsQueryBuilder = $this->commentRepository->findByPost($post);

        $this->assertInstanceOf(QueryBuilder::class, $commentsQueryBuilder);
    }

    /**
     * Helper function to create a new post entity for testing purposes.
     *
     * @param string $title the title of the post
     *
     * @return Post the created post entity
     */
    protected function createPost(string $title): Post
    {
        $expectedPost = new Post();
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        // Persist the post entity
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($expectedPost);
        $entityManager->flush();

        return $expectedPost;
    }

    /**
     * Helper function to create a category for testing purposes.
     *
     * @return Category the created category entity
     */
    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
}
