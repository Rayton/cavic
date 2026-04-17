# AGENTS.md

## Purpose
This file defines the implementation plan for improving the admin UI/UX of this Laravel application. Any coding agent working on the admin experience should follow this plan unless the user explicitly overrides it.

## Project Context
- Application name: CAVIC
- Framework: Laravel
- Primary admin menu file: `resources/views/layouts/menus/admin.blade.php`
- Main layout with modal infrastructure: `resources/views/layouts/app.blade.php`
- Routes: `routes/web.php`
- Admin views: `resources/views/backend/admin/**`
- Styles: `public/backend/assets/css/styles.css`, `public/backend/assets/css/responsive.css`
- Existing modal infrastructure:
  - `#main_modal`
  - `#secondary_modal`

## Main Goal
Reduce admin complexity by:
- grouping related features into task-based modules
- reducing top-level sidebar clutter
- using modal popups for short CRUD flows
- keeping large workflows on full pages
- preserving existing routes and functionality during rollout
- making the admin experience competitive with strong microfinance platforms

## Current Scope
This plan is **admin-first**.
- Optimize the admin experience now.
- Do **not** redesign for teller, credit officer, branch manager, or collector as separate roles yet.
- However, structure the admin workspaces so role-specific versions can be derived later.

---

# Target Admin Navigation
Agents should aim for this top-level admin structure:

1. Dashboard
2. Action Center
3. Members
4. Loans
5. Finance
6. Reports
7. Administration
8. Messages

## Navigation Rules
- Do **not** keep action-only links in the sidebar.
- Move actions like `Add Member`, `Bulk Import`, `New Transaction`, and `Compose Message` to page-level buttons.
- Keep old route names working unless the user explicitly asks for route changes.
- Prefer additive refactors over destructive rewrites.

---

# Target Workspaces

## 1) Dashboard
Purpose: overview, risk visibility, and quick access.

Should contain:
- executive KPI cards
- operational quick actions
- pending counts
- exception alerts
- recent activity
- branch-aware performance summaries

Admin dashboard KPIs should eventually surface:
- total active members
- active borrowers
- total portfolio outstanding
- overdue amount
- PAR-style overdue exposure summary
- due today / overdue today
- disbursements this month
- deposits this month
- withdrawals this month
- cash / liquidity position

Should not contain:
- deep navigation duplication

## 2) Action Center
Purpose: daily operational work queue.

Tabs:
- Member Requests
- Pending Loans
- Deposit Requests
- Withdraw Requests
- Due Today / Upcoming Repayments
- Exceptions

Action Center should prioritize:
- items due today
- overdue items needing action
- pending approvals
- failed / incomplete operational items
- reconciliation mismatches or posting exceptions when implemented

## 3) Members
Tabs:
- All Members
- Onboarding / Requests
- KYC & Documents
- Branches
- Leaders
- Import
- Custom Fields

Members workspace should support lifecycle visibility:
- applicant / pending approval
- active member
- incomplete KYC
- dormant / inactive member
- member with active loans
- member with overdue exposure

## 4) Loans
Tabs:
- Pipeline
- Disbursements
- Repayments
- Due / Upcoming
- Collections
- Loan Products
- Approvals
- Calculator

Loans workspace should expose a lifecycle pipeline such as:
- Draft
- Submitted
- Under Review
- Approved
- Ready for Disbursement
- Disbursed / Active
- Overdue
- Closed
- Rejected

Collections inside Loans should support:
- due today
- overdue buckets
- follow-up status
- promise-to-pay notes later
- quick access to repayment posting

## 5) Finance
Tabs:
- Wallets
- Savings Accounts
- Teller / Cash Ops
- Cash Transactions
- Requests
- Expenses
- Banking
- Reconciliation
- Methods
- Interest

Finance should eventually support stronger microfinance operations such as:
- cash in / cash out monitoring
- branch cash position
- teller-like posting workflows inside admin for now
- end-of-day balancing checkpoints
- reconciliation and exception surfacing

## 6) Reports
Tabs:
- Executive KPIs
- Portfolio
- Collections
- Accounts
- Transactions
- Expenses
- Banking
- Branch Performance
- Revenue

Reports should answer real microfinance questions like:
- what is overdue and how much
- which branch is underperforming
- what was disbursed this period
- what was collected this period
- where is liquidity pressure appearing

## 7) Administration
Tabs:
- Users
- Roles & Permissions
- Settings
- Currency
- Notification Templates

## 8) Messages
Tabs:
- Inbox
- Sent

---

# Modal vs Full Page Rules

