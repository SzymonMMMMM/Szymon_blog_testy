<?php
/**
 * Post entity.
 */

namespace App\Entity;

use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Post.
 */
#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
class Post
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
     * Created at.
     *
     * @var \DateTimeImmutable|null $CreatedAt Created at
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null $updatedAt Updated at
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Title.
     *
     * @var string|null $title Title
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    /**
     * Content.
     *
     * @var string|null $content Content
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 65535)]
    private ?string $content;

    /**
     * Category.
     *
     * @var Category|null $category Category
     */
    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'LAZY')]
    #[Assert\Type(Category::class)]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Category $category = null;

    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tag> $tags Tags
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'posts_tags')]
    private $tags;

    /**
     * Author.
     *
     * @var User|null $author Author
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    #[Assert\Type(User::class)]
    private ?User $author;


//    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comments::class)]
//    private Collection $comments;

//    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true)]
//    private Collection $comment;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
//        $this->comments = new ArrayCollection();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable $createdAt Created at
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param \DateTimeImmutable $updatedAt Updated at
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title Title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for content.
     *
     * @return string|null Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string|null $content Content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category|null $category Category
     */
    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag entity
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author Author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

//    /**
//     * @return Collection<int, Comments>
//     */
//    public function getComments(): Collection
//    {
//        return $this->comments;
//    }

//    public function addComment(Comments $comment): self
//    {
//        if (!$this->comments->contains($comment)) {
//            $this->comments->add($comment);
//            $comment->setPost($this);
//        }
//
//        return $this;
//    }

//    public function removeComment(Comments $comment): self
//    {
//        if ($this->comments->removeElement($comment)) {
//            // set the owning side to null (unless already changed)
//            if ($comment->getPost() === $this) {
//                $comment->setPost(null);
//            }
//        }
//
//        return $this;
//    }
}
