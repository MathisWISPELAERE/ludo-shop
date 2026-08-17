<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/account', name: 'app_account', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'user' => $this->getCurrentUser(),
        ]);
    }

    #[Route('/account/delete', name: 'app_account_delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_account', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $user = $this->getCurrentUser();
        $user->markAsDeleted();
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé.');

        return $this->redirectToRoute('app_logout');
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
