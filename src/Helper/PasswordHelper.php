<?php

namespace App\Helper;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordHelper
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

    /**
     * @param string              $emailFormData
     * @param MailerInterface $mailer
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse
     */
    public function processSendingPasswordResetEmail(
        Client $user,
        MailerInterface $mailer
    ): JsonResponse {
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->checkEmail();
        }

        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['MAIL_GESTION'], 'Parking-Rue-Du-Moulin'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->checkEmail();
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function checkEmail(): JsonResponse
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        $resetToken = $this->getTokenObjectFromSession();

        if (null === $resetToken) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return new JsonResponse([
            'message' => 'Si un compte existe avec cet email, un email de réinitialisation a été envoyé.',
            'reset_token' => $resetToken
        ], JsonResponse::HTTP_OK);
    }
}
