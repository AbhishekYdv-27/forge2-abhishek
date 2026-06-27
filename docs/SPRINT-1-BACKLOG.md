# PulseDesk — Sprint 1 Backlog

**Sprint Goal:** Ship a working multi-tenant support-desk API with auth, ticket CRUD, and comments.
**Repo:** `backend/` (Laravel 11 + Sanctum)

---

## Current State Assessment

Routes (`routes/api.php`) and all three controllers are **already implemented**, but the code contains a **critical schema mismatch** with the database migrations. The controllers will crash at runtime until fixed. The real sprint work is reconciliation + hardening, not greenfield implementation.

### Schema vs. Code Mismatch Summary

| Concept | Migration (source of truth) | Model/Controller (broken) |
|---|---|---|
| Ticket requester FK | `requester_id` | `customer_id` ❌ |
| Ticket assignee FK | `assignee_id` | `assigned_to` ❌ |
| Ticket title field | `subject` | `title` ❌ |
| Ticket status enum | `open, pending, resolved, closed` | `open, in_progress, resolved, closed` ❌ |
| Ticket tags column | `tags` (json, nullable) | not in `$fillable` ❌ |
| Comment internal flag | `is_internal` (boolean) | not in `$fillable` / not validated ❌ |
| ActivityLog model | has `ticket_id`, `user_id`, `action`, `description` | completely empty stub ❌ |
| User relationships | — | foreign keys reference non-existent columns ❌ |

---

## Issue 1 — P0 — Fix Ticket schema mismatch (Model + Controller) ✅ COMPLETE

**Assignee:** OpenClaw
**Status:** ✅ Complete — commit `b569ec1` on `main`. `migrate:fresh --seed` clean.
**Labels:** bug, backend, sprint-1, blocked-everything

The `tickets` migration defines `requester_id`, `assignee_id`, `subject`, status enum `['open','pending','resolved','closed']`, and a `tags` JSON column. The `Ticket` model and `TicketController` reference `customer_id`, `assigned_to`, `title`, and status `['open','in_progress','resolved','closed']`. Every ticket store/show/update query will fail with a SQL error.

**Files edited:**
- `app/Models/Ticket.php`
- `app/Http/Controllers/Api/TicketController.php`
- *(collateral: `User.php`, `Organization.php`, `Comment.php` — partly satisfies Issue 2)*

**Acceptance criteria:**
- [x] Ticket `$fillable` uses `requester_id`, `assignee_id`, `subject` (not `customer_id`, `assigned_to`, `title`)
- [x] Ticket relationships renamed: `requester()` → `belongsTo(User::class, 'requester_id')`, `assignee()` → `belongsTo(User::class, 'assignee_id')`
- [x] `tags` added to `$fillable` with `'tags' => 'array'` in `$casts`
- [x] TicketController `index()`, `show()`, `store()`, `update()` updated to use correct field names
- [x] `store()` sets `requester_id` from auth user, validates `subject` + `description` + `priority`
- [x] `update()` validates status as `open, pending, resolved, closed`
- [x] `index()` eager-loads `requester` and `assignee` (not `customer`/`agent`)
- [x] `show()` eager-loads `requester`, `assignee`, `comments.user`

**⚠️ Note:** The fix replaced Laravel 11's `casts()` method on `User` with a `$casts` property, dropping `'password' => 'hashed'`. Not currently breaking (`AuthController` calls `Hash::make()` explicitly) but is a latent footgun. Rolled into Issue 2.

---

## Issue 2 — P0 — Fix User & Organization model relationships (re-scoped)

**Assignee:** OpenClaw
**Status:** 🔵 Ready to assign — reduced scope after Issue 1 collateral fixes
**Labels:** bug, backend, sprint-1

User model relationships reference non-existent ticket columns. They must match the migration schema.

**Partly done by Issue 1 commit `b569ec1` (verified — do not redo):**
- [x] `User::tickets()` foreign key changed `customer_id` → `requester_id`
- [x] `User::assignedTickets()` foreign key changed `assigned_to` → `assignee_id`
- [x] `Organization::tickets()` and `Organization::users()` correct

**Remaining tasks:**
1. Add `User::comments()` → `return $this->hasMany(Comment::class);`
2. Add `User::activityLogs()` → `return $this->hasMany(ActivityLog::class);`
3. **Restore password auto-hashing** on `User` — replace the `$casts` property with the Laravel 11 `casts()` method:
   ```php
   protected function casts(): array {
       return [
           'email_verified_at' => 'datetime',
           'password' => 'hashed',
       ];
   }
   ```
   (Fixes the regression introduced in `b569ec1` where `'password' => 'hashed'` was dropped.)

**DoD:**
- [ ] `User::first()->comments` resolves without error in `tinker`
- [ ] `User::first()->activityLogs` resolves without error in `tinker`
- [ ] `password` is auto-hashed on mass assignment (verify via a test or `tinker`)
- [ ] `php artisan migrate:fresh --seed` clean

**Files to edit:**
- `app/Models/User.php`

---

## Issue 3 — P1 — Complete ActivityLog model

**Labels:** feature, backend, sprint-1

`ActivityLog` is an empty stub (`//` body only). It needs fillable fields and relationships to be usable.

**Files to edit:**
- `app/Models/ActivityLog.php`

**Acceptance criteria:**
- [ ] `$fillable` = `['ticket_id', 'user_id', 'action', 'description']`
- [ ] `ticket()` → `belongsTo(Ticket::class)`
- [ ] `user()` → `belongsTo(User::class)`

---

## Issue 4 — P1 — Add `is_internal` comment support

**Labels:** feature, backend, sprint-1

The `comments` migration has an `is_internal` boolean (default false), but the Comment model and CommentController ignore it entirely.

**Files to edit:**
- `app/Models/Comment.php`
- `app/Http/Controllers/Api/CommentController.php`

**Acceptance criteria:**
- [ ] `is_internal` added to Comment `$fillable`
- [ ] CommentController `store()` validates `'is_internal' => 'sometimes|boolean'` and passes it through
- [ ] Only `agent`/`admin` roles can create internal comments (customers cannot) — add a role check

---

## Issue 5 — P2 — Model factories & database seeders

**Labels:** tooling, testing, sprint-1

No factories or seeders exist. Needed to test the API endpoints end-to-end.

**Acceptance criteria:**
- [ ] Factories for `Organization`, `User`, `Ticket`, `Comment`
- [ ] `DatabaseSeeder` creates: 1 org → 1 admin + 2 agents + 3 customers → 10 tickets → 20 comments
- [ ] `php artisan migrate:fresh --seed` runs clean

---

## Issue 6 — P2 — Form Request classes & API Resources

**Labels:** refactor, backend, sprint-1

Inline validation is scattered across controllers. Extract to Form Requests and add API Resources for consistent JSON shaping.

**Acceptance criteria:**
- [ ] `StoreTicketRequest`, `UpdateTicketRequest`, `StoreCommentRequest`, `RegisterRequest`
- [ ] `TicketResource`, `CommentResource`, `UserResource`
- [ ] Controllers return `Resource` / `JsonResource` instead of raw `response()->json()`
