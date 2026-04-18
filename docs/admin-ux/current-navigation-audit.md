# Current Admin Navigation Audit

## Purpose
This document captures the current admin navigation structure for **CAVIC** before the UI/UX restructuring. It serves as a baseline so future refactors preserve functionality while improving usability.

## Scope
- Admin sidebar navigation
- Related admin routes
- Current grouping problems
- Existing modal-capable CRUD areas
- High-risk dependencies to preserve during refactor

---

# 1. Current Admin Sidebar Snapshot

Primary file:
- `resources/views/layouts/menus/admin.blade.php`

## Current top-level items
1. Dashboard
2. Wallets
3. Branches
4. Members
5. Loans
6. Upcoming Payments
7. Loan Repayments
8. Accounts
9. Deposit
10. Withdraw
11. Transactions
12. Expense
13. Deposit Methods
14. Withdraw Methods
15. Bank Accounts
16. Messages
17. Reports
18. System Users
19. System Settings

## Approximate navigation size
- Top-level items: **19**
- Route references in menu: **~53**

## Immediate UX issues
- Too many top-level choices
- Actions mixed with navigation
- Financial workflows split across too many sections
- Loan workflows split across too many sections
- Reports overloaded in sidebar
- Configuration items fragmented

---

# 2. Current Menu Structure by Section

## Dashboard
- `dashboard.index`

## Wallets
- `wallets.index`

## Branches
- `branches.index`

## Members
- `members.index`
- `members.create`
- `members.import`
- `custom_fields.index` for `members`
- `members.pending_requests`

## Loans
- `loans.index`
- `loans.filter` with `pending`
- `loans.filter` with `active`
- `loans.admin_calculator`
- `loan_products.index`
- `custom_fields.index` for `loans`

## Upcoming Payments
- `loans.upcoming_loan_repayments`

## Loan Repayments
- `loan_payments.index`

## Accounts
- `savings_accounts.index`
- `interest_calculation.calculator`
- `savings_products.index`

## Deposit
- `transactions.create?type=deposit`
- `deposit_requests.index`

## Withdraw
- `transactions.create?type=withdraw`
- `withdraw_requests.index`

## Transactions
- `transactions.create`
- `transactions.index`
- `transaction_categories.index`

## Expense
- `expenses.index`
- `expense_categories.index`

## Deposit Methods
- `automatic_methods.index`
- `deposit_methods.index`

## Withdraw Methods
- `withdraw_methods.index`

## Bank Accounts
- `bank_accounts.index`
- `bank_transactions.index`

## Messages
- `messages.compose`
- `messages.inbox`
- `messages.sent`

## Reports
- `reports.account_statement`
- `reports.account_balances`
- `reports.loan_report`
- `reports.loan_due_report`
- `reports.loan_repayment_report`
- `reports.transactions_report`
- `reports.expense_report`
- `reports.cash_in_hand`
- `reports.bank_transactions`
- `reports.bank_balances`
- `reports.revenue_report`

## System Users
- `users.index`
- `roles.index`

## System Settings
- `settings.index`
- `currency.index`
- `leaders.index`
- `email_templates.index`

---

# 3. Current Route Families Relevant to Admin UX

## Dashboard and widgets
- `dashboard.index`
- `dashboard.total_customer_widget`
- `dashboard.deposit_requests_widget`
- `dashboard.withdraw_requests_widget`
- `dashboard.loan_requests_widget`
- `dashboard.expense_overview_widget`
- `dashboard.deposit_withdraw_analytics`
- `dashboard.recent_transaction_widget`
- `dashboard.due_loan_list`
- `dashboard.active_loan_balances`

## Members
- `members.*`
- `custom_fields.*`
- `member_documents.*`
- `branches.*`
- `leaders.*`

## Loans
- `loans.*`
- `loan_products.*`
- `loan_payments.*`
- `loan_collaterals.*`
- `guarantors.*`
- `loan_approvals.*`
- `loan_approver_settings.*` (restored in `routes/web.php`; available for loan approval configuration)

## Finance
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

## Reports
- `reports.*`

## Administration
- `users.*`
- `roles.*`
- `permission.*`
- `settings.*`
- `currency.*`
- `email_templates.*`

## Messages
- `messages.*`

---

# 4. Existing Modal-Capable CRUD Areas

The app already has modal infrastructure in:
- `resources/views/layouts/app.blade.php`
  - `#main_modal`
  - `#secondary_modal`

