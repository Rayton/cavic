# Target Information Architecture

## Purpose
This document defines the desired future admin information architecture for **CAVIC**. It should guide navigation refactors, workspace creation, modal decisions, and future UI consistency work.

This architecture is intentionally additive and backward-compatible in early phases: improve navigation first, preserve current routes, then consolidate workspaces.

---

# 1. Design Goals

The target admin UX should:
- reduce sidebar clutter
- group features by user task, not table/model
- centralize pending work into one place
- convert short CRUD tasks to modal-first flows
- keep large, high-risk workflows on full pages
- preserve current functionality during transition
- improve discoverability and consistency
- reflect real microfinance operations such as onboarding, approvals, disbursement, collections, reconciliation, and branch visibility
- make overdue exposure, pending work, and exceptions impossible to miss

## Scope note
This target architecture is **admin-first for now**.
Separate role-based experiences for teller, credit officer, branch manager, or collector can be added later. The current goal is to make the **admin experience alone** reach a much higher microfinance UX standard.

---

# 2. Target Top-Level Admin Navigation

The future admin sidebar should contain only these major destinations:

1. Dashboard
2. Action Center
3. Members
4. Loans
5. Finance
6. Reports
7. Administration
8. Messages

## Navigation rules
- Sidebar should contain **modules**, not one-off actions.
- Actions like `Add Member`, `New Transaction`, `Bulk Import`, and `Compose` should appear as page-level buttons.
- Badge counts should appear only on actionable queues or important attention items.
- Similar workflows should be grouped together even if they currently use different controllers/routes.

---

# 3. Target Module Definitions

## 3.1 Dashboard
### Purpose
Provide overview, alerts, risk visibility, and entry points to important work.

### Should include
- executive KPI cards
- operational KPI cards
- pending counts
- quick actions
- recent activity
- exception alerts
- branch-aware performance highlights

### Recommended admin KPI layer
The admin dashboard should eventually expose at least:
- total active members
- active borrowers
- portfolio outstanding
- overdue amount
- PAR-style overdue exposure summary
- due today
- overdue today
- disbursements this month
- collections this month
- deposits this month
- withdrawals this month
- liquidity / cash position
- branch with highest exception load

### Should not include
- duplicated deep navigation for every module

### Suggested quick actions
- Add Member
- New Loan
- Post Repayment
- New Transaction
- Add Expense
- Open Reports

---

## 3.2 Action Center
### Purpose
Single place for operational queues and tasks requiring attention.

### Tabs
- Member Requests
- Pending Loans
- Deposit Requests
- Withdraw Requests
- Due Today / Upcoming Repayments
- Exceptions

### UX behavior
- approve/reject actions should be near each row
- details should open in modal or large modal where possible
- counts should match sidebar badges
- items due today should be visually prioritized over generic pending queues
- exceptions should include failed postings, reconciliation mismatches, incomplete documents, or other high-friction admin blockers once implemented

### Primary source routes
- `members.pending_requests`
- `loan_approvals.*`
- `deposit_requests.*`
- `withdraw_requests.*`
- `loans.upcoming_loan_repayments`
- `loans.filter` with pending state

---

## 3.3 Members
### Purpose
All member-related administration in one module.

### Tabs
- All Members
- Onboarding / Requests
- Documents
- Branches
- Leaders
- Import
- Custom Fields

### Included features
- Member List
- Add Member
- Edit Member
- Member Requests
- Bulk Import
- Branches
- Leaders
- Member custom fields
- Member documents from member detail view
- Document / onboarding visibility

### UX behavior
- `Add Member` should be primary page CTA
- `Bulk Import` belongs inside Import tab
- Branches and Leaders should no longer feel hidden under settings
- Member profile remains full page with tabs
- onboarding states should be visible, such as pending approval, incomplete documents, active, dormant, and members with overdue loans

### Microfinance enhancement
Members should be more than a simple directory. The workspace should help admins answer:
- who is still awaiting approval?
- who has incomplete documents?
- which members have active or overdue loans?
- which branch has onboarding bottlenecks?

