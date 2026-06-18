# Role Access Fixes Design

## Goal

Fix the role and access-control findings from E2E review with the smallest safe change set:

- Production boot must not fail when HTTPS forcing runs.
- `backend-operator` must not view transactions or receipts.
- `backend-analyst` must be able to view Roles in the admin panel, but not mutate roles.
- Maya sandbox CAPTCHA is treated as an external automation limit, not an app bug.

## Decisions

- `backend-operator` is not allowed to view Transactions or Receipts.
- `backend-analyst` is allowed to view Roles read-only in the UI.
- Maya full payment completion stays manual because hosted sandbox reCAPTCHA blocks headless automation.

## Design

### Production URL Facade

Add the missing `Illuminate\Support\Facades\URL` import in `App\Providers\AppServiceProvider`.
This keeps the existing production-only `URL::forceScheme('https')` behavior and fixes the missing class reference.

### Backend Operator Access

Remove `can view dashboard analytics` from the `backend-operator` role definition in the role-permission migration/matrix.
`TransactionPolicy` and `ReceiptPolicy` already depend on `can view dashboard analytics`, so removing that permission blocks operator access without changing policy code.

The existing `PolicyEnforcementTest` becomes the source of truth: backend admin can view settings/reports; backend operator cannot.

### Backend Analyst Role Viewing

Change `RoleResource::canAccess()` so backend users with `can view role` can reach the Roles resource.
Mutation stays guarded by `RolePolicy`:

- `create` requires `can create role`.
- `update` requires `can update role`.
- `delete` requires `can delete role`.
- protected roles stay non-deletable.

This makes analyst access consistent with the existing `can view role` permission while keeping privilege-escalation controls.

### Maya Sandbox

No application code change.
The E2E report should state that Maya redirect and card form load are automated, but final payment requires hosted reCAPTCHA and must remain a manual sandbox step unless a fake local gateway is added later.

## Tests

Run targeted tests after implementation:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl tests/Feature/Pos/CashCheckoutTest.php
```

Add or update access-control coverage so it proves:

- `backend-operator` cannot view `Transaction` or `Receipt`.
- `backend-analyst` can access the Roles resource/page.
- `backend-analyst` cannot create, update, or delete roles.
- `backend-admin` still has full role-management access.

## Scope Limits

- Do not split report permissions into new granular permissions in this change.
- Do not add a fake Maya gateway in this change.
- Do not rewrite Filament resources outside the role access checks needed here.
