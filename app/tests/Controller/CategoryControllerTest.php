<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\CategoryServiceInterface;
use App\Service\PostService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

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

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
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
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * @return void Void
     */
    public function testShowCategoryRoute(): void
    {
        // given
        $expectedStatusCode = 200;
        $category = new Category();
        $category->setTitle('test category');
        $categoryService = self::getContainer()->get(CategoryServiceInterface::class);
        $categoryService->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * @return void Void
     */
    public function testCreateCategoryAnonymous(): void
    {
        $this->httpClient->request('GET', '/category/create');

        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @return void Void
     *
     * @throws ContainerExceptionInterface Container Exception Interface
     * @throws NotFoundExceptionInterface  Not Found Exception Interface
     * @throws ORMException                ORMException
     * @throws OptimisticLockException     Optimistic Lock Exception
     */
    public function testCreateCategoryAdmin(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $this->httpClient->submitForm('Zapisz', [
            'category' => [
                'title' => 'test category',
            ],
        ]);

        $this->assertResponseRedirects('/category');
        $this->httpClient->followRedirect();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * @return void Void
     *
     * @throws ContainerExceptionInterface Container Exception Interface
     * @throws NotFoundExceptionInterface  Not Found Exception Interface
     * @throws ORMException                ORMException
     * @throws OptimisticLockException     Optimistic Lock Exception
     */
    public function testEditCategoryAdmin(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $this->httpClient->submitForm('Edytuj', [
            'category' => [
                'title' => 'test category edited',
            ],
        ]);

        $this->assertResponseRedirects('/category');
        $this->httpClient->followRedirect();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * @return void Void
     */
    public function testEditCategoryAnonymous(): void
    {
        $expectedStatusCode = 302;

        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * @return void Void
     *
     * @throws ContainerExceptionInterface Container Exception Interface
     * @throws NotFoundExceptionInterface  Not Found Exception Interface
     * @throws ORMException                ORMException
     * @throws OptimisticLockException     Optimistic Lock Exception
     */
    public function testDeleteCategoryWitohoutPostsAdmin(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');
        $this->httpClient->submitForm('Usuń');

        $this->assertResponseRedirects('/category');
        $this->httpClient->followRedirect();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $remainingCategories = $categoryRepository->findAll();
        $this->assertCount(0, $remainingCategories);
    }

    /**
     * @return void Void
     *
     * @throws ContainerExceptionInterface Container Exception Interface
     * @throws NotFoundExceptionInterface  Not Found Exception Interface
     * @throws ORMException                ORMException
     * @throws OptimisticLockException     Optimistic Lock Exception
     */
    public function testDeleteCategoryWitPostsAdmin(): void
    {
        $expectedStatusCode = 302;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);
        $expectedPost = new Post();
        $expectedPost->setTitle('Test post');
        $expectedPost->setContent('Test content');
        $expectedPost->setCreatedAt(new \DateTimeImmutable());
        $expectedPost->setUpdatedAt(new \DateTimeImmutable());
        $expectedPost->setCategory($category);
        $postService = self::getContainer()->get(PostService::class);
        $postService->save($expectedPost);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $this->httpClient->followRedirect();
        $remainingCategories = $categoryRepository->findAll();
        $this->assertCount(1, $remainingCategories);
    }

    /**
     * Delete cateogry as anonymous test.
     *
     * @return void Void
     */
    public function testDeleteCategoryAnonymous(): void
    {
        $expectedStatusCode = 302;

        $category = new Category();
        $category->setTitle('test category title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $remainingCategories = $categoryRepository->findAll();
        $this->assertCount(1, $remainingCategories);
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
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
