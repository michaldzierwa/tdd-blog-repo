<?php
/**
 * Post service.
 */

namespace App\Service;

use App\Dto\PostListFiltersDto;
use App\Dto\PostListInputFiltersDto;
use App\Entity\Post;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class PostService.
 */
class PostService implements PostServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * @param CategoryServiceInterface $categoryService Category Service
     * @param PostRepository           $postRepository  Post Repository
     * @param PaginatorInterface       $paginator       Paginator
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly PostRepository $postRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * @param int                     $page    Page
     * @param PostListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Pagination Interface
     */
    public function getPaginatedList(int $page, PostListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->postRepository->queryAll($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Post $post Post entity
     */
    public function save(Post $post): void
    {
        $this->postRepository->save($post);
    }

    /**
     * Delete entity.
     *
     * @param Post $post Post entity
     */
    public function delete(Post $post): void
    {
        $this->postRepository->delete($post);
    }

    /**
     * @param int $id Id
     *
     * @return Post|null Post
     */
    public function findOneById(int $id): ?Post
    {
        return $this->postRepository->findOneById($id);
    }

    /**
     * Prepare filters for the posts list.
     *
     * @param PostListInputFiltersDto $filters Raw filters from request
     *
     * @return PostListFiltersDto Result filters
     */
    private function prepareFilters(PostListInputFiltersDto $filters): PostListFiltersDto
    {
        return new PostListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
        );
    }
}
