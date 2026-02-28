<?php

namespace Alexisgt01\CmsCore\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestEmail extends Mailable
{
    public function __construct(
        public string $siteName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Test] Email de test — ' . $this->siteName,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $date = now()->format('d/m/Y H:i:s');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head><meta charset="UTF-8"></head>
        <body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <h1 style="color: #1a1a1a; font-size: 24px;">Email de test</h1>
            <p style="color: #333; font-size: 16px; line-height: 1.5;">
                Cet email confirme que la configuration d'envoi d'emails fonctionne correctement
                pour <strong>{$this->siteName}</strong>.
            </p>
            <p style="color: #666; font-size: 14px;">
                Envoye le {$date}
            </p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="color: #999; font-size: 12px;">
                Cet email a ete envoye automatiquement depuis le panneau d'administration.
            </p>
        </body>
        </html>
        HTML;
    }
}
