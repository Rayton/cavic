# Developer Backlog

## Purpose
This backlog translates the **CAVIC** admin UI/UX strategy into implementation-ready work items for developers and coding agents.

It combines:
- the base admin navigation refactor
- the workspace consolidation plan
- the microfinance enhancement layer
- the current admin-only scope decision

Use this together with:
- `AGENTS.md`
- `docs/admin-ux/current-navigation-audit.md`
- `docs/admin-ux/target-information-architecture.md`
- `docs/admin-ux/microfinance-enhancement-layer.md`

---

# Scope
## In scope now
- Admin UX only
- Navigation restructure
- Workspace pages
- Modal standardization
- Dashboard improvements
- Loan lifecycle visibility
- Collections visibility
- Finance reconciliation visibility
- Reports restructuring

## Out of scope for now
- Teller-only UX
- Collector-only UX
- Credit-officer-only UX
- Branch-manager-only UX
- Customer portal redesign

---

# Backlog Format
Each ticket includes:
- ID
- Title
- Story Points
- Priority
- Objective
- Main files
- Dependencies
- Acceptance Criteria

---

# Epic A — Discovery, Audit, and Safety Baseline

## UX-001 — Capture current admin navigation baseline
**Story Points:** 2  
**Priority:** Critical

### Objective
Document the current admin navigation and relevant route inventory before changing UX.

### Main files
- `resources/views/layouts/menus/admin.blade.php`
- `routes/web.php`
- `resources/views/backend/admin/**`

### Tasks
- export route list
- capture screenshots of current admin pages
- document all current sidebar links
- note which screens already support modal CRUD

### Deliverables
- `storage/app/admin-route-audit.txt`
- screenshots baseline
- current navigation notes

### Dependencies
- none

### Acceptance Criteria
- all current admin sidebar entries are documented
- screenshots exist for major workflows
- route inventory is saved for regression comparison

---

## UX-002 — Finalize target information architecture and scope
**Story Points:** 3  
**Priority:** Critical

### Objective
Lock the admin-only target structure and ensure all current features have a future home.

### Main files
- `docs/admin-ux/target-information-architecture.md`
- `docs/admin-ux/microfinance-enhancement-layer.md`

### Tasks
- confirm final top-level menu
- confirm final workspace tabs
- confirm admin-only scope for current phase
- confirm modal vs page decisions

### Dependencies
- UX-001

### Acceptance Criteria
- every current admin feature maps to a target module/tab
- the team agrees the current phase is admin-only

---

## UX-003 — Audit route/view inconsistencies before refactor
**Story Points:** 3  
**Priority:** High

### Objective
Find Blade references that may not match actual route definitions.

### Main files
- `routes/web.php`
- `resources/views/backend/admin/**`

### Areas to audit
- resource `show` routes referenced from views
- any AJAX modal links referencing missing routes
- verify restored `loan_approver_settings.*` flow renders correctly in tenant admin

### Dependencies
- UX-001

### Acceptance Criteria
- missing or inconsistent route references are documented and fixed or deferred explicitly

---

# Epic B — Navigation Refactor

## UX-010 — Refactor admin sidebar into grouped modules
**Story Points:** 5  
**Priority:** Critical

### Objective
Replace the overloaded admin menu with grouped, task-based navigation.

### Target top-level items
- Dashboard
- Action Center
- Members
- Loans
- Finance
- Reports
- Administration
- Messages

### Main files
- `resources/views/layouts/menus/admin.blade.php`

### Dependencies
- UX-002

### Acceptance Criteria
- top-level admin menu is reduced to grouped modules
- sidebar no longer exposes action-only links
- all previous areas remain reachable

---

## UX-011 — Split admin menu into partials
**Story Points:** 3  
**Priority:** High

### Objective
Make the admin menu maintainable and easier to evolve.

