<?php
/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserRepository $userRepository User repository
     */
    public function __construct(UserRepository $userRepository);

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void;
}
