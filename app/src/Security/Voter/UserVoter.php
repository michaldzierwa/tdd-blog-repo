<?php

/**
 * User Voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Voter.
 */
class UserVoter extends Voter
{
    public const EDIT = 'USER_EDIT';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT])
            && $subject instanceof User;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string         $attribute An attribute
     * @param mixed          $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     * @param TokenInterface $token     Token Interface
     *
     * @return bool Result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can delete comment.
     *
     * @param User          $userTomanage User to manage
     * @param UserInterface $user         User Interface
     *
     * @return bool Boolean
     */
    private function canEdit(User $userTomanage, UserInterface $user): bool
    {
        if ($userTomanage->getUserIdentifier() === $user->getUserIdentifier()) {
            return true;
        }

        $roleUser = ['ROLE_USER'];
        foreach ($user->getRoles() as $role) {
            if ('ROLE_ADMIN' === $role && $userTomanage->getRoles() === $roleUser) {
                return true;
            }
        }

        return false;
    }
}
