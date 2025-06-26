<?php

/**
 * Comment entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class CommentTest.
 */
class CommentTest extends TestCase
{
    /**
     * Test getter and setter for Post.
     */
    public function testGetAndSetPost(): void
    {
        // given
        $comment = new Comment();
        $post = new Post();

        // when
        $comment->setPost($post);

        // then
        $this->assertSame($post, $comment->getPost(), 'getPost method returns Post');
    }

    /**
     * Test getters and setters for content.
     */
    public function testGetAndSetContent(): void
    {
        // given
        $comment = new Comment();
        $content = 'Test comment';

        // when
        $comment->setContent($content);

        // then
        $this->assertEquals($content, $comment->getContent());
    }

    /**
     * Test getters and setters for User.
     */
    public function testGetAndSetUser(): void
    {
        // given
        $comment = new Comment();
        $user = new User();

        // when
        $comment->setUser($user);

        // then
        $this->assertSame($user, $comment->getUser());
    }
}
