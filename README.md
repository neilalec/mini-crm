# Mini CRM

Mini CRM is a lightweight Laravel and Vue application for capturing customer enquiries, tracking lead status and keeping follow-up activity in one place.

I built this as a portfolio project to practice a PHP-first full-stack workflow with Laravel, Inertia, Vue, SQL migrations, authentication, validation and feature tests. I am continuing to shape the project based on feedback and practical use cases for small businesses.

## Features

- Public enquiry forms for individual businesses.
- Authenticated lead inbox with search, status, follow-up and contact-state filters.
- Lead detail page with status, quote amount and follow-up date updates.
- Customer chat link for each lead so a customer can continue the conversation.
- Internal notes, outgoing messages and reply templates.
- Activity timeline for enquiries, messages, notes, status changes, quote updates and follow-up changes.
- Broadcast events for lead, message, note, template and activity updates.
- Feature tests for the core lead workflow.

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Breeze authentication
- Inertia.js
- Vue 3
- Tailwind CSS
- Vite
- SQLite by default for local development
- PHPUnit

## Project Structure

```text
app/
  Http/Controllers/       Lead, enquiry, chat, profile and template controllers
  Models/                 Business, Lead, LeadActivity, LeadMessage, LeadNote and ReplyTemplate
  Events/                 Broadcast events used by the UI

database/
  migrations/             SQL schema for users, businesses, leads, messages, notes and activity
  seeders/                Demo user, business, leads and templates

resources/js/
  Pages/                  Inertia Vue pages
  Components/             Shared Vue UI components

tests/
  Feature/LeadWorkflowTest.php
  Feature/Auth/
```

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js 20.19 or newer
- npm
- SQLite, MySQL or another Laravel-supported database

## Setup

1. Install PHP and JavaScript dependencies.

```bash
composer install
npm install
```

2. Create the environment file and application key.

```bash
cp .env.example .env
php artisan key:generate
```

3. For a simple local SQLite setup, create the database file and set `DB_CONNECTION=sqlite` in `.env`.

```bash
touch database/database.sqlite
```

4. Run migrations and seed demo data.

```bash
php artisan migrate --seed
```

5. Start the application.

```bash
composer run dev
```

You can also run the Laravel and Vite servers separately:

```bash
php artisan serve
npm run dev
```

## Demo Data

The database seeder creates a demo account and a sample business.

```text
Email: test@example.com
Password: password
Business enquiry URL: /northside-plumbing/enquire
```

## Testing

```bash
php artisan test
```

The lead workflow tests cover public enquiry creation, next-working-day follow-up dates, lead inbox filters and activity records created when a lead is updated.

## Notes

- Actively being developed, not a production CRM.
- Mail and broadcasting use Laravel's normal configuration, so local behaviour depends on your `.env` settings.
- The customer chat link is token-based for simplicity; a production application would need a fuller security review around access, expiry and audit requirements.
