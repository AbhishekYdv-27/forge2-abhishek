# Sprint 01 -- Laravel 11 backend with multi-tenancy, auth and database

Goal: Stand up a working Laravel 11 + MySQL + Sanctum backend with org-scoped
multi-tenancy, a role-based user model, auth API endpoints, and a seeded
dataset (1 org, 5 users, 12 tickets) that `php artisan migrate --seed` builds
from a fresh clone.

Models: Hermes=<planning / product owner>, OpenClaw=<coding / glm-5.1>

## Issues
- [ ] #1 Laravel 11 fresh install with Sanctum in the backend/ folder
- [ ] #2 Database migrations: organizations, users (with roles), tickets, comments, activity_logs
- [ ] #3 Multi-tenant middleware (scope every query by organization_id)
- [ ] #4 Auth API: register, login, logout endpoints
- [ ] #5 Database seeders: 1 org, 1 admin, 2 agents, 2 customers, 12 tickets

## Outcome
- Shipped: ...
- Slipped / moved to next sprint: ...
- PRs: #... (merged by me)
