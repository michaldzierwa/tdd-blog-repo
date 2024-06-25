<?php

/**
 * Comment controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CommentControllerTest.
 */
class CommentControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/comment';

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
     * Test creating a comment via form submission.
     */
    public function testCreateComment(): void
    {
        $post = $this->createPost();
        $checkRoute = $this->httpClient->request('GET', '/comment/'.$post->getId().'/create');
        $this->assertResponseIsSuccessful();

        $this->httpClient->submitForm('Zapisz', [
            'comment[nick]' => 'test nick',
            'comment[email]' => 'test@example.com',
            'comment[content]' => 'test comment content',
        ]);

        $this->assertResponseRedirects('/post/'.$post->getId());
        $this->httpClient->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('test comment content', $this->httpClient->getResponse()->getContent());
    }

    /**
     * Test deleting a comment.
     */
    public function testDeleteComment(): void
    {
        $post = $this->createPost();
        $comment = $this->createComment($post);
        $this->httpClient->request('GET', '/comment/'.$comment->getId().'/delete');
        $this->assertResponseRedirects('/login');

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $this->httpClient->request('GET', '/comment/'.$comment->getId().'/delete');
        $this->assertResponseIsSuccessful();

        $form = $this->httpClient->getCrawler()->selectButton('Usuń')->form();
        $this->httpClient->submit($form);

        $this->assertResponseRedirects('/post');
        $this->httpClient->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('test comment content', $this->httpClient->getResponse()->getContent());

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $deletedComment = $entityManager->getRepository(Comment::class)->find($comment->getId());
        $this->assertNull($deletedComment);
    }

    /**
     * Creates and persists a category entity.
     *
     * @return Category created category object
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
     * Creates and persists a post entity with a category.
     *
     * @return Post created post object
     */
    protected function createPost(): Post
    {
        $post = new Post();
        $post->setTitle('test post title');
        $post->setContent('test post content');
        $post->setUpdatedAt(new \DateTimeImmutable());
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setCategory($this->createCategory());
        $postRepository = self::getContainer()->get(PostRepository::class);
        $postRepository->save($post);

        return $post;
    }

    /**
     * Creates a user with the specified roles.
     *
     * @param array $roles roles to assign to the user
     *
     * @return User created user object
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

    /**
     * Creates and persists a comment entity associated with a post.
     *
     * @param Post $post post entity to associate the comment with
     *
     * @return Comment created comment object
     */
    private function createComment(Post $post): Comment
    {
        $comment = new Comment();
        $comment->setNick('test Nick');
        $comment->setEmail('test@example.com');
        $comment->setContent('test comment content');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setPost($post);

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($comment);
        $entityManager->flush();

        return $comment;
    }
}