### Primary source routes
- `members.*`
- `branches.*`
- `leaders.*`
- `custom_fields.*` for members
- `member_documents.*`

---

## 3.4 Loans
### Purpose
Manage loan applications, approvals, disbursement readiness, repayments, and collections in one place.

### Tabs
- Pipeline
- Disbursements
- Repayments
- Due / Upcoming
- Collections
- Loan Products
- Approvals
- Calculator

### Included features
- All Loans
- Pending Loans
- Active Loans
- Loan Calculator
- Loan Products
- Loan Repayments
- Upcoming Payments
- Loan approvals
- Loan-related collateral and guarantors from loan detail flow
- collection visibility for overdue loans
- disbursement readiness checks

### UX behavior
- lifecycle states should be central to the UI, not buried in filters
- recommended stages include Draft, Submitted, Under Review, Approved, Ready for Disbursement, Disbursed/Active, Overdue, Closed, and Rejected
- Loan Repayments should no longer be a separate sidebar area
- Upcoming Payments should live here and also surface in Action Center
- Collections should show overdue buckets, urgency, and quick repayment actions
- Loan details remain full page

### Microfinance enhancement
The Loans module should help admins answer:
- which applications are stuck in review?
- which approved loans are ready for disbursement?
- which active loans are becoming risky?
- which loans are overdue by bucket?
- how much needs follow-up today?

### Primary source routes
- `loans.*`
- `loan_products.*`
- `loan_payments.*`
- `loan_approvals.*`
- `loan_collaterals.*`
- `guarantors.*`
- `loan_approver_settings.*`

---

## 3.5 Finance
### Purpose
Centralize all money, account, cash, method, and banking workflows.

### Tabs
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

### Included features
#### Wallets
- wallet summary
- wallet import

#### Savings Accounts
- savings accounts
- savings products / account types

#### Teller / Cash Ops
- branch cash movement visibility
- high-frequency posting shortcuts for admin
- quick cash in / cash out awareness
- day-end readiness indicators

#### Cash Transactions
- new transaction
- deposit money
- withdraw money
- transaction history
- transaction categories

#### Requests
- deposit requests
- withdraw requests

#### Expenses
- expenses
- expense categories

#### Banking
- bank accounts
- bank transactions

#### Reconciliation
- balancing checkpoints
- posting mismatches
- unresolved finance exceptions

#### Methods
- online gateways
- offline deposit methods
- withdraw methods

#### Interest
- interest calculation/posting

### UX behavior
- Deposit and Withdraw should be treated as variants of transactions, not standalone sidebar modules
- Banking should group accounts and transactions
- Methods should group all inbound/outbound method setup
- Teller / Cash Ops should make daily admin cash handling easier even before dedicated teller roles are introduced
- Reconciliation should make finance exceptions and unresolved mismatches visible
- Short CRUD screens in this module should be modal-first where suitable

### Microfinance enhancement
Finance should help admins answer:
- what cash moved today?
- where are pending financial requests?
- are there unresolved mismatches or posting issues?
- what is the current liquidity / cash position?

### Primary source routes
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

---

## 3.6 Reports
### Purpose
Provide one reporting hub instead of many sidebar links.

### Tabs
- Executive KPIs
- Portfolio
- Collections
- Accounts
- Transactions
- Expenses
- Banking
- Branch Performance
- Revenue

### Included reports
#### Executive KPIs
- portfolio outstanding
- overdue summary
- disbursements vs collections
- liquidity indicators

#### Portfolio
- Loan Report
- Loan Due Report
- Loan Repayment Report
- portfolio segmentation and performance views later

#### Collections
- overdue buckets
- due today / overdue trends
- collection effectiveness later

#### Accounts
- Account Statement
- Account Balances
- Cash in Hand

#### Transactions
- Transaction Report

#### Expenses
- Expense Report

#### Banking
- Bank Transactions
- Bank Account Balances