### Main files
Create:
- `resources/views/layouts/menus/admin/dashboard.blade.php`
- `resources/views/layouts/menus/admin/action-center.blade.php`
- `resources/views/layouts/menus/admin/members.blade.php`
- `resources/views/layouts/menus/admin/loans.blade.php`
- `resources/views/layouts/menus/admin/finance.blade.php`
- `resources/views/layouts/menus/admin/reports.blade.php`
- `resources/views/layouts/menus/admin/administration.blade.php`
- `resources/views/layouts/menus/admin/messages.blade.php`

### Dependencies
- UX-010

### Acceptance Criteria
- `admin.blade.php` becomes a lightweight wrapper
- menu items are easier to update per module

---

## UX-012 — Add active/open state support for grouped navigation
**Story Points:** 3  
**Priority:** High

### Objective
Ensure grouped menu items remain intuitive when browsing workspace routes and legacy routes.

### Main files
- `resources/views/layouts/menus/admin*.blade.php`
- `public/backend/assets/js/scripts.js` if needed

### Dependencies
- UX-010

### Acceptance Criteria
- correct parent menu stays open/highlighted
- grouped routes still map visually to the right sidebar module

---

# Epic C — Dashboard and Action Center

## UX-020 — Add Action Center route and controller
**Story Points:** 3  
**Priority:** Critical

### Objective
Create a new Action Center route as the operational work queue.

### Main files
- `routes/web.php`
- `app/Http/Controllers/ActionCenterController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- Action Center route loads
- controller returns queue data needed for tabs

---

## UX-021 — Build Action Center tabbed page
**Story Points:** 8  
**Priority:** Critical

### Tabs
- Member Requests
- Pending Loans
- Deposit Requests
- Withdraw Requests
- Due Today / Upcoming Repayments
- Exceptions

### Main files
- `resources/views/backend/admin/action_center/index.blade.php`

### Dependencies
- UX-020

### Acceptance Criteria
- admin can process major pending work from one page
- high-priority items are easy to identify
- counts match menu badges

---

## UX-022 — Add executive + operational KPI layer to Dashboard
**Story Points:** 8  
**Priority:** Critical

### Objective
Upgrade dashboard beyond generic stats to a microfinance-aware admin console.

### KPI groups
#### Executive KPIs
- active members
- active borrowers
- portfolio outstanding
- overdue amount
- disbursements this month
- collections this month
- deposits/withdrawals trend
- liquidity/cash position

#### Operational KPIs
- pending approvals
- due today
- overdue today
- ready for disbursement
- finance exceptions
- branch workload summary

### Main files
- `resources/views/backend/admin/dashboard-admin.blade.php`
- `app/Http/Controllers/DashboardController.php`
- widget endpoints if needed

### Dependencies
- UX-001

### Acceptance Criteria
- dashboard surfaces both strategic and operational priorities
- overdue, approvals, and exceptions are visually prominent

---

## UX-023 — Add branch performance and exception visibility to dashboard
**Story Points:** 5  
**Priority:** High

### Objective
Make the admin dashboard branch-aware and exception-aware.

### Main files
- `resources/views/backend/admin/dashboard-admin.blade.php`
- `app/Http/Controllers/DashboardController.php`

### Dependencies
- UX-022

### Acceptance Criteria
- branch-level performance or workload summary is visible
- exception list or exception cards are visible to admin

---

# Epic D — Members Workspace

## UX-030 — Add Members workspace route and controller
**Story Points:** 3  
**Priority:** High

### Main files
- `routes/web.php`
- `app/Http/Controllers/MemberWorkspaceController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- workspace route loads
- members module data can be served from one place

---

## UX-031 — Build Members workspace
**Story Points:** 8  
**Priority:** High

### Tabs
- All Members
- Onboarding / Requests
- KYC & Documents
- Branches
- Leaders
- Import
- Custom Fields

### Main files
- `resources/views/backend/admin/member/workspace.blade.php`

### Dependencies
- UX-030

### Acceptance Criteria
- member-related features are grouped cleanly
- Add Member becomes page CTA
- Bulk Import is no longer a sidebar link

---

