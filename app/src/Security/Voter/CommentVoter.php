<?php
/**
 * Comment voter.
 */

namespace App\Security\Voter;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CommentVoter.
 */
class CommentVoter extends Voter
{

    /**
     * Delete permission.
     *
     * @const string
     */
    private const DELETE = 'DELETE';

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
        return in_array($attribute, [self::DELETE])
            && $subject instanceof Comment;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Comment) {
            return false;
        }

        return match ($attribute) {
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can delete comment.
     *
     * @param Comment          $comment Comment entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Comment $comment, UserInterface $user): bool
    {
        // Allow the author to delete their own comment
        if ($comment->getAuthor() === $user) {
            return true;
        }

        // Allow admin to delete any comment
        foreach ($user->getRoles() as $role) {
            if ($role === 'ROLE_ADMIN') {
                return true;
            }
        }

        return false;
    }
}
