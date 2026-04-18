# CAVIC UI Upgrade Implementation Checklist

**Source design:** `design.md`
**Reference image:** `newcavic.webp`
**Scope:** Admin UI only
**Guardrail:** Retain the public/frontend website and existing route behavior while upgrading backend admin UX.

---

## 0. Non-negotiables

- [ ] Do **not** redesign or break the public website frontend.
- [ ] Do **not** remove legacy routes in the first pass.
- [ ] Do **not** force large workflows into small modals.
- [ ] Do **not** move action-only links into the sidebar.
- [ ] Do preserve modal infrastructure: `#main_modal`, `#secondary_modal`.
- [ ] Do preserve tenant-aware behavior, branch switching, and permissions.
- [ ] Do keep admin improvements additive and reversible where possible.

---

## 1. Foundation styles

### Tokens and shell
- [x] Add admin design tokens to `public/backend/assets/css/styles.css`
- [x] Add responsive admin-shell rules to `public/backend/assets/css/responsive.css`
- [x] Introduce a rounded app shell for admin-only pages
- [x] Keep admin shell changes scoped so frontend pages are unaffected
- [ ] Add consistent card, border, radius, spacing, and muted shadow rules

### Typography and readability
- [ ] Normalize page titles, subtitles, card titles, helper text
- [ ] Improve contrast for primary and secondary text
- [ ] Standardize status colors: success, warning, danger, info

### Modal styling
- [ ] Soften modal corners and borders
- [ ] Standardize modal header/body spacing
- [ ] Keep existing AJAX modal behavior intact

---

## 2. Sidebar and navigation

### Sidebar structure
- [ ] Refactor admin sidebar into a calm workspace-first navigation
- [ ] Keep only these top-level items:
  - [ ] Dashboard
  - [ ] Action Center
  - [ ] Members
  - [ ] Loans
  - [ ] Finance
  - [ ] Reports
  - [ ] Administration
  - [ ] Messages
- [ ] Preserve menu badges/counters where already available
- [ ] Keep sidebar changes scoped to admin so customer/frontend behavior is retained

### Sidebar presentation
- [x] Add cleaner branding block in sidebar
- [x] Add section label like “Main Menu” or “Workspaces”
- [x] Style active state with the new teal visual system
- [x] Reduce hover noise and sidebar clutter
- [x] Ensure collapsed sidebar still works with existing JS

### Active states
- [ ] Verify active route highlighting still works
- [ ] Verify grouped menu remains open when submenu is active where applicable

---

## 3. Header, breadcrumbs, and page chrome

### Header area
- [ ] Modernize admin top utility bar without changing behavior
- [ ] Keep notification dropdown working
- [ ] Keep profile menu working
- [ ] Keep language switcher working
- [ ] Keep branch switcher working

### Page header and breadcrumbs
- [ ] Improve breadcrumb visuals in `resources/views/layouts/others/breadcrumbs.blade.php`
- [ ] Standardize reusable page header usage
- [ ] Prefer explicit breadcrumb arrays in workspace pages
- [ ] Preserve segment-based fallback behavior for older pages
- [ ] Standardize major admin workspace tabs in the top strip directly below the navbar, matching Dashboard
- [ ] Keep contextual selectors like branch selection on the right side of that same top strip when needed

---

## 4. Shared UI partials

- [ ] Confirm reusable page header partial is the standard entry point
- [x] Create/standardize module tabs partial
- [x] Create/standardize filter bar partial
- [x] Create/standardize quick actions partial
- [x] Create/standardize empty state partial
- [ ] Keep styling reusable instead of page-specific
- [ ] Use the dashboard-style top underline tabs as the default tab pattern for major admin workspaces

Primary files:
- `resources/views/layouts/others/page-header.blade.php` or current shared header partial in use
- `resources/views/backend/admin/partials/module-tabs.blade.php`
- `resources/views/backend/admin/partials/filter-bar.blade.php`
- `resources/views/backend/admin/partials/quick-actions.blade.php`
- `resources/views/backend/admin/partials/empty-state.blade.php`

---

## 5. Dashboard upgrade

- [x] Apply the new shell most clearly on Dashboard first
- [ ] Add KPI cards for microfinance operations
- [ ] Add recent activity panel
- [ ] Add operational insight panels
- [ ] Emphasize overdue exposure, due today, approvals, and exceptions
- [ ] Keep dashboard useful, not decorative

Suggested KPI set:
- [ ] Total Active Members
- [ ] Active Borrowers
- [ ] Portfolio Outstanding
- [ ] Overdue Amount
- [ ] PAR Exposure
- [ ] Due Today
- [ ] Disbursements This Month
- [ ] Deposits This Month
- [ ] Withdrawals This Month
- [ ] Cash Position

---

## 6. Action Center workspace

- [ ] Ensure route/controller/view remain reachable
- [ ] Surface urgent operational queues first
- [ ] Add tabs:
  - [ ] Member Requests
  - [ ] Pending Loans
  - [ ] Deposit Requests
  - [ ] Withdraw Requests
  - [ ] Due Today / Upcoming Repayments
  - [ ] Exceptions
- [ ] Make approval/review actions easy to scan

---

## 7. Members workspace

- [ ] Build/standardize workspace with tabs:
  - [ ] All Members
  - [ ] Onboarding / Requests
  - [ ] KYC & Documents
  - [ ] Branches
  - [ ] Leaders
  - [ ] Import
  - [ ] Custom Fields
- [ ] Add lifecycle visibility for pending/active/incomplete/dormant states
- [ ] Keep full-page create/edit/view behavior for members

---

## 8. Loans workspace