#### Branch Performance
- branch comparison views
- branch-level workload, loan, and collection summaries later

#### Revenue
- Revenue Report

### UX behavior
- user should enter reporting through one page only
- report filter panels should be consistent
- reports should be selectable in-page rather than via many menu links
- key reports should align to microfinance questions, not just generic accounting outputs

### Primary source routes
- `reports.*`

---

## 3.7 Administration
### Purpose
Centralize system, user, and configuration tasks.

### Tabs
- Users
- Roles & Permissions
- Settings
- Currency
- Notification Templates

### Included features
- Manage Users
- Roles
- Permission / Access Control
- Tenant Settings
- Currency Management
- Notification Templates

### UX behavior
- Settings remains a tabbed page
- Currency stays modal-first CRUD
- Roles create/edit remains modal-first
- Access control remains page-level

### Primary source routes
- `users.*`
- `roles.*`
- `permission.*`
- `settings.*`
- `currency.*`
- `email_templates.*`

---

## 3.8 Messages
### Purpose
Keep messaging available without overemphasizing it in the sidebar.

### Tabs
- Inbox
- Sent

### Actions
- Compose should be a page button, not a sidebar submenu item

### Future option
This module could later move to a top-bar notification/message center if desired.

### Primary source routes
- `messages.*`

---

# 4. Modal vs Full Page Decision Framework

## 4.1 Modal-first CRUD
Use modal-first for short, low-risk, quick-edit entities.

### Approved modal-first candidates
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

## 4.2 Detail modal / large modal
Use for inspect-and-decide flows.

### Good candidates
- Deposit request detail
- Withdraw request detail
- Message preview
- Loan approval summary

## 4.3 Full pages only
Keep as full pages because they are longer, more sensitive, or more complex.

### Full-page workflows
- Member create/edit/view
- Loan create/edit/view
- Loan product create/edit
- Transaction create/edit
- Deposit method create/edit
- Withdraw method create/edit
- Reports center and detailed report pages
- Settings center
- Member import
- Wallet import

---

# 5. Navigation Behavior Rules

## 5.1 Sidebar
- Only modules belong in sidebar.
- Avoid exposing every child route.
- Keep submenu depth shallow.
- Prefer one module link leading to a tabbed workspace.

## 5.2 Page headers
Each workspace should include:
- title
- short description or context
- primary CTA button
- optional quick filter row
- optional summary cards
- high-signal status/exception indicators where relevant

## 5.3 Tabs
- Tabs should be used inside module pages, not the sidebar, for secondary navigation.
- Tabs should preserve current route context if possible.
- Where useful, selected tab state may be kept in query string.
- In Loans, Finance, and Reports, tab order should reflect daily operational priority.

## 5.4 Actions
- `Add New`, `Import`, `Compose`, `Run Report`, `Approve`, etc. should be closest to the list or data they affect.
- Row actions should use a consistent dropdown pattern.
- High-frequency actions like approve, disburse, post repayment, and review request should be made faster than low-frequency settings work.

## 5.5 Status and exception visibility
- overdue status, pending approval, disbursement readiness, and reconciliation exceptions should be visually obvious
- aging buckets should be treated as primary navigation/filter concepts where applicable
- admin users should not need to dig through multiple pages to find what requires immediate action

---

# 6. Target Workspace Route Strategy

The first implementation phase should preserve old routes while adding new workspace routes.

## Recommended additive routes
- `action_center.index`
- `members.workspace`
- `loans.workspace`
- `finance.index`
- `reports.index`
- `administration.index`

## Strategy
- Sidebar points to new workspace routes
- Existing legacy routes remain available
- Existing detailed routes remain the source of truth for CRUD actions until safely consolidated

---

# 7. File Architecture Recommendations

## Core files that should evolve
- `resources/views/layouts/menus/admin.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`
- `resources/views/backend/admin/**`
- `public/backend/assets/css/styles.css`
- `public/backend/assets/css/responsive.css`

