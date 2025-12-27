<?php

declare(strict_types=1);

namespace App\UseCases\SendFireCountEmail;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handler for sending fire count confirmation email.
 */
final readonly class SendFireCountEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function handle(SendFireCountEmailCommand $command): void
    {
        $summaryUrl = $this->urlGenerator->generate(
            'fire_count_summary',
            ['id' => $command->fireCountId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->to(new Address($command->email))
            ->subject($this->translator->trans('fire_count.email.subject'))
            ->htmlTemplate('emails/fire_count_confirmation.html.twig')
            ->context([
                'summaryUrl' => $summaryUrl,
                'adultsCount' => $command->adultsCount,
                'childrenCount' => $command->childrenCount,
                'totalCount' => $command->adultsCount + $command->childrenCount,
            ]);

        $this->mailer->send($email);
    }
}