## UX-032 — Add member onboarding and KYC visibility
**Story Points:** 5  
**Priority:** High

### Objective
Make member lifecycle states visible from the workspace.

### Lifecycle markers
- applicant
- pending approval
- incomplete KYC
- active
- dormant
- active borrower
- overdue borrower

### Main files
- `resources/views/backend/admin/member/workspace.blade.php`
- `resources/views/backend/admin/member/view.blade.php`
- `app/Http/Controllers/MemberWorkspaceController.php`

### Dependencies
- UX-031

### Acceptance Criteria
- admins can quickly identify onboarding and KYC bottlenecks
- members with overdue exposure are easier to spot

---

# Epic E — Loans Workspace

## UX-040 — Add Loans workspace route and controller
**Story Points:** 3  
**Priority:** High

### Main files
- `routes/web.php`
- `app/Http/Controllers/LoanWorkspaceController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- loans workspace route loads
- tabs can be populated centrally

---

## UX-041 — Build Loans workspace
**Story Points:** 8  
**Priority:** High

### Tabs
- Pipeline
- Disbursements
- Repayments
- Due / Upcoming
- Collections
- Loan Products
- Approvals
- Calculator

### Main files
- `resources/views/backend/admin/loan/workspace.blade.php`

### Dependencies
- UX-040

### Acceptance Criteria
- Loans module feels like a complete workflow area
- repayments and upcoming dues no longer feel disconnected

---

## UX-042 — Add admin loan lifecycle pipeline UX
**Story Points:** 8  
**Priority:** Critical

### Objective
Expose loan stages clearly in the UI.

### Recommended stages
- Draft
- Submitted
- Under Review
- Approved
- Ready for Disbursement
- Disbursed / Active
- Overdue
- Closed
- Rejected

### Main files
- `resources/views/backend/admin/loan/workspace.blade.php`
- loan list/filter views
- controller logic for stage counts if needed

### Dependencies
- UX-041

### Acceptance Criteria
- lifecycle stages are easy to navigate
- admins can tell where loans are getting stuck

---

## UX-043 — Add disbursement queue and readiness visibility
**Story Points:** 5  
**Priority:** Critical

### Objective
Surface approved loans that are ready, blocked, or pending for disbursement.

### Main files
- `resources/views/backend/admin/loan/workspace.blade.php`
- `app/Http/Controllers/LoanWorkspaceController.php`

### Dependencies
- UX-042

### Acceptance Criteria
- admin can quickly see which loans are ready for disbursement
- blockers are visible, not hidden in detail pages

---

## UX-044 — Add collections tab inside Loans
**Story Points:** 8  
**Priority:** Critical

### Objective
Add a proper collections view for overdue and due-soon loans.

### Collections features
- due today
- overdue buckets
- total overdue amount
- urgency indicators
- quick links to member/loan/repayment actions

### Main files
- `resources/views/backend/admin/loan/workspace.blade.php`
- `app/Http/Controllers/LoanWorkspaceController.php`

### Dependencies
- UX-041

### Acceptance Criteria
- overdue items are visible by aging bucket
- collections pressure is obvious to admin

---

# Epic F — Finance Workspace

## UX-050 — Add Finance workspace route and controller
**Story Points:** 3  
**Priority:** Critical

### Main files
- `routes/web.php`
- `app/Http/Controllers/FinanceHubController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- finance workspace route loads
- grouped tabs can be rendered from one hub

---

## UX-051 — Build Finance workspace
**Story Points:** 13  
**Priority:** Critical

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

### Main files
- `resources/views/backend/admin/finance/index.blade.php`

### Dependencies
- UX-050

### Acceptance Criteria
- finance operations are consolidated into one module
- deposit/withdraw are treated as transaction variants
- banking and methods are grouped correctly

---

## UX-052 — Add Teller / Cash Ops inside Finance (admin-only version)
**Story Points:** 5  
**Priority:** High

### Objective
Create a strong admin-level cash operations view without yet creating a separate teller role.

