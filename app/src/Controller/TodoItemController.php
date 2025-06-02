<?php
/**
 * TodoItem controller.
 */

namespace App\Controller;

use App\Entity\TodoItem;
use App\Entity\User;
use App\Service\TodoItemServiceInterface;
use App\Form\Type\TodoItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TodoItemController.
 */
#[Route('/todoitem')]
class TodoItemController extends AbstractController
{
    /**
     * TodoItem service.
     */
    private TodoItemServiceInterface $todoItemService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param TodoItemServiceInterface $todoItemService TodoItem service
     * @param TranslatorInterface      $translator      Translator
     */
    public function __construct(TodoItemServiceInterface $todoItemService, TranslatorInterface $translator)
    {
        $this->todoItemService = $todoItemService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'todoitem_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $pagination = $this->todoItemService->getPaginatedList(
            $request->query->getInt('page', 1),
            $user
        );

        return $this->render('todoitem/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param TodoItem $todoItem TodoItem
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'todoitem_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(TodoItem $todoItem): Response
    {
        if ($todoItem->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        return $this->render('todoitem/show.html.twig', ['todoitem' => $todoItem]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'todoitem_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $todoItem = new TodoItem();
        $todoItem->setAuthor($user);
        $form = $this->createForm(TodoItemType::class, $todoItem);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->save($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        return $this->render(
            'todoitem/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'todoitem_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, TodoItem $todoItem): Response
    {
        if ($todoItem->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        $form = $this->createForm(
            TodoItemType::class,
            $todoItem,
            [
            'method' => 'PUT',
            'action' => $this->generateUrl('todoitem_edit', ['id' => $todoItem->getId()]),
            'is_edit' => true,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->save($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        return $this->render(
            'todoitem/edit.html.twig',
            [
                'form' => $form->createView(),
                'todoitem' => $todoItem,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'todoitem_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, TodoItem $todoItem): Response
    {
        if ($todoItem->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        $form = $this->createForm(
            TodoItemType::class,
            $todoItem,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('todoitem_delete', ['id' => $todoItem->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->delete($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('todoitem_index');
        }

        return $this->render(
            'todoitem/delete.html.twig',
            [
                'form' => $form->createView(),
                'todoitem' => $todoItem,
            ]
        );
    }
}
