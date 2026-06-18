# Role Access Fixes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix production URL facade boot, backend operator report access, backend analyst read-only Roles access, and document the Maya sandbox CAPTCHA limitation.

**Architecture:** Keep the existing Spatie Permission and Filament policy model. Change role permissions at the seeded/migration matrix, keep report policies unchanged, and make the Roles Filament resource access match the existing `can view role` permission while mutation remains policy-gated.

**Tech Stack:** Laravel 12, Filament v5, Spatie Laravel Permission, PHPUnit/Pest-style Laravel feature tests, Docker/Sail container.

---

## File Structure

- `app/Providers/AppServiceProvider.php`: imports `URL` facade used by production HTTPS forcing.
- `database/migrations/2026_06_17_000001_redefine_role_permissions.php`: defines role-permission matrix; removes dashboard analytics from `backend-operator`.
- `app/Filament/Resources/Roles/RoleResource.php`: controls whether the Roles resource is visible/reachable in Filament.
- `tests/Feature/AccessControl/PolicyEnforcementTest.php`: verifies operator report/settings blocking.
- `tests/Feature/AccessControl/RoleManagementAccessTest.php`: verifies admin and analyst role-resource behavior.
- `pos-e2e-findings.pdf`: final report artifact updated after fixes.

---

### Task 1: Fix Production URL Facade Import

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `app/Providers/AppServiceProvider.php` syntax check

- [ ] **Step 1: Write the minimal failing check**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php -r "require 'vendor/autoload.php'; require 'app/Providers/AppServiceProvider.php'; echo class_exists(App\\Providers\\AppServiceProvider::class) ? 'loaded'.PHP_EOL : 'missing'.PHP_EOL;"
```

Expected before fix:

```text
loaded
```

This only proves the class loads. The defect is static: `URL::forceScheme()` is referenced without an import. The implementation step fixes that reference.

- [ ] **Step 2: Add the missing import**

In `app/Providers/AppServiceProvider.php`, add this import near the other facades:

```php
use Illuminate\Support\Facades\URL;
```

Expected import block includes:

```php
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
```

- [ ] **Step 3: Verify syntax**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php -l app/Providers/AppServiceProvider.php
```

Expected:

```text
No syntax errors detected in app/Providers/AppServiceProvider.php
```

- [ ] **Step 4: Commit**

Run:

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "fix: import URL facade"
```

---

### Task 2: Block Backend Operator From Reports

**Files:**
- Modify: `database/migrations/2026_06_17_000001_redefine_role_permissions.php`
- Test: `tests/Feature/AccessControl/PolicyEnforcementTest.php`

- [ ] **Step 1: Run current failing access-control test**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl/PolicyEnforcementTest.php
```

Expected before fix:

```text
FAILED  Tests\Feature\AccessControl\PolicyEnforcementTest > settings and reports are gated by permission
A non-privileged backend user must not view App\Models\Transaction
```

- [ ] **Step 2: Remove analytics permission from backend operator**

In `database/migrations/2026_06_17_000001_redefine_role_permissions.php`, replace this block:

```php
'backend-operator' => [
    'can access backend panel',
    ...$this->crud('pos item'),
    ...$this->crud('pos category'),
    'can view dashboard analytics', // enables Transactions, Receipts, Statistics
],
```

with:

```php
'backend-operator' => [
    'can access backend panel',
    ...$this->crud('pos item'),
    ...$this->crud('pos category'),
],
```

Do not change `TransactionPolicy` or `ReceiptPolicy`; they already require `can view dashboard analytics`.

- [ ] **Step 3: Run access-control test**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl/PolicyEnforcementTest.php
```

Expected:

```text
PASS  Tests\Feature\AccessControl\PolicyEnforcementTest
✓ settings and reports are gated by permission
```

- [ ] **Step 4: Commit**

Run:

```bash
git add database/migrations/2026_06_17_000001_redefine_role_permissions.php
git commit -m "fix: block operators from reports"
```

---

### Task 3: Let Backend Analysts View Roles Read-Only

**Files:**
- Modify: `app/Filament/Resources/Roles/RoleResource.php`
- Modify: `tests/Feature/AccessControl/RoleManagementAccessTest.php`

- [ ] **Step 1: Update tests first**

In `tests/Feature/AccessControl/RoleManagementAccessTest.php`, replace the `test_non_super_admin_cannot_access_role_resource` test with these two tests:

```php
public function test_backend_analyst_can_access_role_resource_read_only(): void
{
    $analyst = $this->backendUser('backend-analyst');

    $this->actingAs($analyst, 'backend');

    $this->assertTrue(RoleResource::canAccess());
    $this->assertTrue($analyst->can('can view role'));
    $this->assertFalse($analyst->can('can create role'));
    $this->assertFalse($analyst->can('can update role'));
    $this->assertFalse($analyst->can('can delete role'));
}