## Existing admin modal views identified
- `bank_account/modal/create.blade.php`
- `bank_account/modal/edit.blade.php`
- `bank_account/modal/view.blade.php`
- `bank_transaction/modal/create.blade.php`
- `bank_transaction/modal/edit.blade.php`
- `bank_transaction/modal/view.blade.php`
- `branch/modal/create.blade.php`
- `branch/modal/edit.blade.php`
- `branch/modal/view.blade.php`
- `currency/modal/create.blade.php`
- `currency/modal/edit.blade.php`
- `currency/modal/view.blade.php`
- `custom_field/modal/create.blade.php`
- `custom_field/modal/edit.blade.php`
- `expense/modal/create.blade.php`
- `expense/modal/edit.blade.php`
- `expense/modal/view.blade.php`
- `expense_category/modal/create.blade.php`
- `expense_category/modal/edit.blade.php`
- `guarantor/modal/create.blade.php`
- `guarantor/modal/edit.blade.php`
- `leader/modal/form.blade.php`
- `loan/modal/approver_setting.blade.php`
- `member/modal/accept_request.blade.php`
- `member_documents/modal/create.blade.php`
- `member_documents/modal/edit.blade.php`
- `member_documents/modal/view.blade.php`
- `savings_accounts/modal/create.blade.php`
- `savings_accounts/modal/edit.blade.php`
- `savings_accounts/modal/view.blade.php`
- `savings_product/modal/create.blade.php`
- `savings_product/modal/edit.blade.php`
- `savings_product/modal/view.blade.php`
- `transaction_category/modal/create.blade.php`
- `transaction_category/modal/edit.blade.php`
- `transaction_category/modal/view.blade.php`
- `user/role/modal/create.blade.php`
- `user/role/modal/edit.blade.php`
- `user/role/modal/view.blade.php`

## Implication
A modal-first UX improvement is feasible without rebuilding the interaction model from scratch.

---

# 5. Existing Full-Page Heavy Workflows

These are currently implemented as full pages and should generally remain full pages during early refactor phases:
- Member create/edit/view
- Loan create/edit/view
- Loan product create/edit
- Transaction create/edit
- Deposit method create/edit
- Withdraw method create/edit
- Reports screens
- Settings screen
- Wallet import
- Member import

---

# 6. Current UX Pain Points by Domain

## Members
Problems:
- Member List, Add Member, Import, Requests, Branches, and Leaders are split across sidebar and unrelated areas.
- Member Requests are operational items but live under Members only.
- Leaders are under System Settings, which is not intuitive.

## Loans
Problems:
- Loans, Upcoming Payments, and Loan Repayments are split into separate top-level sections.
- Loan Products and Calculator are mixed with loan list navigation.
- Pending loan work is not centralized well.

## Finance
Problems:
- Deposit, Withdraw, Transactions, Accounts, Wallets, Banking, and Methods are fragmented.
- Deposit and Withdraw are presented as separate modules instead of transaction variants.
- Payment methods are split by implementation type, not by user mental model.

## Reports
Problems:
- Too many report links in sidebar.
- Reports are not grouped by business purpose.
- Users must remember which report name maps to which business question.

## Administration
Problems:
- Users and Roles are separated from Settings, Currency, Templates, and Leaders.
- Leaders are likely organizational/member-related, not system-settings related.

## Messages
Problems:
- Compose is in navigation even though it is an action, not a destination.

---

# 7. Action Links That Should Not Stay in Sidebar

These are currently exposed like navigation but are really actions:
- `members.create`
- `members.import`
- `transactions.create`
- `transactions.create?type=deposit`
- `transactions.create?type=withdraw`
- `messages.compose`

These should be relocated to page-level buttons in the future UX.

---

# 8. Known Technical Constraints

## Layout and menu system
- Menu rendered through `resources/views/layouts/app.blade.php`
- User-type-specific menu include: `layouts.menus.{user_type}`
- Admin-specific menu file must continue to work within this structure

## Breadcrumb system
Current breadcrumb file:
- `resources/views/layouts/others/breadcrumbs.blade.php`

Constraint:
- Current implementation is URL-segment based
- It is weak for workspace/tabbed pages
- Future workspace pages should support explicit breadcrumbs while keeping backward compatibility

## Modal system
Constraint:
- Existing modal system should be reused
- Avoid introducing a second modal framework

## Tenant-aware app behavior
Constraint:
- Navigation and new workspace pages must preserve tenant-aware route behavior
- Existing route names should remain valid unless explicitly redesigned by user request

---

# 9. High-Risk Areas to Preserve During Refactor

Agents must be careful not to break:
- badge counters in sidebar
- modal form submission behavior
- AJAX table/data loading routes
- approval/reject workflows
- request detail views/download links
- settings save flows
- role and permission management
- tenant-aware URLs
- existing resource route expectations in Blade views

---

# 10. Recommended Baseline Screenshots to Capture

Before implementation, capture screenshots of:
- Dashboard
- Full admin sidebar
- Members list
- Member detail view
- Loans list
- Loan detail view
- Transactions create page
- Deposit Requests list
- Withdraw Requests list
- Reports menu and one report page
- Settings page
- Roles list

---

# 11. Audit Summary

## Current state
The admin UI is functionally rich but navigation-heavy.

## Structural problems
- too many top-level items
- poor grouping by workflow
- action links in sidebar
- duplicated mental effort for related tasks

## Opportunity
Because the app already contains:
- tabbed pages
- modal CRUD patterns
- modular route families

…it is well-positioned for an incremental UX refactor without a full rewrite.

---

# 12. Next Documents
Use this audit together with:
- `docs/admin-ux/target-information-architecture.md`
- `AGENTS.md`
