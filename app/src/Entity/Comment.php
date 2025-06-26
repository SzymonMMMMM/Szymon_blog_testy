<?php

/**
 * Category entity.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Comment.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    /**
     * Primary key.
     *
     * @var int|null $id ID
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Content.
     *
     * @var string|null $content Content
     */
    #[ORM\Column(type: 'string', length: 15000)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 15000)]
    private ?string $content;

    /**
     * User.
     *
     * @var User|null $user User
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\Type(User::class)]
    private ?User $user = null;

    /**
     * Post.
     *
     * @var Post|null $post Post
     */
    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Post $post;

    /**
     * Getter for id.
     *
     * @return int|null id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for content.
     *
     * @return string|null content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string $content Content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Getter for user.
     *
     * @return User|null User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Setter for user.
     *
     * @param User|null $user User
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * Getter for post.
     *
     * @return Post|null Post
     */
    public function getPost(): ?Post
    {
        return $this->post;
    }

    /**
     * Setter for post.
     *
     * @param Post|null $post Post
     */
    public function setPost(?Post $post): void
    {
        $this->post = $post;
    }
}
