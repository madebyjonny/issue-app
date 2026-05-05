<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class ProjectResourceSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('key', 'CRUE')
            ->orWhere('key', 'CRU')
            ->orWhere(fn ($q) => $q->whereRaw('LOWER(name) = ?', ['crue']))
            ->first();

        if (!$project) {
            $this->command->warn('CRUE project not found — skipping resource seeder.');
            return;
        }

        $resources = [
            [
                'name'    => 'UI Design Guidelines',
                'type'    => 'design',
                'content' => <<<'CONTENT'
## Philosophy
Light, minimal, and breathable — calm productivity UI. Think Linear or Notion, not Bootstrap.

## Visual Style
- Background: #f7f8f5 (warm off-white) for page bg; #ffffff for cards
- Accent: muted sage green (#3d6b58) for primary actions and active states
- Danger: muted coral (#c0392b) for destructive actions
- Text: #1a1c18 (near-black) for body; #6b7163 for secondary/muted text
- Borders: #e8eae5 (very light, warm gray)
- Card shadows: `0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.04)` (subtle)
- Rounded corners: 10px for cards, 6px for inputs/buttons/tags
- Generous whitespace — never crowded

## Typography
- Font: Inter, system-ui, sans-serif
- Body: 14px / 1.6 line-height
- Secondary: 12px, color: var(--color-text-muted)
- Headings: font-weight 600, tight letter-spacing
- No bold UI chrome — let whitespace do the work

## CSS Approach (IMPORTANT — No Tailwind)
Do NOT use Tailwind utility classes. Write component-scoped CSS.

**Global CSS** (`resources/css/global.css`) handles base HTML elements ONLY.
Base elements must look correct WITHOUT any class applied:
```css
button { background: var(--color-accent); color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
button:hover { background: var(--color-accent-hover); }
button[type="button"], button.secondary { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
input, textarea, select { border: 1px solid var(--color-border); border-radius: 6px; padding: 8px 12px; font-size: 14px; color: var(--color-text); background: #fff; width: 100%; }
input:focus, textarea:focus, select:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 3px rgba(61,107,88,0.12); }
label { font-size: 12px; font-weight: 600; color: var(--color-text-muted); display: block; margin-bottom: 4px; }
```

**Component CSS** lives in a `<style>` block inside the Blade component file, or a co-located CSS file (e.g. `resources/css/components/sidebar.css`).
Do NOT put component styles in global.css unless they apply everywhere.

## CSS Variables (define in :root in global.css)
```css
:root {
  --color-bg: #f7f8f5;
  --color-surface: #ffffff;
  --color-border: #e8eae5;
  --color-text: #1a1c18;
  --color-text-muted: #6b7163;
  --color-accent: #3d6b58;
  --color-accent-hover: #2e5243;
  --color-accent-light: #edf3f0;
  --color-danger: #c0392b;
  --radius: 10px;
  --radius-sm: 6px;
}
```

## Layout Patterns
- Sidebar: fixed width (~220px), light background (#f0f2ee), no harsh borders
- Main content: scrollable, comfortable padding (24–32px)
- Cards: white background, subtle shadow or `1px solid var(--color-border)`
- Forms: max-width 480–600px, stacked labels above inputs
CONTENT,
                'tickets' => ['CRUE-12', 'CRUE-13', 'CRUE-14'],
            ],

            [
                'name'    => 'Laravel Backend Conventions',
                'type'    => 'development',
                'content' => <<<'CONTENT'
## Stack
Laravel 13, PHP 8.4. No additional packages without discussion.

## Authentication
- Web routes: `auth` middleware (Breeze session-based)
- API routes: `auth:sanctum` middleware (token-based)
- MCP server only: `auth:api` (Passport) — do not use elsewhere

## Dual Response Pattern
Every controller method should handle both web and API requests:
```php
if ($request->expectsJson()) {
    return response()->json(['data' => $result]);
}
return redirect()->route('...');
```
API JSON envelope: `{ data, message, errors }`

## Eloquent
- Always eager-load relationships to avoid N+1 (`with([...])`)
- Use `firstOrCreate` / `updateOrCreate` in seeders
- Model factories required for every model
- Complex business logic goes in service classes, not controllers or model events

## Validation
- Use FormRequest classes for all validation, not inline `$request->validate()`
- API 422 errors return `{ errors: { field: [message] } }`

## Routes
- Web: `routes/web.php`
- API: `routes/api.php` (prefix `/api/v1/`)
- MCP: `routes/ai.php` — do not add non-MCP routes here

## Naming
- Controllers: `OrganisationController`, `BookingController` (singular)
- API resources: plural `/api/v1/organisations`, `/api/v1/bookings`
- Route names: `organisations.index`, `bookings.store`
CONTENT,
                'tickets' => ['CRUE-1', 'CRUE-2', 'CRUE-3', 'CRUE-4', 'CRUE-5', 'CRUE-6', 'CRUE-7', 'CRUE-8', 'CRUE-9', 'CRUE-10', 'CRUE-11', 'CRUE-12', 'CRUE-13', 'CRUE-14', 'CRUE-15', 'CRUE-16'],
            ],

            [
                'name'    => 'RRULE & Recurring Schedules',
                'type'    => 'development',
                'content' => <<<'CONTENT'
## Core Principle
Never store individual occurrence rows. A `Schedule` record stores one RRULE string.
Occurrences are resolved in-memory for a given date range.

## Library
Use `rlanvin/php-rrule` for RRULE parsing.

## Occurrence Identity
An occurrence is uniquely identified by `(schedule_id, occurrence_date)`.
`occurrence_date` is an ISO8601 date string in UTC: `"2026-05-12"`.
This tuple is used as the FK for bookings and waitlist entries — not a separate occurrences table.

## Booking Schema
```
bookings: id, schedule_id, occurrence_date (date), user_id, status, created_at, updated_at
```
No `event_id` — schedules ARE the events.

## Resolving Occurrences
```php
$rrule = new RRule($schedule->rrule);
$occurrences = $rrule->getOccurrencesBetween($startDate, $endDate);
```
Always cap the range (e.g. max 90 days ahead) to avoid runaway resolution.
Cache resolved occurrences within a single request when multiple components need them.

## Timezones
Store DTSTART in UTC in the RRULE string.
Display times in the organisation's configured timezone (stored on `organisations.timezone`).
Use Carbon for conversion: `Carbon::parse($utcDate)->setTimezone($org->timezone)`.

## RRULE Examples
Weekly on Tuesdays and Thursdays at 09:00 UTC:
`DTSTART:20260101T090000Z\nRRULE:FREQ=WEEKLY;BYDAY=TU,TH`

Every weekday, 6am UTC:
`DTSTART:20260101T060000Z\nRRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR`
CONTENT,
                'tickets' => ['CRUE-7', 'CRUE-8', 'CRUE-9', 'CRUE-10', 'CRUE-11'],
            ],

            [
                'name'    => 'Stripe & Billing',
                'type'    => 'development',
                'content' => <<<'CONTENT'
## Setup
Laravel Cashier (Stripe) handles billing. Two separate billing flows exist:

## Flow 1 — Org pays Crue (Platform Subscription)
The `Organisation` model is Billable (uses Cashier).
Organisations subscribe to a Crue plan to access the platform.
Handled via standard Cashier checkout sessions.

## Flow 2 — Subscriber pays Org (Class Bookings)
Use Stripe Connect (Express accounts).
The organisation's connected account processes the payment.
Crue takes a configurable platform fee via `application_fee_amount`.

## Stripe Connect Setup
```php
// Store on organisations table
$org->stripe_account_id = $onboardingResult->account_id;

// When creating a booking checkout session
$session = \Stripe\Checkout\Session::create([
    'payment_intent_data' => [
        'application_fee_amount' => $this->platformFeeInCents($amount),
        'transfer_data' => ['destination' => $org->stripe_account_id],
    ],
    ...
]);
```

## Platform Fee
Configurable: `config('billing.platform_fee_percent')` — never hardcode.
Formula: `$feeInCents = (int) round($amountInCents * config('billing.platform_fee_percent') / 100)`

## Webhooks
Handle in `StripeWebhookController`. Always verify the webhook signature.
Key events to handle:
- `invoice.payment_succeeded` → renew class allowance, update subscription status
- `customer.subscription.deleted` → mark org subscription inactive
- `customer.subscription.updated` → sync plan/status changes
- `checkout.session.completed` → confirm one-off booking payments
- `account.updated` → sync Connect account status (charges_enabled)

## Membership Gating
Before allowing a booking, check:
1. Subscriber has an active membership subscription for this org
2. The subscription plan allows this class type
3. If capacity is full → add to waitlist, return `error_code: capacity_full`
CONTENT,
                'tickets' => ['CRUE-4', 'CRUE-5', 'CRUE-6', 'CRUE-11', 'CRUE-15'],
            ],
        ];

        foreach ($resources as $data) {
            $ticketIdentifiers = $data['tickets'];
            unset($data['tickets']);

            $resource = ProjectResource::updateOrCreate(
                ['project_id' => $project->id, 'name' => $data['name']],
                $data
            );

            $ticketIds = Ticket::where('project_id', $project->id)
                ->whereIn('identifier', $ticketIdentifiers)
                ->pluck('id')
                ->all();

            $resource->tickets()->sync($ticketIds);

            $this->command->info("Resource '{$resource->name}' created/updated with " . count($ticketIds) . " ticket(s) attached.");
        }
    }
}