### Main files
- `resources/views/backend/admin/finance/index.blade.php`
- relevant finance controllers/views

### Dependencies
- UX-051

### Acceptance Criteria
- admin can monitor cash movement and branch cash activity more clearly
- high-frequency cash actions become easier to access

---

## UX-053 — Add reconciliation and finance exception visibility
**Story Points:** 8  
**Priority:** High

### Objective
Surface unresolved finance mismatches and operational finance issues.

### Main files
- `resources/views/backend/admin/finance/index.blade.php`
- supporting controllers

### Dependencies
- UX-051

### Acceptance Criteria
- finance exceptions are visible in workspace or dashboard
- unresolved mismatches are not hidden behind isolated screens

---

# Epic G — Reports and Administration

## UX-060 — Add Reports Center route and controller
**Story Points:** 3  
**Priority:** Critical

### Main files
- `routes/web.php`
- `app/Http/Controllers/ReportCenterController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- report center route loads

---

## UX-061 — Build Reports Center
**Story Points:** 8  
**Priority:** Critical

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

### Main files
- `resources/views/backend/admin/reports/index.blade.php`

### Dependencies
- UX-060

### Acceptance Criteria
- reports are entered from one central place
- report groupings align with business questions

---

## UX-062 — Add branch performance reporting layer
**Story Points:** 5  
**Priority:** High

### Objective
Support branch-level visibility in reports.

### Main files
- `resources/views/backend/admin/reports/index.blade.php`
- reporting controller logic

### Dependencies
- UX-061

### Acceptance Criteria
- branch comparison or branch summary views are accessible in Reports

---

## UX-063 — Add Administration workspace route and controller
**Story Points:** 3  
**Priority:** High

### Main files
- `routes/web.php`
- `app/Http/Controllers/AdministrationHubController.php`

### Dependencies
- UX-002

### Acceptance Criteria
- administration route loads successfully

---

## UX-064 — Build Administration workspace
**Story Points:** 8  
**Priority:** High

### Tabs
- Users
- Roles & Permissions
- Settings
- Currency
- Notification Templates

### Main files
- `resources/views/backend/admin/administration/index.blade.php`

### Dependencies
- UX-063

### Acceptance Criteria
- system configuration tasks are grouped in one workspace

---

# Epic H — Modal Standardization and Fast Actions

## UX-070 — Audit modal-capable CRUD coverage
**Story Points:** 2  
**Priority:** High

### Objective
Confirm which admin entities are already modal-first and which need conversion.

### Dependencies
- UX-001

### Acceptance Criteria
- list of modal-ready entities is documented

---

## UX-071 — Standardize modal form behavior
**Story Points:** 5  
**Priority:** High

### Main files
- `resources/views/layouts/app.blade.php`
- `public/backend/assets/js/scripts.js`

### Scope
- `ajax-modal`
- `ajax-submit`
- `ajax-screen-submit`
- validation handling
- success refresh behavior

### Dependencies
- UX-070

### Acceptance Criteria
- modal CRUD behaves consistently across modules

---

## UX-072 — Convert micro-CRUD screens to modal-first
**Story Points:** 5  
**Priority:** High

### Scope
- Branches
- Leaders
- Currency
- Transaction Categories
- Expense Categories
- Savings Products
- Roles
- Bank Accounts
- Bank Transactions
- Member Documents
- Guarantors

### Dependencies
- UX-071

### Acceptance Criteria
- simple config/lookup screens no longer require full-page navigation

---

## UX-073 — Add request and review detail modals
**Story Points:** 5  
**Priority:** High

### Scope
- Deposit request detail
- Withdraw request detail
- Message preview
- approval detail summary where practical

### Dependencies
- UX-021
- UX-071

### Acceptance Criteria
- admins can inspect and act without unnecessary full-page transitions

---

# Epic I — Shared UI Patterns and Visual Consistency

## UX-080 — Support explicit breadcrumbs for workspace pages
**Story Points:** 3  
**Priority:** Medium

### Main files
- `resources/views/layouts/others/breadcrumbs.blade.php`

### Dependencies
- UX-020
- UX-030
- UX-040
- UX-050
- UX-060
- UX-063

### Acceptance Criteria
- workspace pages show meaningful breadcrumbs
- legacy pages still fall back safely

---

## UX-081 — Create reusable page header partial
**Story Points:** 3  
**Priority:** Medium

### Main files
Create:
- `resources/views/layouts/others/page-header.blade.php`

### Acceptance Criteria
- module pages share a consistent heading + CTA pattern

---

## UX-082 — Create shared workspace partials
**Story Points:** 5  
**Priority:** Medium

### Files
- `resources/views/backend/admin/partials/module-tabs.blade.php`
- `resources/views/backend/admin/partials/filter-bar.blade.php`
- `resources/views/backend/admin/partials/quick-actions.blade.php`
- `resources/views/backend/admin/partials/empty-state.blade.php`

### Dependencies
- UX-081

### Acceptance Criteria
- workspaces use common UI building blocks

---

## UX-083 — Add workspace-specific UI styling
**Story Points:** 5  
**Priority:** Medium

### Main files
- `public/backend/assets/css/styles.css`
- `public/backend/assets/css/responsive.css`

### Scope
- status chips
- aging badges
- exception panels
- workspace tabs
- sticky filter areas
- dashboard KPI cards

### Dependencies
- UX-021
- UX-031
- UX-041
- UX-051
- UX-061
- UX-064

### Acceptance Criteria
- new admin workspaces look cohesive and operationally focused

---

## UX-084 — Improve mobile/small-screen workspace behavior
**Story Points:** 3  
**Priority:** Medium

### Dependencies
- UX-083

### Acceptance Criteria
- tabbed workspaces remain usable on smaller screens
- CTAs and filters do not break layout

---

# Epic J — QA, Rollout, and Cleanup

## UX-090 — Run full admin regression pass
**Story Points:** 5  
**Priority:** Critical

### Test scenarios
- sidebar loads and groups correctly
- workspaces load
- badges still work
- modal forms submit correctly
- add/edit member still works
- loan creation and approval still works
- repayments still work
- deposit/withdraw request actions still work
- reports still run
- settings still save
- tenant-aware URLs still work

### Dependencies
- all implementation tickets for current release

### Acceptance Criteria
- no major admin workflow is broken

---

## UX-091 — Remove obsolete sidebar links after stabilization
**Story Points:** 2  
**Priority:** Medium

### Objective
Clean up temporary duplication once the new navigation is validated.

### Dependencies
- UX-090

### Acceptance Criteria
- final sidebar reflects only approved grouped navigation

---

# Suggested Release Sequence

## Release 1 — Safe foundation
- UX-001
- UX-002
- UX-003
- UX-010
- UX-011
- UX-012

## Release 2 — Dashboard + Action Center
- UX-020
- UX-021
- UX-022
- UX-023

## Release 3 — Members + Loans
- UX-030
- UX-031
- UX-032
- UX-040
- UX-041
- UX-042
- UX-043
- UX-044

## Release 4 — Finance + Reports + Administration
- UX-050
- UX-051
- UX-052
- UX-053
- UX-060
- UX-061
- UX-062
- UX-063
- UX-064

## Release 5 — UI polish and cleanup
- UX-070
- UX-071
- UX-072
- UX-073
- UX-080
- UX-081
- UX-082
- UX-083
- UX-084
- UX-090
- UX-091

---

# Definition of Done
The backlog phase is considered complete when:
- grouped admin navigation is live
- dashboard reflects real microfinance priorities
- Action Center supports daily operational work
- Members, Loans, Finance, Reports, and Administration workspaces exist
- loan lifecycle, collections, disbursement readiness, and finance exceptions are visible
- short CRUD flows are modal-first where appropriate
- regression testing passes
- the admin UI feels like a microfinance operations console rather than a generic CRUD backend
