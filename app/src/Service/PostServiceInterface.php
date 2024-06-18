<?php
/**
 * Post service interface.
 */

namespace App\Service;

use App\Dto\PostListInputFiltersDto;
use App\Entity\Post;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface PostServiceInterface.
 */
interface PostServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                     $page    Page number
     * @param PostListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, PostListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Post $post Post entity
     */
    public function save(Post $post): void;

    /**
     * Delete entity.
     *
     * @param Post $post Post entity
     */
    public function delete(Post $post): void;

    /**
     * @param int $id Id
     *
     * @return Post|null Post
     */
    public function findOneById(int $id): ?Post;
}
