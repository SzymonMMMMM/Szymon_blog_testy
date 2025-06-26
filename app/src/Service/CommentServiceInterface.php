<?php

/**
 * Comment service interface.
 */

namespace App\Service;

use App\Entity\Comment;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void;

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void;

    /**
     * Find by id.
     *
     * @param array $id Comment id
     *
     * @return Comment[] Array of Comment entities
     *
     * @throws NonUniqueResultException
     */
    public function findBy(array $id): array;
}
