# Admin module UI refinement — 16 October 2026

## Scope

Unify 23 admin module views with the existing admin workspace, without changing controllers, routes, database, payment calculations, shipping rates, or integration payloads.

- Shared, escaped page heading partial and admin-only module stylesheet.
- Shipping sections: methods/origin, internal delivery coverage, and shipping subsidy. Existing POST forms remain independent; origin and coverage fields receive associated labels.
- Consistent product, stock, category images, flash sale, voucher, redeem, article, banner, invoice-list, message-list, traffic, and theme settings layouts.
- Compact typography, neutral themed surfaces, mobile form stacking, horizontally scrollable data tables, and bounded legacy dialog surfaces.
- Shared article view retains the public visitor hero; only admin receives the new heading.
- Redeem edit/delete buttons had no handlers before this change; they are explicitly disabled and labelled as unavailable rather than presented as working actions.

Standalone printed invoices and exported product tables are excluded. No SQL or environment changes are required.

## Verification

- Full PHPUnit suite: 314 tests, 2,022 assertions passed.
- JavaScript regression suite: 14 tests passed.
- PHP lint for changed views, shared partial, and new test passed.
- Form contract fixture checks actions, methods, encoding and named field/default expressions across 24 view sources against the pre-change baseline.
- Synthetic render tests cover 23 modules; no production database or API requests are used.
- Isolated Chrome checks: 23 synthetic pages at 1440px and 390px viewport widths; no document horizontal overflow in all 46 layouts. Shipping and flash sale screenshots were visually inspected.
- These are local fixture checks, not a claim of authenticated production end-to-end testing.

## Rollback

Revert this UI commit and deploy through the normal process. There are no migrations, data conversions, or database rollback steps. Existing admin workspace/navigation assets from the preceding commit remain separate.
