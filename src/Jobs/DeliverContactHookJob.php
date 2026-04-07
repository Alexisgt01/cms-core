<?php

namespace Alexisgt01\CmsCore\Jobs;

use Alexisgt01\CmsCore\Models\HookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class DeliverContactHookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = HookDelivery::with(['endpoint', 'request.contact'])->find($this->deliveryId);

        if (! $delivery) {
            return;
        }

        $endpoint = $delivery->endpoint;

        if (! $endpoint || ! $endpoint->enabled) {
            $delivery->update([
                'status' => 'failed',
                'last_error' => 'Endpoint disabled or not found',
            ]);

            return;
        }

        $request = $delivery->request;
        $contact = $request?->contact;

        $body = json_encode([
            'event' => $delivery->event,
            'hook_key' => $endpoint->hook_key,
            'contact' => $contact ? [
                'id' => $contact->id,
                'email' => $contact->email,
                'name' => $contact->name,
                'phone' => $contact->phone,
            ] : null,
            'request' => [
                'id' => $request->id,
                'type' => $request->type,
                'form_id' => $request->form_id,
                'state' => (string) $request->state,
                'payload' => $request->payload,
                'meta' => $request->meta,
                'created_at' => $request->created_at->toIso8601String(),
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $body, $endpoint->secret);

        $maxBodyLog = (int) config('cms-contacts.max_body_log_size', 4096);

        $headers = array_merge($endpoint->headers ?? [], [
            'Content-Type' => 'application/json',
            'X-Contacts-Event' => $delivery->event,
            'X-Contacts-Hook-Key' => $endpoint->hook_key,
            'X-Contacts-Timestamp' => (string) $timestamp,
            'X-Contacts-Signature' => $signature,
        ]);

        $delivery->update([
            'request_body' => mb_substr($body, 0, $maxBodyLog),
            'attempt' => $delivery->attempt + 1,
        ]);

        try {
            $response = Http::timeout($endpoint->timeout ?? 5)
                ->withHeaders($headers)
                ->withBody($body, 'application/json')
                ->post($endpoint->url);

            $delivery->update([
                'last_http_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, $maxBodyLog),
            ]);

            if ($response->successful()) {
                $delivery->update(['status' => 'success']);
            } else {
                $this->handleFailure($delivery, $endpoint, 'HTTP ' . $response->status());
            }
        } catch (\Throwable $e) {
            $this->handleFailure($delivery, $endpoint, $e->getMessage());
        }
    }

    private function handleFailure(HookDelivery $delivery, mixed $endpoint, string $error): void
    {
        $maxRetries = $endpoint->retries ?? 3;

        if ($delivery->attempt >= $maxRetries) {
            $delivery->update([
                'status' => 'failed',
                'last_error' => $error,
            ]);

            return;
        }

        $backoff = $endpoint->backoff ?? [5, 30, 120];
        $delaySeconds = $backoff[$delivery->attempt - 1] ?? end($backoff);

        $delivery->update([
            'status' => 'pending',
            'last_error' => $error,
            'next_retry_at' => now()->addSeconds($delaySeconds),
        ]);
    }
}
