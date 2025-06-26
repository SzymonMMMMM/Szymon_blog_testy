<?php

/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single category for anonymous user.
     */
    public function testShowSingleCategoryAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/1');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single category for unauthorized user (category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSingleCategoryUnauthorizedUser(): void
    {
        // given
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $user2 = $this->createUser([UserRole::ROLE_USER->value], 'user_test2@example.com');
        $category = $this->createCategory($user1);
        $this->httpClient->loginUser($user2);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId());
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-warning[role="alert"]');
    }

    /**
     * Test show single category for authorized user (category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSingleCategoryAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId());
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create category form for anonymous user.
     */
    public function testCreateCategoryAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create category form for authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateCategoryAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create category submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateCategorySubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $createButtonText = $this->translator->trans('action.save');
        // when
        $this->httpClient->submitForm($createButtonText, [
            'category[title]' => 'Test Category Title',
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test delete category form for unauthorized user (not category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategoryUnauthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $user2 = $this->createUser([UserRole::ROLE_USER->value], 'user_test2@example.com');
        $category = $this->createCategory($user1);
        $this->httpClient->loginUser($user2);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit category form for anonymous user.
     */
    public function testEditCategoryAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/1/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit category form for authorized user (category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategoryAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit category submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategorySubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $editButtonText = $this->translator->trans('action.edit');

        // when
        $this->httpClient->submitForm($editButtonText, [
            'category[title]' => 'Updated Category Title',
        ]);

        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test delete category form for anonymous user.
     */
    public function testDeleteCategoryAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/1/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete category form for unauthorized user (not category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategoryUnauthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $user2 = $this->createUser([UserRole::ROLE_USER->value], 'user_test2@example.com');
        $category = $this->createCategory($user1);
        $this->httpClient->loginUser($user2);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete category form for authorized user (category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategoryAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete category submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategorySubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');
        $deleteButtonText = $this->translator->trans('action.delete');

        // when
        $this->httpClient->submitForm($deleteButtonText, [
            'category[title]' => 'Deleted Category Title',
        ]);

        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test delete category submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategorySubmitCategoryHasPosts(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
        $this->createPost($user, $category);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');

        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-warning[role="alert"]');
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
    private function createUser(array $roles, string $email = 'user@example.com'): User
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
     * Create post.
     *
     * @param User     $author   Post author
     * @param Category $category Category category
     * @param string   $title    Post title
     * @param string   $content  Post content
     *
     * @return Post Post entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createPost(User $author, Category $category, string $title = 'Test Post title', string $content = 'Test Post content'): Post
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCreatedAt(new \DateTimeImmutable('2023-01-15 14:30:00'));
        $post->setUpdatedAt(new \DateTimeImmutable('2023-01-15 16:30:00'));
        $post->setAuthor($author);
        $post->setCategory($category);
        $postRepository = static::getContainer()->get(PostRepository::class);
        $postRepository->save($post);

        return $post;
    }
}
