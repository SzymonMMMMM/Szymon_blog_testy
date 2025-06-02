<?php
/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{



    /**
     * Comment repository.
     */
    private CommentRepository $commentRepository;



    /**
     * Constructor.
     *
     * @param CommentRepository           $commentRepository     Comment repository
     */
    public function __construct(CommentRepository $commentRepository)
    {

        $this->commentRepository = $commentRepository;
    }


    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }


    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }

}
