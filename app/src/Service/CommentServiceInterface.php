<?php
/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * @param int  $page Page
     * @param Post $post Post
     *
     * @return PaginationInterface Paginator interface
     */
    public function getPaginatedList(int $page, Post $post): PaginationInterface;

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void;

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void;
}
