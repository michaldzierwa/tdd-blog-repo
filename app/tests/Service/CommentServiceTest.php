<?php
/**
 * Comment Service Tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\CommentService;
use App\Service\PostService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class Comment Service Test.
 */
class CommentServiceTest extends KernelTestCase
{
    /**
     * @var CommentRepository|null comment repository instance
     */
    private ?CommentRepository $commentRepository;

    /**
     * @var CommentRepository|null comment repository instance
     */
    private ?PostRepository $postRepository;

    /**
     * @var CommentRepository|null comment repository instance
     */
    private ?CommentService $commentService;

    /**
     * Set up tests.
     *
     * @return void Void
     */
    public function setUp(): void
    {
        $container = self::getContainer();
        $this->commentRepository = $container->get(CommentRepository::class);
        $this->postRepository = $container->get(PostRepository::class);
        $this->postService = $container->get(PostService::class);
        $this->commentService = new CommentService(
            $this->commentRepository,
            $container->get(PaginatorInterface::class),
            $this->postRepository
        );
    }

    /**
     * Test saving a comment.
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
        $comment = new Comment();
        $comment->setContent('Test Comment');
        $comment->setPost($expectedPost);
        $comment->setNick('test nick');
        $comment->setEmail('test@mail.com');
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->commentRepository->save($comment);
        $actualComment = $this->commentRepository->findOneById($comment->getId());

        $this->assertEquals($comment, $actualComment);
    }

    /**
     * Test retrieving a paginated list of comments.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;

        $expectedPost = new Post();
        $expectedPost->setTitle('test post');
        $expectedPost->setContent('test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory('test category title'));
        $this->postService->save($expectedPost);

        $counter = 0;
        while ($counter < $dataSetSize) {
            $comment = new Comment();
            $comment->setContent('Test Comment'.$counter);
            $comment->setPost($expectedPost);
            $comment->setNick('test nick');
            $comment->setEmail('test@mail.com');
            $comment->setCreatedAt(new \DateTimeImmutable());
            $this->commentRepository->save($comment);
            ++$counter;
        }

        // when
        $pagination = $this->commentService->getPaginatedList($page, $expectedPost);

        // then
        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertEquals(3, $pagination->count());
    }

    /**
     * Test deleting a comment.
     */
    public function testDelete(): void
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
        $comment = new Comment();
        $comment->setContent('Test Comment');
        $comment->setPost($expectedPost);
        $comment->setNick('test nick');
        $comment->setEmail('test@mail.com');
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->commentRepository->save($comment);
        $commentId = $comment->getId();

        $this->commentService->delete($comment);
        $deletedComment = $this->commentRepository->findOneById($commentId);

        $this->assertNull($deletedComment);
    }

    /**
     * Test finding comments by post.
     */
    public function testFindByPost(): void
    {
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
        $counter = 0;
        while ($counter < 3) {
            $comment = new Comment();
            $comment->setContent('Test Comment'.$counter);
            $comment->setPost($expectedPost);
            $comment->setNick('test nick');
            $comment->setEmail('test@mail.com');
            $comment->setCreatedAt(new \DateTimeImmutable());
            $this->commentRepository->save($comment);
            ++$counter;
        }

        $comments = $this->commentService->findByPost($expectedPost);

        $this->assertCount(3, $comments);
        foreach ($comments as $comment) {
            $this->assertInstanceOf(Comment::class, $comment);
            $this->assertEquals($expectedPost->getId(), $comment->getPost()->getId());
        }
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
