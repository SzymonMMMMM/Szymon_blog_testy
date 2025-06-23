<?php
/**
 * Post controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Enum\UserRole;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\Type\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PostControllerTest.
 */
class PostControllerTest extends WebTestCase
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
    public const TEST_ROUTE = '/post';

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
     * Test '/post' route.
     */
    public function testPostRoute(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single post for user.
     */
    public function testShowSinglePostUser(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/1');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single post and create comment post for Anonymous.
     */
    public function testShowSingleCreateCommentPostAnonymous(): void
    {
        // given
        $this->httpClient->request('GET', self::TEST_ROUTE . '/1');

        $this->assertSelectorExists('form');

        $postButtonText = $this->translator->trans('action.post');

        // when
        $this->httpClient->submitForm($postButtonText, [
            'comment[content]' => 'Test Post Content',
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-warning[role="alert"]');
    }

    /**
     * Test show single post and create comment post for user.
     */
    public function testShowSingleCreateCommentPostUser(): void
    {
        // given
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $this->httpClient->loginUser($user1);


        $this->httpClient->request('GET', self::TEST_ROUTE . '/1');
        $postButtonText = $this->translator->trans('action.post');

        // when
        $this->httpClient->submitForm($postButtonText, [
            'comment[content]' => 'Test Post Content',
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test create post form for anonymous user.
     */
    public function testCreatePostAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create post form for authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreatePostAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create post submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreatePostSubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        $createButtonText = $this->translator->trans('action.save');

        // when
        $this->httpClient->submitForm($createButtonText, [
            'post[title]' => 'Test Post Title',
            'post[content]' => 'Test Post Content',
            'post[category]' => $category->getId(),
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test create post submit with valid data and tags.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreatePostSubmitValidDataAndTags(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        $createButtonText = $this->translator->trans('action.save');

        // when
        $this->httpClient->submitForm($createButtonText, [
            'post[title]' => 'Test Post Title',
            'post[content]' => 'Test Post Content',
            'post[category]' => $category->getId(),
            'post[tags]' => 'tag1,tag2,tag3',
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test edit post form for anonymous user.
     */
    public function testEditPostAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/1/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }
    /**
     * Test edit category form for unauthorized user (not category owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategoryUnauthorizedUser(): void
    {
        // given
        $expectedStatusCode = 404;
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $user2 = $this->createUser([UserRole::ROLE_USER->value], 'user_test2@example.com');
        $category = $this->createCategory($user1);
        $post = $this->createPost($user1, $category);
        $this->httpClient->loginUser($user2);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $category->getId() . '/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit post form for authorized user (post owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditPostAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
        $post = $this->createPost($user, $category);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit post submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditPostSubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
        $post = $this->createPost($user, $category);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/edit');
        $editButtonText = $this->translator->trans('action.edit');

        // when
        $this->httpClient->submitForm($editButtonText, [
            'post[title]' => 'Test Post Title',
            'post[content]' => 'Test Post Content',
            'post[category]' => $category->getId(),
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test edit post submit with valid data and tags.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditPostSubmitValidDataAndTags(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
//        $tag1 = new Tag();
//        $tag1->setTitle('tag99');
//        $tag2 = new Tag();
//        $tag2->setTitle('tag88');

//        $post = $this->createPostWithTags($user, $category, [$tag1, $tag2]);
        $post = $this->createPostWithTags($user, $category);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/edit');
        $editButtonText = $this->translator->trans('action.edit');

        // when
        $this->httpClient->submitForm($editButtonText, [
            'post[title]' => 'Test Post Title',
            'post[content]' => 'Test Post Content',
            'post[category]' => $category->getId(),
            'post[tags]' => 'tag1,tag2,tag3',
        ]);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Test delete post form for anonymous user.
     */
    public function testDeletePostAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/1/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete post form for unauthorized user (not post owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeletePostUnauthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $user1 = $this->createUser([UserRole::ROLE_USER->value], 'user_test1@example.com');
        $user2 = $this->createUser([UserRole::ROLE_USER->value], 'user_test2@example.com');
        $category = $this->createCategory($user1);
        $post = $this->createPost($user1, $category);
        $this->httpClient->loginUser($user2);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete post form for authorized user (post owner).
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeletePostAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
        $post = $this->createPost($user, $category);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete post submit with valid data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeletePostSubmitValidData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);
        $category = $this->createCategory($user);
        $post = $this->createPost($user, $category);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $post->getId() . '/delete');
        $deleteButtonText = $this->translator->trans('action.delete');

        // when
        $this->httpClient->submitForm($deleteButtonText);
        // then
        $this->assertResponseRedirects();
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('div.alert-success[role="alert"]');
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
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
     * @param User   $author Post author
     * @param Category $category Post category
     * @param string $title  Post title
     *
     * @return Post Post entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createPost(User $author, Category $category, string $title = 'Test Post', string $content = 'Test Post Content'): Post
    {
        $post = new Post();
        $post->setAuthor($author);
        $post->setCategory($category);
        $post->setTitle($title);
        $post->setContent($content);

        $postRepository = static::getContainer()->get(PostRepository::class);
        $postRepository->save($post);

        return $post;
    }

    /**
     * Create post with tags.
     *
     * @param User   $author Post author
     * @param Category $category Post category
     * @param string $title  Post title
     *
     * @return Post Post entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createPostWithTags(User $author, Category $category, string $title = 'Test Post', string $content = 'Test Post Content'): Post
    {
        $post = new Post();
        $post->setAuthor($author);
        $post->setCategory($category);
        $post->setTitle($title);
        $post->setContent($content);

        $tag1 = new Tag();
        $tag1->setTitle('tag99');
        $post->addTag($tag1);
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag1);


        $postRepository = static::getContainer()->get(PostRepository::class);
        $postRepository->save($post);
        return $post;
    }

    /**
     * Create comment.
     *
     * @param User   $author Comment author
     * @param Post $post_id Post id
     * @param string $content  Comment content
     *
     * @return Comment Comment entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createComment(User $author, Post $post_id, string $content = 'Test Comment Content'): Comment
    {
        $comment = new Comment();
        $comment->setUser($author);
        $comment->setPost($post_id);
        $comment->setContent($content);

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $commentRepository->save($comment);

        return $comment;
    }
}