## Use Modal for Short CRUD
Agents should keep or convert these to modal-first where practical:
- Branches
- Leaders
- Currency
- Transaction Categories
- Expense Categories
- Savings Products
- Savings Accounts quick CRUD
- Bank Accounts
- Bank Transactions
- Member Documents
- Roles
- Custom Fields
- Guarantors
- Loan approver settings

## Use Detail Modal / Large Modal for Quick Review
- Deposit request detail
- Withdraw request detail
- Message preview
- Loan approval summary

## Keep as Full Page
Do not force these into small modals:
- Member create/edit/view
- Loan create/edit/view
- Loan product create/edit
- Transaction create/edit
- Deposit method create/edit
- Withdraw method create/edit
- Reports center
- Settings center
- Bulk import flows
- Wallet import workflows

---

# Implementation Priorities
Agents should generally implement in this order.

## Phase 1 - Safe foundation
1. Audit current menu/routes/views
2. Refactor sidebar into grouped modules
3. Preserve old routes
4. Keep existing screens working

## Phase 2 - New workspaces
1. Action Center
2. Reports Center
3. Administration workspace

## Phase 3 - Core module workspaces
1. Members workspace
2. Loans workspace
3. Finance workspace

## Phase 4 - Modal standardization
1. Standardize `ajax-modal`, `ajax-submit`, `ajax-screen-submit`
2. Convert short CRUD flows to modal-first
3. Add request detail modals

## Phase 5 - Polish
1. Shared page headers
2. Better breadcrumbs
3. Consistent tabs/filter bars/empty states
4. Responsive polish
5. Regression testing

---

# Git-Ready Backlog Summary
Agents may use these IDs in commits, branches, or task notes.

## Epic A - Discovery
- UX-001 Capture current admin navigation baseline
- UX-002 Create target information architecture document

## Epic B - Navigation
- UX-010 Refactor admin sidebar into grouped modules
- UX-011 Split admin menu into maintainable partials
- UX-012 Add active-state support for grouped navigation

## Epic C - Workspaces
- UX-020 Add Action Center route and controller
- UX-021 Build Action Center tabbed page
- UX-022 Add Members workspace route and controller
- UX-023 Build Members workspace
- UX-024 Add Loans workspace route and controller
- UX-025 Build Loans workspace
- UX-026 Add Finance workspace route and controller
- UX-027 Build Finance workspace
- UX-028 Add Reports Center route and controller
- UX-029 Build Reports Center
- UX-030 Add Administration workspace route and controller
- UX-031 Build Administration workspace
- UX-032 Add admin loan lifecycle pipeline and status model to workspace UX
- UX-033 Add collections management tab inside Loans
- UX-034 Add disbursement queue and readiness visibility
- UX-035 Add member onboarding and KYC visibility to Members workspace
- UX-036 Add teller / cash operations tab inside Finance
- UX-037 Add reconciliation and day-end visibility inside Finance
- UX-038 Add executive KPI dashboard layer for admin
- UX-039 Add branch performance and exception visibility across dashboard/reports

## Epic D - Modals
- UX-040 Audit existing modal-capable CRUD screens
- UX-041 Standardize modal form behavior
- UX-042 Convert category/config micro-CRUD to modal-first
- UX-043 Add detail modals for request review

## Epic E - Shared UI patterns
- UX-050 Support explicit breadcrumbs for workspace pages
- UX-051 Create reusable page header partial
- UX-052 Create shared module tabs/filter/empty state partials

## Epic F - Visual polish
- UX-060 Add workspace-specific UI styling
- UX-061 Improve mobile/small-screen behavior for workspaces

## Epic G - QA and cleanup
- UX-070 Audit route inconsistencies referenced in views
- UX-071 Run full admin regression test pass
- UX-072 Remove obsolete sidebar links after stabilization

---

# File-Level Guidance

## Core files likely to change
- `resources/views/layouts/menus/admin.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`
- `resources/views/backend/admin/**`
- `public/backend/assets/css/styles.css`
- `public/backend/assets/css/responsive.css`

## Recommended new files
### Menu partials
- `resources/views/layouts/menus/admin/dashboard.blade.php`
- `resources/views/layouts/menus/admin/action-center.blade.php`
- `resources/views/layouts/menus/admin/members.blade.php`
- `resources/views/layouts/menus/admin/loans.blade.php`
- `resources/views/layouts/menus/admin/finance.blade.php`
- `resources/views/layouts/menus/admin/reports.blade.php`
- `resources/views/layouts/menus/admin/administration.blade.php`
- `resources/views/layouts/menus/admin/messages.blade.php`