## Recommended new menu partials
- `resources/views/layouts/menus/admin/dashboard.blade.php`
- `resources/views/layouts/menus/admin/action-center.blade.php`
- `resources/views/layouts/menus/admin/members.blade.php`
- `resources/views/layouts/menus/admin/loans.blade.php`
- `resources/views/layouts/menus/admin/finance.blade.php`
- `resources/views/layouts/menus/admin/reports.blade.php`
- `resources/views/layouts/menus/admin/administration.blade.php`
- `resources/views/layouts/menus/admin/messages.blade.php`

## Recommended new workspace views
- `resources/views/backend/admin/action_center/index.blade.php`
- `resources/views/backend/admin/member/workspace.blade.php`
- `resources/views/backend/admin/loan/workspace.blade.php`
- `resources/views/backend/admin/finance/index.blade.php`
- `resources/views/backend/admin/reports/index.blade.php`
- `resources/views/backend/admin/administration/index.blade.php`

## Recommended shared partials
- `resources/views/layouts/others/page-header.blade.php`
- `resources/views/backend/admin/partials/module-tabs.blade.php`
- `resources/views/backend/admin/partials/filter-bar.blade.php`
- `resources/views/backend/admin/partials/quick-actions.blade.php`
- `resources/views/backend/admin/partials/empty-state.blade.php`

---

# 8. Breadcrumb Strategy

## Current problem
Breadcrumbs are currently based mainly on URL segments.

## Target approach
- support explicit breadcrumb arrays from controllers/views
- retain fallback behavior for old pages
- ensure workspace pages can show meaningful context like:
  - Dashboard > Members
  - Dashboard > Finance > Banking
  - Dashboard > Reports > Loans

Primary file:
- `resources/views/layouts/others/breadcrumbs.blade.php`

---

# 9. Visual Design Principles

The target admin interface should feel:
- calmer
- more task-oriented
- more consistent
- less cluttered
- easier to learn

## Specific visual guidelines
- use summary cards sparingly and meaningfully
- standardize tab styling
- keep filters close to tables
- reduce repeated header clutter
- use badge colors consistently
- avoid icon overload
- ensure good mobile behavior for tabs and CTAs

---

# 10. Success Criteria

The target IA is successful when:
- top-level sidebar items drop from about 19 to 7–8
- admins can complete common work in 1–2 navigational steps
- requests and approvals are easy to find in one place
- finance operations feel consolidated
- reports are discoverable from one reporting hub
- short CRUD tasks do not unnecessarily trigger full-page navigation
- no existing important workflow is lost

---

# 11. Implementation Sequencing

## Phase 1
- audit current navigation
- refactor sidebar
- preserve all current routes
- introduce workspace route stubs

## Phase 2
- implement Action Center
- implement Reports Center
- implement Administration workspace
- add executive KPI and exception layer to Dashboard

## Phase 3
- implement Members workspace with onboarding/document visibility
- implement Loans workspace with pipeline, disbursement, and collections emphasis
- implement Finance workspace with teller/cash ops and reconciliation visibility

## Phase 4
- standardize modal behavior
- convert eligible short CRUD screens to modal-first
- improve request detail views

## Phase 5
- add shared page headers
- improve breadcrumbs
- improve responsive tab behavior
- run regression QA

---

# 12. Guardrails for Agents and Developers

When implementing this architecture:
- prefer incremental changes over full rewrites
- preserve route compatibility during the first rollout
- do not remove legacy links until the new workspaces are validated
- do not introduce extra top-level menu groups unless required
- reuse existing modals and Blade patterns where possible
- keep tenant-aware behavior intact
- audit route references in views before changing navigation assumptions
- optimize for real microfinance admin needs, not just cleaner grouping
- bias design toward visibility of risk, exceptions, collections pressure, and operational bottlenecks

---

# 13. Related Documents
- `AGENTS.md`
- `docs/admin-ux/current-navigation-audit.md`
- `docs/admin-ux/microfinance-enhancement-layer.md`
