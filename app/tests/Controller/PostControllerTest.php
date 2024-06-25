<?php
/**
 * Post controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\PostService;
use App\Service\PostServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PostControllerTest.
 */
class PostControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/post';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     *
     * @return void Void
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test route.
     *
     * @return void Void
     */
    public function testIndexRoute(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show post.
     */
    public function testShowPost(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 1;
        $category = new Category();
        $categoryId = new \ReflectionProperty(Category::class, 'id');
        $categoryId->setValue($category, $testCategoryId);
        $category->setTitle('test category');
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('test post');
        $expectedPost->setContent('test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = $this->createMock(PostServiceInterface::class);
        $postService->expects($this->once())
            ->method('findOneById')
            ->with($testPostId)
            ->willReturn($expectedPost);
        static::getContainer()->set(PostServiceInterface::class, $postService);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test show post for a post that does not exist.
     */
    public function testShowPostForPostThatDoesNotExist(): void
    {
        // given
        $expectedStatusCode = 302;
        $testPostId = 1;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testPostId);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test create post route for anonymous user.
     */
    public function testCreatePostRouteAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test create post route for admin user.
     */
    public function testCreatePostRouteAdmin(): void
    {
        // given
        $expectedStatusCode = 200;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test create post.
     */
    public function testCreatePost(): void
    {
        // given
        $expectedStatusCode = 200;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = $this->createMock(PostServiceInterface::class);
        static::getContainer()->set(PostServiceInterface::class, $postService);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $this->httpClient->submitForm('Zapisz', [
            'post' => [
                'title' => $expectedPost->getTitle(),
                'content' => $expectedPost->getContent(),
                'category' => $expectedPost->getCategory()->getId(),
            ],
        ]);

        $this->assertResponseRedirects('/post');
        $this->httpClient->followRedirect();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit post route for admin user.
     */
    public function testEditPostRouteAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = $this->createMock(PostServiceInterface::class);
        $postService->expects($this->once())
            ->method('findOneById')
            ->with($testPostId)
            ->willReturn($expectedPost);
        static::getContainer()->set(PostServiceInterface::class, $postService);

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/edit');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit post route for a post that does not exist.
     */
    public function testEditPostRouteAdminForPostNotFound(): void
    {
        // given
        $expectedStatusCode = 302;
        $notPresentId = 1;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$notPresentId.'/edit');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit post route for anonymous user.
     */
    public function testEditPostRouteAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/edit');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit post.
     */
    public function testEditPost(): void
    {
        // given
        $expectedStatusCode = 302;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $expectedPost = new Post();
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = self::getContainer()->get(PostService::class);
        $postService->save($expectedPost);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/edit');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/edit');
        $this->httpClient->submitForm(
            'Edytuj',
            [
                'post[title]' => 'new title',
                'post[content]' => 'new content',
                'post[category]' => $expectedPost->getCategory()->getId(),
            ],
            'PUT'
        );
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete post route for admin user.
     */
    public function testDeletePostRouteAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = $this->createMock(PostServiceInterface::class);
        $postService->expects($this->once())
            ->method('findOneById')
            ->with($testPostId)
            ->willReturn($expectedPost);
        static::getContainer()->set(PostServiceInterface::class, $postService);

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete post route for admin user for post that does not exist.
     */
    public function testDeletePostRouteAdminForPostNotFound(): void
    {
        // given
        $expectedStatusCode = 302;
        $notPresentId = 1;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$notPresentId.'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete post route for anonymous user.
     */
    public function testDeletePostRouteAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;
        $testPostId = 1;
        $expectedPost = new Post();
        $postId = new \ReflectionProperty(Post::class, 'id');
        $postId->setValue($expectedPost, $testPostId);
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete post.
     */
    public function testDeletePost(): void
    {
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $expectedPost = new Post();
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = self::getContainer()->get(PostService::class);
        $postService->save($expectedPost);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/delete');
        $this->httpClient->submitForm(
            'Usuń'
        );

        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete post for post that has comments.
     */
    public function testDeletePostWithComment(): void
    {
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $expectedPost = new Post();
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($this->createCategory());
        $postService = self::getContainer()->get(PostService::class);
        $postService->save($expectedPost);

        $comment = new Comment();
        $comment->setNick('test Nick');
        $comment->setEmail('test@example.com');
        $comment->setContent('test comment content');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setPost($expectedPost);
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId().'/delete');
        $this->httpClient->submitForm(
            'Usuń'
        );

        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Create category function.
     *
     * @return Category Category
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

    /**
     * Create user function.
     *
     * @param array $roles Roles
     *
     * @return User User
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