### Workspace views
- `resources/views/backend/admin/action_center/index.blade.php`
- `resources/views/backend/admin/member/workspace.blade.php`
- `resources/views/backend/admin/loan/workspace.blade.php`
- `resources/views/backend/admin/finance/index.blade.php`
- `resources/views/backend/admin/reports/index.blade.php`
- `resources/views/backend/admin/administration/index.blade.php`

### Shared partials
- `resources/views/layouts/others/page-header.blade.php`
- `resources/views/backend/admin/partials/module-tabs.blade.php`
- `resources/views/backend/admin/partials/filter-bar.blade.php`
- `resources/views/backend/admin/partials/quick-actions.blade.php`
- `resources/views/backend/admin/partials/empty-state.blade.php`

### Controllers
- `app/Http/Controllers/ActionCenterController.php`
- `app/Http/Controllers/MemberWorkspaceController.php`
- `app/Http/Controllers/LoanWorkspaceController.php`
- `app/Http/Controllers/FinanceHubController.php`
- `app/Http/Controllers/ReportCenterController.php`
- `app/Http/Controllers/AdministrationHubController.php`

---

# Route Mapping Principles
Agents should preserve current route families and remap navigation around them.

## Action Center sources
- `members.pending_requests`
- `members.accept_request`
- `members.reject_request`
- `loans.filter` with pending state
- `loan_approvals.*`
- `deposit_requests.*`
- `withdraw_requests.*`
- `loans.upcoming_loan_repayments`

## Members sources
- `members.*`
- `custom_fields.*` for members
- `branches.*`
- `leaders.*`
- `member_documents.*`

## Loans sources
- `loans.*`
- `loan_products.*`
- `loan_payments.*`
- `loan_collaterals.*`
- `guarantors.*`
- `loan_approvals.*`

## Finance sources
- `wallets.*`
- `savings_accounts.*`
- `savings_products.*`
- `transactions.*`
- `transaction_categories.*`
- `deposit_requests.*`
- `withdraw_requests.*`
- `expenses.*`
- `expense_categories.*`
- `bank_accounts.*`
- `bank_transactions.*`
- `deposit_methods.*`
- `withdraw_methods.*`
- `automatic_methods.*`
- `interest_calculation.*`

## Reports sources
- `reports.*`

## Administration sources
- `users.*`
- `roles.*`
- `permission.*`
- `settings.*`
- `currency.*`
- `email_templates.*`

## Messages sources
- `messages.*`

---

# UX Rules Agents Must Follow
- Prefer grouping by user task, not by database model.
- Prefer tabs inside workspaces over many sidebar links.
- Keep page-level CTAs near the data they affect.
- Avoid introducing new top-level menu items unless requested.
- Reuse existing Blade views where possible before creating large rewrites.
- Reuse existing modal infrastructure before inventing new modal systems.
- Keep tenant-aware routes and permissions intact.
- If a route is referenced in Blade but missing from `routes/web.php`, audit before changing behavior.
- Optimize for real microfinance admin workflows: onboarding, approvals, disbursement, repayment, collections, reconciliation, and branch visibility.
- Make overdue items, pending approvals, and operational exceptions highly visible.
- Treat statuses and aging buckets as first-class UX elements, not hidden filters.

---

# Breadcrumb Guidance
Current breadcrumbs are segment-based and weak for workspace pages.

Agents should improve this by supporting explicit breadcrumb arrays from controllers/views while preserving fallback behavior.

Primary file:
- `resources/views/layouts/others/breadcrumbs.blade.php`

---

# QA Checklist
Before marking work complete, agents should verify:
- admin sidebar loads correctly
- active/open states work
- all new workspace routes load
- old routes still work
- modal CRUD still submits correctly
- request approve/reject actions still work
- transaction forms still work
- reports still run
- settings tabs still save correctly
- responsive layout is not broken

---

# Definition of Done
A task or phase is complete when:
- the affected features are reachable
- the new UX grouping is clearer than before
- old route behavior is preserved unless intentionally changed
- modal/page behavior follows the rules above
- no major admin workflow is broken
- overdue work, approvals, disbursement readiness, and operational exceptions are easier to spot than in the current UI
- the admin experience feels closer to a real microfinance operations console than a generic CRUD backend

---

# Notes for Agents
- Start with the smallest safe change that improves structure.
- Favor incremental refactors over large risky rewrites.
- Do not remove legacy routes during the first pass.
- If uncertain, preserve function and improve organization first.
