<?php

/**
 * Post controller.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\Type\CommentType;
use App\Form\Type\PostType;
use App\Service\CommentServiceInterface;
use App\Service\PostServiceInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PostController.
 */
#[Route('/post')]
class PostController extends AbstractController
{
    /**
     * Post Service.
     */
    private PostServiceInterface $postService;

    /**
     * Comment Service.
     */
    private CommentServiceInterface $commentService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param PostServiceInterface    $postService    Post Service
     * @param CommentServiceInterface $commentService Comment Service
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(PostServiceInterface $postService, CommentServiceInterface $commentService, TranslatorInterface $translator)
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'post_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        /** @var array $filters */
        $filters = $this->getFilters($request);
        $pagination = $this->postService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('posts/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Post    $post    Post entity
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @throws NonUniqueResultException
     */
    #[Route('/{id}', name: 'post_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPost($post);

        $form = $this->createForm(
            CommentType::class,
            $comment,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('post_show', ['id' => $post->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.can_not_create_a_comment')
                );
            } else {
                $this->commentService->Save($comment);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.comment_created_successfully')
                );
            }

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        $comment = $this->postService->findOneBy($post->getId());

        return $this->render(
            'posts/show.html.twig',
            [
                'post' => $post,
                'form' => $form->createView(),
                'comment' => $comment,
            ]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'post_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $post = new Post();
        $post->setAuthor($user);
        $form = $this->createForm(
            PostType::class,
            $post,
            [
                'action' => $this->generateUrl('post_create'),
                'author' => $post->getAuthor(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render('posts/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'post_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Post $post): Response
    {
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.you_cant_edit_not_your_post')
            );

            return $this->redirectToRoute('post_index');
        }

        $form = $this->createForm(
            PostType::class,
            $post,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('post_edit', ['id' => $post->getId()]),
                'author' => $post->getAuthor(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'posts/edit.html.twig',
            [
                'form' => $form->createView(),
                'posts' => $post,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'post_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Post $post): Response
    {
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.you_cant_delete_not_your_post')
            );

            return $this->redirectToRoute('post_index');
        }

        $form = $this->createForm(
            FormType::class,
            $post,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('post_delete', ['id' => $post->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->delete($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'posts/delete.html.twig',
            [
                'form' => $form->createView(),
                'posts' => $post,
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');

        return $filters;
    }
}
