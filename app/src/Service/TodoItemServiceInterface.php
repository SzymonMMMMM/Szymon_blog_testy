<?php
/**
 * TodoItem service interface.
 */

namespace App\Service;

use App\Entity\TodoItem;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class TodoItemServiceInterface.
 */
interface TodoItemServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface;

    /**
     * Save entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function save(TodoItem $todoItem): void;

    /**
     * Delete entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function delete(TodoItem $todoItem): void;
}
