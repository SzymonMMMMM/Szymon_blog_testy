<?php

/**
 * Post service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\CategoryServiceInterface;
use App\Service\PostService;
use App\Service\PostServiceInterface;
use App\Service\TagServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PostServiceTest.
 */
class PostServiceTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?PostServiceInterface $postService;
    private ?CategoryServiceInterface $categoryService;
    private ?TagServiceInterface $tagService;
    private ?User $testUser;

    /**
     * Set up test.
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->postService = $container->get(PostService::class);
        $this->categoryService = $container->get('App\Service\CategoryServiceInterface');
        $this->tagService = $container->get('App\Service\TagServiceInterface');
        $this->testUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
    }

    /**
     * Test prepare filters with a category.
     */
    public function testPrepareFiltersWithCategory(): void
    {
        // given

        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Category');

        $expectedCategory->setAuthor($this->testUser);
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();
        $filters = ['category_id' => $expectedCategory->getId()];

        // when
        $result = $this->postService->prepareFilters($filters);

        // then
        $this->assertArrayHasKey('category', $result);
        $this->assertEquals($expectedCategory, $result['category']);
    }

    /**
     * Test prepare filters with a tag.
     */
    public function testPrepareFiltersWithTag(): void
    {
        // given
        $expectedTag = new Tag();
        $expectedTag->setTitle('Test Tag');

        $this->entityManager->persist($expectedTag);
        $this->entityManager->flush();
        $tag = $this->createTag($this->testUser);
        $filters = ['tag_id' => $tag->getId()];

        // when
        $result = $this->postService->prepareFilters($filters);

        // then
        $this->assertArrayHasKey('tag', $result);
        $this->assertEquals($tag, $result['tag']);
    }

    /**
     * Create user.
     *
     * @param array  $roles User roles
     * @param string $email User email
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles, string $email = 'testuser@example.com'): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    /**
     * Create category.
     *
     * @param User   $author Category author
     * @param string $title  Category title
     *
     * @return Category Category entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createCategory(User $author, string $title = 'Test Category'): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setAuthor($author);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create tag.
     *
     * @param User   $author Tag author
     * @param string $title  Tag title
     *
     * @return Tag Tag entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createTag(User $author, string $title = 'Test Tag'): Tag
    {
        $tag = new Tag();
        $tag->setTitle($title);
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }
}
