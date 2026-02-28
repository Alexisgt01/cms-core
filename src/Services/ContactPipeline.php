<?php

namespace Alexisgt01\CmsCore\Services;

use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\Contact;
use Alexisgt01\CmsCore\Models\ContactRequest;
use Alexisgt01\CmsCore\Models\ContactSetting;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Alexisgt01\CmsCore\Models\HookEndpoint;

class ContactPipeline
{
    /**
     * Handle an incoming contact event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array{idempotency_key?: string, hook_key?: string|array<int, string>, form_id?: string, meta?: array<string, mixed>}  $options
     */
    public function handle(string $type, array $payload = [], array $options = []): ContactRequest
    {
        $idempotencyKey = $options['idempotency_key'] ?? null;

        if ($idempotencyKey !== null) {
            $existing = ContactRequest::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $contact = $this->upsertContact($payload);

        $request = ContactRequest::create([
            'contact_id' => $contact?->id,
            'type' => $type,
            'form_id' => $options['form_id'] ?? ($payload['form_id'] ?? null),
            'state' => 'new',
            'payload' => $payload,
            'meta' => $options['meta'] ?? null,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->dispatchHooks($request, $type, $options['hook_key'] ?? null);

        return $request;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertContact(array $payload): ?Contact
    {
        $email = $payload['email'] ?? null;

        if (! is_string($email) || $email === '') {
            return null;
        }

        $name = $payload['name'] ?? null;

        if ($name === null && isset($payload['first_name'])) {
            $name = trim(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''));
        }

        $contact = Contact::upsertByEmail($email, [
            'name' => $name,
            'phone' => $payload['phone'] ?? null,
        ]);

        if (isset($payload['tags']) && is_array($payload['tags'])) {
            $existingTags = $contact->tags ?? [];
            $merged = array_values(array_unique(array_merge($existingTags, $payload['tags'])));
            $contact->update(['tags' => $merged]);
        }

        if (isset($payload['consents']) && is_array($payload['consents'])) {
            $existingConsents = $contact->consents ?? [];
            $merged = array_merge($existingConsents, $payload['consents']);
            $contact->update(['consents' => $merged]);
        }

        if (isset($payload['attribs']) && is_array($payload['attribs'])) {
            $existingAttribs = $contact->attribs ?? [];
            $merged = array_merge($existingAttribs, $payload['attribs']);
            $contact->update(['attribs' => $merged]);
        }

        return $contact;
    }

    /**
     * @param  string|array<int, string>|null  $hookKey
     */
    private function dispatchHooks(ContactRequest $request, string $event, string|array|null $hookKey): void
    {
        $query = HookEndpoint::query()->where('enabled', true);

        if ($hookKey !== null) {
            $keys = is_array($hookKey) ? $hookKey : [$hookKey];
            $query->whereIn('hook_key', $keys);
        }

        $endpoints = $query->get();

        $async = ContactSetting::instance()->default_async
            ?? config('cms-contacts.default_async', true);

        foreach ($endpoints as $endpoint) {
            if (! $endpoint->acceptsEvent($event)) {
                continue;
            }

            $delivery = HookDelivery::create([
                'hook_endpoint_id' => $endpoint->id,
                'contact_request_id' => $request->id,
                'event' => $event,
                'status' => 'pending',
                'attempt' => 0,
            ]);

            if ($async) {
                DeliverContactHookJob::dispatch($delivery->id);
            } else {
                (new DeliverContactHookJob($delivery->id))->handle();
            }
        }
    }
}
