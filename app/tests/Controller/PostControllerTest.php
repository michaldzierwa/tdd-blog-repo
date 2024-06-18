<?php
/**
 * Fizz Buzz controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\PostServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class FizzBuzzControllerTest.
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
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Title 1');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

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

    /**
     * Test route.
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

    public function testShowPost(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 1;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $testPostId = 1;
        $expectedPost = new Post();
        $postIdProperty = new \ReflectionProperty(Post::class, 'id');
        $postIdProperty->setValue($expectedPost, $testPostId);
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
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedPost->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    public function testShowPostForNonExistantPost(): void
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

    //    public function testCanCreatePost(): void
    //    {
    //        // given
    //        $expectedStatusCode = 200;
    //        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
    //        $this->httpClient->loginUser($adminUser);
    //        // when
    //        $route = self::TEST_ROUTE . '/create';
    //        $this->httpClient->request('GET', $route);
    //        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
    //
    //        // then
    //        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    //    }

    public function testCreatePost(): void
    {
        // given
        $expectedStatusCode = 302;

        $testCategoryId = 1;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        $createdPostTitle = 'Test post';
        $createdPostContent = 'Test post';
        $createdCategory = $this->createCategory();
        // when
        $route = self::TEST_ROUTE.'/create';
        //        echo $route;
        $this->httpClient->request('GET', $route);
        $this->httpClient->submitForm(
            'Save',
            ['post' => [
                'title' => $createdPostTitle,
                'content' => $createdPostContent,
                'category' => $createdCategory->getId(),
            ],
            ]
        );
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    //    public function testEditPostWithMock(): void
    //    {
    //        // given
    //        $expectedStatusCode = 200;
    //        $testCategoryId = 1;
    //        $expectedCategory = new Category();
    //        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
    //        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
    //        $expectedCategory->setTitle('Test category');
    //        $testPostId = 1;
    //        $expectedPost = new Post();
    //        $postIdProperty = new \ReflectionProperty(Post::class, 'id');
    //        $postIdProperty->setValue($expectedPost, $testPostId);
    //        $expectedPost->setTitle('Test post');
    //        $expectedPost->setContent('Test post content');
    //        $expectedPost->setCreatedAt(new \DateTimeImmutable());
    //        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
    //        $expectedPost->setCategory($this->createCategory());
    //        $postService = $this->createMock(PostServiceInterface::class);
    //        $postService->expects($this->once())
    //            ->method('findOneById')
    //            ->with($testPostId)
    //            ->willReturn($expectedPost);
    //        static::getContainer()->set(PostServiceInterface::class, $postService);
    //        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
    //        $this->httpClient->loginUser($adminUser);
    //
    //        // when
    //        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedPost->getId() . '/edit');
    //        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
    //        // then
    //        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    //    }

    //    public function testDeletePostWithMock(): void
    //    {
    //        // given
    //        $expectedStatusCode = 200;
    //        $testCategoryId = 123;
    //        $expectedCategory = new Category();
    //        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
    //        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
    //        $expectedCategory->setTitle('Test category');
    //        $testPostId = 122;
    //        $expectedPost = new Post();
    //        $postIdProperty = new \ReflectionProperty(Post::class, 'id');
    //        $postIdProperty->setValue($expectedPost, $testPostId);
    //        $expectedPost->setTitle('Test post');
    //        $expectedPost->setContent('test content');
    //        $expectedPost->setCreatedAt(new \DateTimeImmutable());
    //        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
    //        $expectedPost->setCategory($this->createCategory());
    //        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
    //        $this->httpClient->loginUser($adminUser);
    //
    //        // when
    //        $route = self::TEST_ROUTE . '/' . $expectedPost->getId() . '/delete';
    //        $this->httpClient->request('GET', $route);
    //        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
    //
    //        // then
    //        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    //    }
}
