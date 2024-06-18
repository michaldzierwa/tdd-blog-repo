<?php
/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * Comment repository.
     */
    private CommentRepository $commentRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Constructor.
     *
     * @param CommentRepository  $commentRepository Comment repository
     * @param PaginatorInterface $paginator         Paginator
     * @param PostRepository     $postRepository    Post repository
     */
    public function __construct(CommentRepository $commentRepository, PaginatorInterface $paginator, PostRepository $postRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->paginator = $paginator;
        $this->postRepository = $postRepository;
    }

    /**
     * Get paginated list.
     *
     * @param int  $page Page number
     * @param Post $post Post
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, Post $post): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->commentRepository->queryAll($post),
            $page,
            //    CommentRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }

    /**
     * @param Post $post Post
     *
     * @return array|null Array
     */
    public function findByPost(Post $post): ?array
    {
        $postId = $post->getId();

        return $this->commentRepository->findBy(['post' => $postId]);
    }
}
