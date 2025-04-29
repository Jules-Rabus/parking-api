<?php

namespace App\Controller;

use App\Entity\Client;
use App\Helper\PasswordHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    /**
     * @var ResetPasswordHelperInterface
     */
    private $resetPasswordHelper;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     * @param EntityManagerInterface       $entityManager
     */
    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
    }

    #[Route('', name : 'app_forgot_password_request')]
    public function request(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $em,
        PasswordHelper $ph
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return new JsonResponse(['error' => 'Email requis'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(Client::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            $ph->checkEmail();
        }

        return $ph->processSendingPasswordResetEmail($user, $mailer);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name : 'app_reset_password')]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        TranslatorInterface $translator,
        string $token = null
    ): JsonResponse {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            return new JsonResponse(
                ['error' => 'No reset password token found in the URL or in the session.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash(
                'reset_password_error',
                sprintf(
                    '%s - %s',
                    $translator->trans(
                        ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                        [],
                        'ResetPasswordBundle'
                    ),
                    $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
                )
            );

            return new JsonResponse(
                ['error' => 'Échec de l\'authentification. Veuillez vous reconnecter.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($request->getContent(), true);

        // The token is valid; allow the user to change their password.
        if (!isset($data['password'])) {
            return new JsonResponse(['error' => 'Mot de passe requis'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode(hash) the plain password, and set it.
        $encodedPassword = $userPasswordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($encodedPassword);
        $this->entityManager->flush();

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();

        return new JsonResponse([
            'success' => true,
            'message' => 'Mot de passe réinitialisé.',
        ], JsonResponse::HTTP_OK);
    }
}