public function test_backend_operator_cannot_access_role_resource(): void
{
    $this->actingAs($this->backendUser('backend-operator'), 'backend');

    $this->assertFalse(RoleResource::canAccess());
}
```

- [ ] **Step 2: Run test to verify analyst case fails**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl/RoleManagementAccessTest.php
```

Expected before implementation:

```text
FAILED  Tests\Feature\AccessControl\RoleManagementAccessTest > backend analyst can access role resource read only
Failed asserting that false is true.
```

- [ ] **Step 3: Update RoleResource access**

In `app/Filament/Resources/Roles/RoleResource.php`, replace `canAccess()`:

```php
public static function canAccess(): bool
{
    $user = Auth::guard('backend')->user();

    return $user instanceof BackendUser && $user->hasRole('backend-admin');
}
```

with:

```php
public static function canAccess(): bool
{
    $user = Auth::guard('backend')->user();

    return $user instanceof BackendUser && $user->can('can view role');
}
```

Also update the comment above it to:

```php
/**
 * Users with role-view permission may reach the Roles resource. Mutating
 * actions remain policy-gated, so analysts can inspect roles without gaining
 * privilege-escalation paths.
 */
```

- [ ] **Step 4: Run role management tests**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl/RoleManagementAccessTest.php
```

Expected:

```text
PASS  Tests\Feature\AccessControl\RoleManagementAccessTest
✓ super admin can access role resource
✓ backend analyst can access role resource read only
✓ backend operator cannot access role resource
✓ protected system role cannot be deleted
✓ non protected role can be deleted by super admin
```

- [ ] **Step 5: Commit**

Run:

```bash
git add app/Filament/Resources/Roles/RoleResource.php tests/Feature/AccessControl/RoleManagementAccessTest.php
git commit -m "fix: allow analysts to view roles"
```

---

### Task 4: Run Full Targeted Verification

**Files:**
- No source modifications expected.

- [ ] **Step 1: Run targeted suite**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan test tests/Feature/AccessControl tests/Feature/Pos/CashCheckoutTest.php
```

Expected:

```text
PASS  Tests\Feature\AccessControl\PolicyEnforcementTest
PASS  Tests\Feature\AccessControl\RoleManagementAccessTest
PASS  Tests\Feature\Pos\CashCheckoutTest
```

- [ ] **Step 2: Verify effective role matrix**

Run:

```bash
docker exec pos-management-system-web-laravel.test-1 php artisan tinker --execute='foreach (App\Models\BackendUser::with("roles")->orderBy("email")->get() as $u) { echo $u->email.": ".$u->roles->pluck("name")->implode(", ").PHP_EOL; }'
```

Expected seeded backend users include:

```text
backend-admin@test.com: backend-admin
backend-analyst@test.com: backend-analyst
backend-operator@test.com: backend-operator
backend-support@test.com: backend-support
```

- [ ] **Step 3: Commit if verification changed tracked artifacts**

If no files changed, do not commit.

If a tracked report or verification artifact is intentionally updated, run:

```bash
git add <changed-artifact>
git commit -m "test: update role access findings"
```

---

### Task 5: Update E2E PDF Findings

**Files:**
- Modify/Create: `pos-e2e-findings.pdf`

- [ ] **Step 1: Update report content**

Regenerate the E2E PDF so it no longer lists the fixed findings as open issues:

- `AppServiceProvider` URL import: resolved.
- `backend-operator` report access: resolved.
- `backend-analyst` Roles view: resolved as read-only.
- Maya sandbox CAPTCHA: expected external limitation remains.

Use the existing local report generation workflow from `/tmp/pos-e2e` if still present. If not present, make the smallest local throwaway script under `/tmp` and write only the final PDF into the repo.

- [ ] **Step 2: Verify PDF exists**

Run:

```bash
file pos-e2e-findings.pdf
```

Expected:

```text
pos-e2e-findings.pdf: PDF document
```

- [ ] **Step 3: Commit PDF separately**

Run:

```bash
git add pos-e2e-findings.pdf
git commit -m "docs: update e2e findings"
```

---

## Self-Review Notes

- Spec coverage: URL import is Task 1; operator blocking is Task 2; analyst read-only Roles access is Task 3; Maya limitation and PDF update are Task 5; verification is Task 4.
- Scope check: no permission split, no fake Maya gateway, no unrelated Filament refactor.
- Commit strategy: each task has its own commit, matching user request.