- [ ] Build/standardize workspace with tabs:
  - [ ] Pipeline
  - [ ] Disbursements
  - [ ] Repayments
  - [ ] Due / Upcoming
  - [ ] Collections
  - [ ] Loan Products
  - [ ] Approvals
  - [ ] Calculator
- [ ] Make lifecycle states visible:
  - [ ] Draft
  - [ ] Submitted
  - [ ] Under Review
  - [ ] Approved
  - [ ] Ready for Disbursement
  - [ ] Disbursed / Active
  - [ ] Overdue
  - [ ] Closed
  - [ ] Rejected
- [ ] Add overdue aging visibility and collections emphasis

---

## 9. Finance workspace

- [ ] Build/standardize workspace with tabs:
  - [ ] Wallets
  - [ ] Savings Accounts
  - [ ] Teller / Cash Ops
  - [ ] Cash Transactions
  - [ ] Requests
  - [ ] Expenses
  - [ ] Banking
  - [ ] Reconciliation
  - [ ] Methods
  - [ ] Interest
- [ ] Surface cash position and pending requests
- [ ] Make reconciliation exceptions visible

---

## 10. Reports workspace

- [ ] Build/standardize Reports center with tabs:
  - [ ] Executive KPIs
  - [ ] Portfolio
  - [ ] Collections
  - [ ] Accounts
  - [ ] Transactions
  - [ ] Expenses
  - [ ] Banking
  - [ ] Branch Performance
  - [ ] Revenue
- [ ] Add filter bar and export controls
- [ ] Make reports feel like a dashboard, not a raw link list

---

## 11. Administration workspace

- [ ] Build/standardize tabs:
  - [ ] Users
  - [ ] Roles & Permissions
  - [ ] Settings
  - [ ] Currency
  - [ ] Notification Templates
- [ ] Keep short CRUD modal-first where practical
- [ ] Keep settings center as a full-page workflow

---

## 12. Messages workspace

- [ ] Keep Inbox and Sent as the main tabs
- [ ] Keep compose as a page-level action, not sidebar clutter
- [ ] Improve list/detail preview layout without breaking existing message routes

---

## 13. Modal-first CRUD rollout

Convert to modal-first where practical:
- [ ] Branches
- [ ] Leaders
- [ ] Currency
- [ ] Transaction Categories
- [ ] Expense Categories
- [ ] Savings Products
- [ ] Savings Accounts quick CRUD
- [ ] Bank Accounts
- [ ] Bank Transactions
- [ ] Member Documents
- [ ] Roles
- [ ] Custom Fields
- [ ] Guarantors
- [ ] Loan approver settings

Use detail/review modal where practical:
- [ ] Deposit request detail
- [ ] Withdraw request detail
- [ ] Message preview
- [ ] Loan approval summary

Keep as full page:
- [ ] Member create/edit/view
- [ ] Loan create/edit/view
- [ ] Loan product create/edit
- [ ] Transaction create/edit
- [ ] Deposit method create/edit
- [ ] Withdraw method create/edit
- [ ] Reports center
- [ ] Settings center
- [ ] Bulk import flows
- [ ] Wallet import workflows

---

## 14. Tables, badges, filters, and empty states

### Tables
- [ ] Standardize soft modern table card styling
- [ ] Add consistent row spacing and muted separators
- [ ] Keep action menus aligned and predictable

### Badges
- [ ] Standardize status pill classes across admin pages
- [ ] Ensure overdue/pending/approved/rejected/disbursed are visually consistent

### Filters
- [ ] Use a reusable filter bar with date, branch, status, and export controls
- [ ] Place filters near the data they affect

### Empty states
- [ ] Add simple icon + explanation + CTA pattern
- [ ] Ensure empty states do not look broken

---

## 15. Branch-aware and risk-aware UX

- [ ] Add branch filters where operationally relevant
- [ ] Make overdue counts and aging buckets highly visible
- [ ] Add exception surfacing to dashboard and action center
- [ ] Keep operational risk visible without overwhelming the UI

---

## 16. Responsive QA

- [ ] Verify admin shell on desktop
- [ ] Verify admin shell on tablet
- [ ] Verify off-canvas/collapsed sidebar behavior on smaller screens
- [ ] Verify KPI cards stack cleanly
- [ ] Verify wide tables remain usable
- [ ] Verify workspace tabs wrap or degrade gracefully

---

## 17. Regression QA

- [ ] Admin sidebar loads correctly
- [ ] Active/open states work
- [ ] New workspace routes load
- [ ] Old routes still work
- [ ] Modal CRUD still submits correctly
- [ ] Request approve/reject actions still work
- [ ] Transaction forms still work
- [ ] Reports still run
- [ ] Settings tabs still save correctly
- [ ] Responsive layout is not broken
- [ ] Frontend website remains unchanged

---

## 18. Suggested implementation order

### Step A — Already started
- [x] Create visual spec from `newcavic.webp`
- [x] Create implementation checklist

### Step B — Safe UI foundation
- [x] Apply admin-only shell classes in `resources/views/layouts/app.blade.php`
- [x] Update admin sidebar presentation
- [x] Update header/breadcrumb/card baseline styles
- [x] Update responsive rules for the new shell

### Step C — Shared partials
- [ ] Finalize shared page header, tabs, filters, and empty states

### Step D — Workspace rollout
- [ ] Dashboard
- [ ] Action Center
- [ ] Members
- [ ] Loans
- [ ] Finance
- [ ] Reports
- [ ] Administration
- [ ] Messages

### Step E — Modal standardization and polish
- [ ] Modal consistency pass
- [ ] Branch-aware filters
- [ ] Exception highlighting
- [ ] Regression pass
