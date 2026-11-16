# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [2.0.0] — 2026-11-12

Complete rewrite of the module.

### Added
- Real notification storage using Laravel's database notifications (the previous version returned hard-coded data).
- 7 typed business events (`NotificationType` enum) with severity levels and per-type required data.
- Per-user, per-event preferences for **in-app** and **e-mail** delivery, with sensible defaults.
- Token authentication with Laravel Sanctum (login, logout, profile).
- Inbox API: pagination, status/type filters, unread count, mark read/unread, mark all as read, delete.
- Admin-only endpoint to dispatch alerts to one or many users.
- English and French translations for messages, labels, e-mails and validation errors.
- React dashboard: login, inbox with unread badge, preferences matrix, EN/FR switch.
- 59 backend tests (PHPUnit) and 36 frontend tests (Vitest + Testing Library).
- GitHub Actions CI, Pint code style, ESLint, demo seeder, bilingual README.

### Changed
- Backend upgraded from Laravel 9 to Laravel 13 (PHP 8.3+).
- Frontend migrated from Create React App to Vite.

### Fixed
- Undefined `trigger` / `markAsRead` routes, duplicated routes, unauthenticated endpoints.
- Mass-assignment of the raw request in the preferences controller.
- Conflict between the custom `notifications()` relation and Laravel's `Notifiable` trait.
- Preferences page posting to the wrong URL and never loading saved values.

## [1.0.0]

- Initial prototype.
