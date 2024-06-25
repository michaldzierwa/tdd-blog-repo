<?php
/**
 * User controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PostControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/user';

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
    public function testIndexRouteAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test route.
     *
     * @return void Void
     */
    public function testIndexRouteAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test showing user profile for anonymous user.
     */
    public function testShowUserAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test showing user profile for admin user.
     */
    public function testShowUserAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test editing user profile with appropriate privileges.
     */
    public function testEditUserWithPrivileges(): void
    {
        // given
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId().'/edit');
        $this->httpClient->submitForm(
            'Edytuj',
            [
                'user[email]' => 'newemail@example.com',
                'user[password][first]' => 'newpasswd1234',
                'user[password][second]' => 'newpasswd1234',
            ],
            'PUT'
        );

        // then
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);

        $this->assertTrue($this->httpClient->getResponse()->isRedirect($this->httpClient->getContainer()->get('router')->generate('post_index')));

        $this->httpClient->followRedirect();
        $updatedUser = $this->getContainer()->get(UserRepository::class)->find($adminUser->getId());
        $this->assertTrue($this->getContainer()->get('security.password_hasher')->isPasswordValid($updatedUser, 'newpasswd1234'));

        $this->assertEquals('newemail@example.com', $updatedUser->getEmail());
    }

    /**
     * Test editing user profile without privileges.
     */
    public function testEditUserWithoutPrivileges(): void
    {
        // given
        $expectedStatusCode = 403;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'admin@example.com');
        $adminUserToEdit = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'adminToEdit@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUserToEdit->getId().'/edit');

        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Helper method to create a user object.
     *
     * @param array  $roles user roles
     * @param string $email user email
     *
     * @return User created user object
     */
    private function createUser(array $roles, string $email = 'user@example.com'): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
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
