<?php

/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserRepository $userRepository User repository
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }
}
