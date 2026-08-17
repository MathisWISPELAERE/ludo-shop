<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Form\OrderStatusFormType;
use App\Service\OrderStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderStatusService $orderStatusService,
    ) {
    }

    #[Route('', name: 'app_admin_orders', methods: ['GET'])]
    public function index(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        $allowedStatuses = $this->orderStatusService->allowedTransitions($order);
        $statusForm = $this->createForm(OrderStatusFormType::class, null, ['statuses' => $allowedStatuses]);

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
            'allowedStatuses' => $allowedStatuses,
            'statusForm' => $statusForm,
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_order_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateStatus(Order $order, Request $request): Response
    {
        $allowedStatuses = $this->orderStatusService->allowedTransitions($order);
        $form = $this->createForm(OrderStatusFormType::class, null, ['statuses' => $allowedStatuses]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();
            if (!$status instanceof OrderStatus) {
                $this->addFlash('danger', 'Statut invalide.');
            } else {
                try {
                    $this->orderStatusService->transition($order, $status);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Statut mis à jour.');
                } catch (\LogicException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }
}
