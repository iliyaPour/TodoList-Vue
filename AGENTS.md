# AGENTS.md

## Stack

Laravel 13 + Vue 3 + Inertia.js + Tailwind CSS v4 + shadcn-vue (new-york-v4). PHPStan level 7. SSR enabled.

## Key commands

```bash
composer setup          # install + .env + key:generate + migrate + npm install + build
composer dev            # artisan dev (Vite + HMR)
composer test           # config:clear → pint --parallel --test → phpstan analyse → artisan test
composer ci:check       # npm lint:check → npm format:check → npm types:check → phpunit
composer lint           # pint --parallel (auto-fix PHP)
npm run lint            # eslint --fix (TS/Vue)
npm run format          # prettier --write resources/
npm run types:check     # vue-tsc --noEmit
php artisan test        # PHPUnit (Unit + Feature suites, SQLite :memory:)
```

Single test: `php artisan test --filter=TestName` or `php vendor/bin/phpunit tests/Feature/DashboardTest.php`.

## Architecture

- **Routes**: `routes/web.php` — Inertia renders Vue pages from `resources/js/pages/`
- **Controllers**: `app/Http/Controllers/` — `ListController`, `TaskController` (empty)
- **Models**: `app/Models/` — `TodoList` (table `lists`), `Task`, `User`
- **Pages**: `resources/js/pages/` — Vue SFC, directory matches route name (`lists/index.vue`)
- **Generated (gitignored)**: `resources/js/routes/`, `resources/js/actions/`, `resources/js/wayfinder/` — run `npm run build` or `composer dev` to regenerate via Wayfinder
- **Layouts**: `AppLayout.vue`, `AuthLayout.vue`, `settings/Layout.vue` — selected by name in `resources/js/app.ts`
- **UI**: shadcn-vue components in `resources/js/components/ui/` (gitignored from lint/format)
- **Utils**: `cn()` helper in `resources/js/lib/utils.ts` (clsx + tailwind-merge)

## Conventions

- PHP: Pint with `laravel` preset (`pint.json`)
- TS/Vue: ESLint with vue-ts + import ordering (alphabetical), `@stylistic` brace style (1tbs), `curly: all`
- Prettier: 4-space indent, single quotes, semicolons, tailwindcss plugin sorts classes
- `.editorconfig`: LF line endings, 4-space indent, 2-space for YAML
- Type imports: `import type` required (enforced by ESLint)
- ESLint ignores: `vendor`, `node_modules`, `public`, `bootstrap/ssr`, `resources/js/actions/**`, `resources/js/components/ui/*`, `resources/js/routes/**`, `resources/js/wayfinder/**`

## Testing

- PHPUnit with SQLite in-memory (`phpunit.xml`)
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` set in phpunit.xml env
- Feature tests in `tests/Feature/`, Unit in `tests/Unit/`
- `composer test` runs lint + types + tests together

## Gotchas

- `pnpm-workspace.yaml` exists but `composer setup` runs `npm install` — use npm
- `.npmrc` has `ignore-scripts=true`
- Wayfinder generates typed routes/actions — regenerate after route or controller changes with `npm run build`
- Tailwind v4 uses CSS-first config (`resources/css/app.css`), not `tailwind.config.js` (referenced in `components.json` but unused)
- Inertia SSR is enabled (`config/inertia.php`) — `npm run build:ssr` for SSR bundle
- Database defaults to SQLite — no external DB needed for local dev/tests
