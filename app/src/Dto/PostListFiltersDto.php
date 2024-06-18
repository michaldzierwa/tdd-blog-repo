<?php
/**
 * Post list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;

/**
 * Class PostListFiltersDto.
 */
class PostListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null $category Category entity
     */
    public function __construct(public readonly ?Category $category)
    {
    }
}
