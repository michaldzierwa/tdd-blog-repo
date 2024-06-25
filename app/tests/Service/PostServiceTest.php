<?php
/**
 * Post service tests.
 */

namespace App\Tests\Service;

use App\Dto\PostListInputFiltersDto;
use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PostService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class Post Service Test.
 */
class PostServiceTest extends KernelTestCase
{
    /**
     * @var PostRepository|null post repository instance
     */
    private ?PostRepository $postRepository;

    /**
     * @var PostService|null post service instance
     */
    private ?PostService $postService;

    /**
     * Set up tests.
     *
     * @return void Void
     */
    public function setUp(): void
    {
        $container = self::getContainer();
        $this->postRepository = $container->get(PostRepository::class);
        $this->postService = $container->get(PostService::class);
    }

    /**
     * Test saving a post.
     */
    public function testSave(): void
    {
        // given
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory('test category title'));

        $this->postService->save($expectedPost);
        $actualPost = $this->postRepository->findOneById($expectedPost->getId());

        $this->assertEquals($expectedPost, $actualPost);
    }

    /**
     * Test retrieving a paginated list of posts.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $expectedPost = new Post();
            $expectedPost->setTitle('test post');
            $expectedPost->setContent('test content');
            $expectedPost->setCreatedAt(new \DateTimeImmutable());
            $expectedPost->setUpdatedAt(new \DateTimeImmutable());
            $expectedPost->setCategory($this->createCategory('test category title'));
            $this->postService->save($expectedPost);

            ++$counter;
        }

        // when
        $filters = new PostListInputFiltersDto();
        $result = $this->postService->getPaginatedList($page, $filters);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Test retrieving a paginated list of posts with category filter.
     */
    public function testGetPaginatedListWithCategoryFilter(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 1;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $expectedPost = new Post();
            $expectedPost->setTitle('test post '.$counter);
            $expectedPost->setContent('test content '.$counter);
            $expectedPost->setCreatedAt(new \DateTimeImmutable());
            $expectedPost->setUpdatedAt(new \DateTimeImmutable());
            $category = $this->createCategory('test category '.$counter);
            $expectedPost->setCategory($category);
            $this->postService->save($expectedPost);

            ++$counter;
        }

        // when
        $filters = new PostListInputFiltersDto($category->getId());  // Passing the category filter
        $result = $this->postService->getPaginatedList($page, $filters);

        // then
        $this->assertInstanceOf(PaginationInterface::class, $result);
        $this->assertEquals($expectedResultSize, $result->getTotalItemCount());
    }

    /**
     * Test deleting a post.
     */
    public function testDelete(): void
    {
        $expectedPost = new Post();
        $expectedPost->setTitle('test post');
        $expectedPost->setContent('test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $category = $this->createCategory('test category');
        $expectedPost->setCategory($category);
        $this->postService->save($expectedPost);
        $postId = $expectedPost->getId();

        $this->postService->delete($expectedPost);
        $deletedPost = $this->postRepository->findOneById($postId);

        $this->assertNull($deletedPost);
    }

    /**
     * Test finding a post by its ID.
     */
    public function testFindOneById(): void
    {
        $expectedPost = new Post();
        $expectedPost->setTitle('test post');
        $expectedPost->setContent('test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $category = $this->createCategory('test category');
        $expectedPost->setCategory($category);
        $this->postService->save($expectedPost);

        $actualPost = $this->postService->findOneById($expectedPost->getId());

        $this->assertEquals($expectedPost->getId(), $actualPost->getId());
        $this->assertEquals($expectedPost->getTitle(), $actualPost->getTitle());
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

    /**
     * Create a new user entity for testing purposes.
     *
     * @param array $roles the roles of the user
     *
     * @return User the created user entity
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }
}
