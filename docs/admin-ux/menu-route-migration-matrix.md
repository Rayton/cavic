# Menu / Route Migration Matrix

## Purpose
This document maps the current **CAVIC** admin navigation and route families to the target admin information architecture.

It is intended to help developers and coding agents:
- preserve all existing functionality
- move items into the correct future workspace
- decide whether an item should be a full page, modal, or internal helper
- avoid losing routes during the admin UX refactor

Use this together with:
- `AGENTS.md`
- `docs/admin-ux/current-navigation-audit.md`
- `docs/admin-ux/target-information-architecture.md`
- `docs/admin-ux/microfinance-enhancement-layer.md`
- `docs/admin-ux/developer-backlog.md`

---

# 1. Migration Rules

## Rule 1 — Keep routes stable first
In the first rollout:
- preserve current route names
- preserve current controller actions
- change navigation before changing route contracts

## Rule 2 — Move actions out of sidebar
Do not keep these as primary sidebar items:
- add/create actions
- import actions
- compose actions
- transaction subtype entry points

These should become:
- page header buttons
- tab-level CTAs
- quick actions inside workspace pages

## Rule 3 — Use workspaces as the new destination
The sidebar should lead to workspace routes such as:
- `action_center.index`
- `members.workspace`
- `loans.workspace`
- `finance.index`
- `reports.index`
- `administration.index`

## Rule 4 — Preserve internal helper routes
AJAX/data/helper routes should stay internal and should not appear in sidebar navigation.

## Rule 5 — Prefer microfinance workflow grouping
When in doubt, group by operational job-to-be-done:
- onboarding
- approval
- disbursement
- repayment
- collections
- reconciliation
- reporting

---

# 2. Target Top-Level Navigation

## Future admin sidebar for CAVIC
1. Dashboard
2. Action Center
3. Members
4. Loans
5. Finance
6. Reports
7. Administration
8. Messages

---

# 3. Current-to-Target Matrix

## 3.1 Dashboard and dashboard widgets

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `dashboard.index` | Dashboard | Dashboard | Overview | Page | Yes | Primary admin landing page |
| `dashboard.total_customer_widget` | Dashboard widget | Dashboard | KPI layer | Internal | Yes | keep internal |
| `dashboard.deposit_requests_widget` | Dashboard widget | Dashboard | KPI layer / Action Center summary | Internal | Yes | keep internal |
| `dashboard.withdraw_requests_widget` | Dashboard widget | Dashboard | KPI layer / Action Center summary | Internal | Yes | keep internal |
| `dashboard.loan_requests_widget` | Dashboard widget | Dashboard | KPI layer / Action Center summary | Internal | Yes | keep internal |
| `dashboard.expense_overview_widget` | Dashboard widget | Dashboard | KPI layer | Internal | Yes | keep internal |
| `dashboard.deposit_withdraw_analytics` | Dashboard widget | Dashboard | Finance summary | Internal | Yes | keep internal |
| `dashboard.recent_transaction_widget` | Dashboard widget | Dashboard | Activity | Internal | Yes | keep internal |
| `dashboard.due_loan_list` | Dashboard widget | Dashboard | Due today / risk | Internal | Yes | keep internal |
| `dashboard.active_loan_balances` | Dashboard widget | Dashboard | Portfolio summary | Internal | Yes | keep internal |

---

## 3.2 Action Center

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `action_center.index` *(new)* | n/a | Action Center | Overview | Page | New | new workspace route |
| `members.pending_requests` | Members | Action Center | Member Requests | Page | Yes | also accessible under Members |
| `members.accept_request` | Members | Action Center | Member Requests | Modal / Action | Yes | already modal-friendly |
| `members.reject_request` | Members | Action Center | Member Requests | Action | Yes | keep |
| `loans.filter` with `pending` | Loans | Action Center | Pending Loans | Page | Yes | also accessible under Loans |
| `loan_approvals.index` | not surfaced well | Action Center | Pending Loans / Approvals | Page | Yes | keep |
| `loan_approvals.show` | approval detail | Action Center | Approval Detail | Detail Modal / Page | Yes | prefer large modal if practical |
| `loan_approvals.approve` | approval action | Action Center | Approval action | Action | Yes | keep |
| `loan_approvals.reject` | approval action | Action Center | Approval action | Action | Yes | keep |
| `deposit_requests.index` | Deposit | Action Center | Deposit Requests | Page | Yes | also accessible under Finance |
| `deposit_requests.show` | Deposit Requests detail | Action Center | Deposit Request Detail | Detail Modal | Yes | move to quicker review flow |
| `deposit_requests.approve` | Deposit Requests action | Action Center | Deposit Requests | Action | Yes | keep |
| `deposit_requests.approve_group` | Deposit Requests action | Action Center | Deposit Requests | Action | Yes | keep |
| `deposit_requests.reject` | Deposit Requests action | Action Center | Deposit Requests | Action | Yes | keep |
| `withdraw_requests.index` | Withdraw | Action Center | Withdraw Requests | Page | Yes | also accessible under Finance |
| `withdraw_requests.show` | Withdraw Requests detail | Action Center | Withdraw Request Detail | Detail Modal | Yes | move to quicker review flow |
| `withdraw_requests.approve` | Withdraw Requests action | Action Center | Withdraw Requests | Action | Yes | keep |
| `withdraw_requests.reject` | Withdraw Requests action | Action Center | Withdraw Requests | Action | Yes | keep |
| `loans.upcoming_loan_repayments` | Upcoming Payments | Action Center | Due Today / Upcoming | Page | Yes | also accessible under Loans |

---

## 3.3 Members workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `members.workspace` *(new)* | n/a | Members | Overview | Page | New | new workspace route |
| `members.index` | Members | Members | All Members | Page | Yes | keep existing listing |
| `members.create` | Members | Members | All Members > Add Member | Page | Yes | remove from sidebar |
| `members.store` | Members | Members | All Members | Action | Yes | keep |
| `members.show` | Members | Members | Member Detail | Page | Yes | keep full page |
| `members.edit` | Members | Members | Member Detail / Edit | Page | Yes | keep |
| `members.update` | Members | Members | Member Detail / Edit | Action | Yes | keep |
| `members.destroy` | Members | Members | All Members | Action | Yes | keep |
| `members.import` | Members | Members | Import | Page | Yes | move inside workspace tab |
| `members.send_email` | Member Detail | Members | Member Detail > Communication | Action | Yes | keep |
| `members.send_sms` | Member Detail | Members | Member Detail > Communication | Action | Yes | keep |
| `members.get_table_data` | data helper | Members | Internal | Internal | Yes | keep internal |
| `members.get_member_transaction_data` | data helper | Members | Internal | Internal | Yes | keep internal |
| `custom_fields.index` for members | Members | Members | Custom Fields | Page / Modal Hybrid | Yes | keep |
| `custom_fields.create` | Members | Members | Custom Fields | Modal | Yes | keep |
| `custom_fields.store` | Members | Members | Custom Fields | Action | Yes | keep |
| `custom_fields.edit` | Members | Members | Custom Fields | Modal | Yes | keep |
| `custom_fields.update` | Members | Members | Custom Fields | Action | Yes | keep |
| `custom_fields.destroy` | Members | Members | Custom Fields | Action | Yes | keep |
| `branches.index` | Branches | Members | Branches | Page / Modal Hybrid | Yes | move under Members |
| `branches.create` | Branches | Members | Branches | Modal | Yes | already modal-ready |
| `branches.store` | Branches | Members | Branches | Action | Yes | keep |
| `branches.show` | Branches | Members | Branches | Modal | Yes | keep |
| `branches.edit` | Branches | Members | Branches | Modal | Yes | keep |
| `branches.update` | Branches | Members | Branches | Action | Yes | keep |
| `branches.destroy` | Branches | Members | Branches | Action | Yes | keep |
| `leaders.index` | System Settings | Members | Leaders | Page / Modal Hybrid | Yes | move under Members |
| `leaders.create` | System Settings | Members | Leaders | Modal | Yes | keep |
| `leaders.store` | System Settings | Members | Leaders | Action | Yes | keep |
| `leaders.edit` | System Settings | Members | Leaders | Modal | Yes | keep |
| `leaders.update` | System Settings | Members | Leaders | Action | Yes | keep |
| `leaders.destroy` | System Settings | Members | Leaders | Action | Yes | keep |
| `member_documents.index` | Member Detail | Members | Documents | Page section | Yes | keep within member detail flow |
| `member_documents.create` | Member Detail | Members | Documents | Modal | Yes | keep modal |
| `member_documents.store` | Member Detail | Members | Documents | Action | Yes | keep |
| `member_documents.edit` | Member Detail | Members | Documents | Modal | Yes | keep |
| `member_documents.update` | Member Detail | Members | Documents | Action | Yes | keep |
| `member_documents.destroy` | Member Detail | Members | Documents | Action | Yes | keep |

---

## 3.4 Loans workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `loans.workspace` *(new)* | n/a | Loans | Overview | Page | New | new workspace route |
| `loans.index` | Loans | Loans | Pipeline | Page | Yes | keep |
| `loans.filter` with `active` | Loans | Loans | Pipeline | Page | Yes | expose as stage/filter |
| `loans.filter` with `pending` | Loans | Loans | Pipeline / Approvals | Page | Yes | expose as stage/filter |
| `loans.create` | Loans | Loans | Pipeline > New Loan | Page | Yes | keep full page |
| `loans.store` | Loans | Loans | Pipeline | Action | Yes | keep |
| `loans.show` | Loans | Loans | Loan Detail | Page | Yes | keep full page |
| `loans.edit` | Loans | Loans | Loan Detail / Edit | Page | Yes | keep |
| `loans.update` | Loans | Loans | Loan Detail / Edit | Action | Yes | keep |
| `loans.destroy` | Loans | Loans | Pipeline | Action | Yes | keep |
| `loans.approve` | Loans | Loans | Approvals | Page / Action | Yes | keep |
| `loans.reject` | Loans | Loans | Approvals | Action | Yes | keep |
| `loans.approval-data` | helper | Loans | Internal | Internal | Yes | keep internal |
| `loans.admin_calculator` | Loans | Loans | Calculator | Page | Yes | keep |
| `loans.calculate` | calculator action | Loans | Calculator | Action | Yes | keep |
| `loans.upcoming_loan_repayments` | Upcoming Payments | Loans | Due / Upcoming | Page | Yes | also used in Action Center |
| `loan_products.index` | Loans | Loans | Loan Products | Page | Yes | keep |
| `loan_products.create` | Loans | Loans | Loan Products > Add | Page | Yes | keep full page |
| `loan_products.store` | Loans | Loans | Loan Products | Action | Yes | keep |
| `loan_products.edit` | Loans | Loans | Loan Products | Page | Yes | keep |
| `loan_products.update` | Loans | Loans | Loan Products | Action | Yes | keep |
| `loan_products.destroy` | Loans | Loans | Loan Products | Action | Yes | keep |
| `loan_payments.index` | Loan Repayments | Loans | Repayments | Page | Yes | move under Loans |
| `loan_payments.create` | Loan Repayments | Loans | Repayments > Post Repayment | Page | Yes | keep full page |
| `loan_payments.store` | Loan Repayments | Loans | Repayments | Action | Yes | keep |
| `loan_payments.show` | Loan Repayments | Loans | Repayment Detail | Page / Detail Modal | Yes | may stay page initially |
| `loan_payments.edit` | Loan Repayments | Loans | Repayments | Page | Yes | keep |
| `loan_payments.update` | Loan Repayments | Loans | Repayments | Action | Yes | keep |
| `loan_payments.destroy` | Loan Repayments | Loans | Repayments | Action | Yes | keep |
| `loan_payments.get_repayment_by_loan_id` | helper | Loans | Internal | Internal | Yes | keep internal |
| `loan_payments.get_table_data` | helper | Loans | Internal | Internal | Yes | keep internal |
| `loan_collaterals.index` | Loan Detail | Loans | Loan Detail > Collateral | Page | Yes | keep nested under loan |
| `loan_collaterals.create` | Loan Detail | Loans | Loan Detail > Collateral | Page | Yes | keep |
| `loan_collaterals.store` | Loan Detail | Loans | Loan Detail > Collateral | Action | Yes | keep |
| `loan_collaterals.show` | Loan Detail | Loans | Loan Detail > Collateral | Page | Yes | keep |
| `loan_collaterals.edit` | Loan Detail | Loans | Loan Detail > Collateral | Page | Yes | keep |
| `loan_collaterals.update` | Loan Detail | Loans | Loan Detail > Collateral | Action | Yes | keep |
| `loan_collaterals.destroy` | Loan Detail | Loans | Loan Detail > Collateral | Action | Yes | keep |
| `guarantors.create` | Loan Detail | Loans | Loan Detail > Guarantors | Modal | Yes | keep modal-first |
| `guarantors.store` | Loan Detail | Loans | Loan Detail > Guarantors | Action | Yes | keep |
| `guarantors.edit` | Loan Detail | Loans | Loan Detail > Guarantors | Modal | Yes | keep |
| `guarantors.update` | Loan Detail | Loans | Loan Detail > Guarantors | Action | Yes | keep |
| `guarantors.destroy` | Loan Detail | Loans | Loan Detail > Guarantors | Action | Yes | keep |
| `loan_approvals.index` | hidden / partial use | Loans | Approvals | Page | Yes | shared with Action Center |
| `loan_approvals.show` | approval detail | Loans | Approvals | Detail Modal / Page | Yes | shared with Action Center |
| `loan_approvals.approve` | action | Loans | Approvals | Action | Yes | keep |
| `loan_approvals.reject` | action | Loans | Approvals | Action | Yes | keep |
| `loan_approver_settings.*` | loan approver configuration | Loans | Approvals | Modal / Page | Yes | restored in `routes/web.php`; can be linked from Loans workspace |

---

## 3.5 Finance workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `finance.index` *(new)* | n/a | Finance | Overview | Page | New | new workspace route |
| `wallets.index` | Wallets | Finance | Wallets | Page | Yes | keep |
| `wallets.template` | Wallets | Finance | Wallets | Action | Yes | keep |
| `wallets.import` | Wallets | Finance | Wallets | Action | Yes | keep |
| `savings_accounts.index` | Accounts | Finance | Savings Accounts | Page | Yes | keep |
| `savings_accounts.create` | Accounts | Finance | Savings Accounts | Modal / Page Hybrid | Yes | keep |
| `savings_accounts.store` | Accounts | Finance | Savings Accounts | Action | Yes | keep |
| `savings_accounts.show` | Accounts | Finance | Savings Accounts | Modal / Page Hybrid | Yes | keep |
| `savings_accounts.edit` | Accounts | Finance | Savings Accounts | Modal / Page Hybrid | Yes | keep |
| `savings_accounts.update` | Accounts | Finance | Savings Accounts | Action | Yes | keep |
| `savings_accounts.destroy` | Accounts | Finance | Savings Accounts | Action | Yes | keep |
| `savings_accounts.get_account_by_member_id` | helper | Finance | Internal | Internal | Yes | keep internal |
| `savings_accounts.get_table_data` | helper | Finance | Internal | Internal | Yes | keep internal |
| `savings_products.index` | Accounts | Finance | Savings Accounts > Account Types | Page / Modal Table | Yes | keep |
| `savings_products.create` | Accounts | Finance | Savings Accounts > Account Types | Modal | Yes | keep |
| `savings_products.store` | Accounts | Finance | Savings Accounts > Account Types | Action | Yes | keep |
| `savings_products.show` | Accounts | Finance | Savings Accounts > Account Types | Modal | Yes | keep |
| `savings_products.edit` | Accounts | Finance | Savings Accounts > Account Types | Modal | Yes | keep |
| `savings_products.update` | Accounts | Finance | Savings Accounts > Account Types | Action | Yes | keep |
| `savings_products.destroy` | Accounts | Finance | Savings Accounts > Account Types | Action | Yes | keep |
| `transactions.index` | Transactions | Finance | Cash Transactions | Page | Yes | keep |
| `transactions.create` | Transactions / Deposit / Withdraw | Finance | Cash Transactions > New | Page | Yes | remove from sidebar |
| `transactions.store` | Transactions | Finance | Cash Transactions | Action | Yes | keep |
| `transactions.show` | Transactions | Finance | Cash Transactions > History | Page | Yes | keep |
| `transactions.edit` | Transactions | Finance | Cash Transactions | Page | Yes | keep |
| `transactions.update` | Transactions | Finance | Cash Transactions | Action | Yes | keep |
| `transactions.destroy` | Transactions | Finance | Cash Transactions | Action | Yes | keep |
| `transactions.get_table_data` | helper | Finance | Internal | Internal | Yes | keep internal |
| `transaction_categories.index` | Transactions | Finance | Cash Transactions > Categories | Page / Modal Table | Yes | keep |
| `transaction_categories.create` | Transactions | Finance | Cash Transactions > Categories | Modal | Yes | keep |
| `transaction_categories.store` | Transactions | Finance | Cash Transactions > Categories | Action | Yes | keep |
| `transaction_categories.show` | Transactions | Finance | Cash Transactions > Categories | Modal | Yes | keep |
| `transaction_categories.edit` | Transactions | Finance | Cash Transactions > Categories | Modal | Yes | keep |
| `transaction_categories.update` | Transactions | Finance | Cash Transactions > Categories | Action | Yes | keep |
| `transaction_categories.destroy` | Transactions | Finance | Cash Transactions > Categories | Action | Yes | keep |
| `deposit_requests.index` | Deposit | Finance | Requests > Deposits | Page | Yes | shared with Action Center |
| `deposit_requests.show` | Deposit | Finance | Requests > Deposits | Detail Modal | Yes | shared with Action Center |
| `deposit_requests.approve` | Deposit | Finance | Requests > Deposits | Action | Yes | keep |
| `deposit_requests.approve_group` | Deposit | Finance | Requests > Deposits | Action | Yes | keep |
| `deposit_requests.reject` | Deposit | Finance | Requests > Deposits | Action | Yes | keep |
| `deposit_requests.download_attachment` | Deposit | Finance | Requests > Deposits | Action | Yes | keep |
| `deposit_requests.destroy` | Deposit | Finance | Requests > Deposits | Action | Yes | keep |
| `withdraw_requests.index` | Withdraw | Finance | Requests > Withdrawals | Page | Yes | shared with Action Center |
| `withdraw_requests.show` | Withdraw | Finance | Requests > Withdrawals | Detail Modal | Yes | shared with Action Center |
| `withdraw_requests.approve` | Withdraw | Finance | Requests > Withdrawals | Action | Yes | keep |
| `withdraw_requests.reject` | Withdraw | Finance | Requests > Withdrawals | Action | Yes | keep |
| `withdraw_requests.destroy` | Withdraw | Finance | Requests > Withdrawals | Action | Yes | keep |
| `expenses.index` | Expense | Finance | Expenses | Page | Yes | keep |
| `expenses.create` | Expense | Finance | Expenses | Modal / Page Hybrid | Yes | keep |
| `expenses.store` | Expense | Finance | Expenses | Action | Yes | keep |
| `expenses.show` | Expense | Finance | Expenses | Modal / Page Hybrid | Yes | keep |
| `expenses.edit` | Expense | Finance | Expenses | Modal / Page Hybrid | Yes | keep |
| `expenses.update` | Expense | Finance | Expenses | Action | Yes | keep |
| `expenses.destroy` | Expense | Finance | Expenses | Action | Yes | keep |
| `expenses.get_table_data` | helper | Finance | Internal | Internal | Yes | keep internal |
| `expense_categories.index` | Expense | Finance | Expenses > Categories | Page / Modal Table | Yes | keep |
| `expense_categories.create` | Expense | Finance | Expenses > Categories | Modal | Yes | keep |
| `expense_categories.store` | Expense | Finance | Expenses > Categories | Action | Yes | keep |
| `expense_categories.edit` | Expense | Finance | Expenses > Categories | Modal | Yes | keep |
| `expense_categories.update` | Expense | Finance | Expenses > Categories | Action | Yes | keep |
| `expense_categories.destroy` | Expense | Finance | Expenses > Categories | Action | Yes | keep |
| `bank_accounts.index` | Bank Accounts | Finance | Banking | Page / Modal Table | Yes | keep |
| `bank_accounts.create` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_accounts.store` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_accounts.show` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_accounts.edit` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_accounts.update` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_accounts.destroy` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_transactions.index` | Bank Accounts | Finance | Banking | Page / Modal Table | Yes | keep |
| `bank_transactions.create` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_transactions.store` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_transactions.show` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_transactions.edit` | Bank Accounts | Finance | Banking | Modal | Yes | keep |
| `bank_transactions.update` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_transactions.destroy` | Bank Accounts | Finance | Banking | Action | Yes | keep |
| `bank_transactions.get_table_data` | helper | Finance | Internal | Internal | Yes | keep internal |
| `deposit_methods.index` | Deposit Methods | Finance | Methods | Page | Yes | move into Methods |
| `deposit_methods.create` | Deposit Methods | Finance | Methods > Offline Deposit | Page | Yes | keep full page |
| `deposit_methods.store` | Deposit Methods | Finance | Methods > Offline Deposit | Action | Yes | keep |
| `deposit_methods.edit` | Deposit Methods | Finance | Methods > Offline Deposit | Page | Yes | keep |
| `deposit_methods.update` | Deposit Methods | Finance | Methods > Offline Deposit | Action | Yes | keep |
| `deposit_methods.destroy` | Deposit Methods | Finance | Methods > Offline Deposit | Action | Yes | keep |
| `automatic_methods.index` | Deposit Methods | Finance | Methods > Online Gateways | Page | Yes | keep |
| `automatic_methods.edit` | Deposit Methods | Finance | Methods > Online Gateways | Page | Yes | keep |
| `automatic_methods.update` | Deposit Methods | Finance | Methods > Online Gateways | Action | Yes | keep |
| `withdraw_methods.index` | Withdraw Methods | Finance | Methods > Withdraw | Page | Yes | move into Methods |
| `withdraw_methods.create` | Withdraw Methods | Finance | Methods > Withdraw | Page | Yes | keep full page |
| `withdraw_methods.store` | Withdraw Methods | Finance | Methods > Withdraw | Action | Yes | keep |
| `withdraw_methods.edit` | Withdraw Methods | Finance | Methods > Withdraw | Page | Yes | keep |
| `withdraw_methods.update` | Withdraw Methods | Finance | Methods > Withdraw | Action | Yes | keep |
| `withdraw_methods.destroy` | Withdraw Methods | Finance | Methods > Withdraw | Action | Yes | keep |
| `interest_calculation.calculator` | Accounts | Finance | Interest | Page | Yes | keep |
| `interest_calculation.interest_posting` | Accounts | Finance | Interest | Action | Yes | keep |
| `interest_calculation.get_last_posting` | helper | Finance | Internal | Internal | Yes | keep internal |

---

## 3.6 Reports workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `reports.index` *(new)* | n/a | Reports | Overview | Page | New | new workspace route |
| `reports.account_statement` | Reports | Reports | Accounts | Page | Yes | keep |
| `reports.account_balances` | Reports | Reports | Accounts | Page | Yes | keep |
| `reports.loan_report` | Reports | Reports | Portfolio | Page | Yes | keep |
| `reports.loan_due_report` | Reports | Reports | Collections / Portfolio | Page | Yes | keep |
| `reports.loan_repayment_report` | Reports | Reports | Portfolio / Collections | Page | Yes | keep |
| `reports.transactions_report` | Reports | Reports | Transactions | Page | Yes | keep |
| `reports.expense_report` | Reports | Reports | Expenses | Page | Yes | keep |
| `reports.cash_in_hand` | Reports | Reports | Accounts / Executive KPIs | Page | Yes | keep |
| `reports.bank_transactions` | Reports | Reports | Banking | Page | Yes | keep |
| `reports.bank_balances` | Reports | Reports | Banking | Page | Yes | keep |
| `reports.revenue_report` | Reports | Reports | Revenue | Page | Yes | keep |

---

## 3.7 Administration workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `administration.index` *(new)* | n/a | Administration | Overview | Page | New | new workspace route |
| `users.index` | System Users | Administration | Users | Page | Yes | keep |
| `users.create` | System Users | Administration | Users > Add User | Page | Yes | keep full page |
| `users.store` | System Users | Administration | Users | Action | Yes | keep |
| `users.show` | System Users | Administration | Users | Page | Yes | keep |
| `users.edit` | System Users | Administration | Users | Page | Yes | keep |
| `users.update` | System Users | Administration | Users | Action | Yes | keep |
| `users.destroy` | System Users | Administration | Users | Action | Yes | keep |
| `users.get_table_data` | helper | Administration | Internal | Internal | Yes | keep internal |
| `roles.index` | System Users | Administration | Roles & Permissions | Page / Modal Table | Yes | keep |
| `roles.create` | System Users | Administration | Roles & Permissions | Modal | Yes | keep |
| `roles.store` | System Users | Administration | Roles & Permissions | Action | Yes | keep |
| `roles.show` | System Users | Administration | Roles & Permissions | Modal | Yes | keep |
| `roles.edit` | System Users | Administration | Roles & Permissions | Modal | Yes | keep |
| `roles.update` | System Users | Administration | Roles & Permissions | Action | Yes | keep |
| `roles.destroy` | System Users | Administration | Roles & Permissions | Action | Yes | keep |
| `permission.index` | System Users | Administration | Roles & Permissions | Page | Yes | keep |
| `permission.show` | Access Control | Administration | Roles & Permissions > Access Control | Page | Yes | keep |
| `permission.store` | Access Control | Administration | Roles & Permissions | Action | Yes | keep |
| `settings.index` | System Settings | Administration | Settings | Page | Yes | keep existing tabbed settings page |
| `settings.store_general_settings` | System Settings | Administration | Settings | Action | Yes | keep |
| `settings.store_currency_settings` | System Settings | Administration | Settings | Action | Yes | keep |
| `settings.store_email_settings` | System Settings | Administration | Settings | Action | Yes | keep |
| `settings.send_test_email` | System Settings | Administration | Settings | Action | Yes | keep |
| `settings.upload_logo` | System Settings | Administration | Settings | Action | Yes | keep |
| `currency.index` | System Settings | Administration | Currency | Page / Modal Table | Yes | keep |
| `currency.create` | System Settings | Administration | Currency | Modal | Yes | keep |
| `currency.store` | System Settings | Administration | Currency | Action | Yes | keep |
| `currency.show` | System Settings | Administration | Currency | Modal | Yes | keep |
| `currency.edit` | System Settings | Administration | Currency | Modal | Yes | keep |
| `currency.update` | System Settings | Administration | Currency | Action | Yes | keep |
| `currency.destroy` | System Settings | Administration | Currency | Action | Yes | keep |
| `email_templates.index` | System Settings | Administration | Notification Templates | Page | Yes | keep |
| `email_templates.show` | System Settings | Administration | Notification Templates | Page / Detail Modal | Yes | keep |
| `email_templates.edit` | System Settings | Administration | Notification Templates | Page | Yes | keep |
| `email_templates.update` | System Settings | Administration | Notification Templates | Action | Yes | keep |

---

## 3.8 Messages workspace

| Current Route / Route Family | Current Menu Area | Future Module | Future Tab / Destination | Interaction Type | Keep Existing Route | Notes |
|---|---|---|---|---|---|---|
| `messages.inbox` | Messages | Messages | Inbox | Page | Yes | keep |
| `messages.sent` | Messages | Messages | Sent | Page | Yes | keep |
| `messages.compose` | Messages | Messages | Compose | Page button | Yes | remove from sidebar |
| `messages.send` | Messages | Messages | Compose | Action | Yes | keep |
| `messages.show` | Messages | Messages | Message Detail | Detail Modal / Page | Yes | modal recommended later |
| `messages.reply` | Messages | Messages | Message Detail / Reply | Page / Modal | Yes | keep |
| `messages.sendReply` | Messages | Messages | Message Detail / Reply | Action | Yes | keep |
| `messages.download_attachment` | Messages | Messages | Message Detail | Action | Yes | keep |

---

# 4. Current Sidebar Links That Should Become Page-Level Actions

| Current Route | Current Sidebar Presentation | Future Placement |
|---|---|---|
| `members.create` | sidebar submenu | Members workspace page header |
| `members.import` | sidebar submenu | Members > Import tab |
| `transactions.create` | sidebar submenu | Finance > Cash Transactions |
| `transactions.create?type=deposit` | sidebar submenu | Finance > Cash Transactions quick action |
| `transactions.create?type=withdraw` | sidebar submenu | Finance > Cash Transactions quick action |
| `messages.compose` | sidebar submenu | Messages page header |

---

# 5. Routes That Must Stay Internal Only

These routes should not be exposed in sidebar navigation:

- all `get_table_data` endpoints
- `members.get_member_transaction_data`
- `savings_accounts.get_account_by_member_id`
- `transaction_categories.get_category_by_type`
- `loans.approval-data`
- `interest_calculation.get_last_posting`
- dashboard widget endpoints

---

# 6. High-Risk Migration Checks

Before cutting over navigation, verify:
- sidebar badge counts still point to the correct route destinations
- modal-backed routes still return modal-compatible responses
- legacy list pages still work if accessed directly
- request detail actions still work after modal conversion
- tenant-aware route generation remains unchanged
- route references in Blade match actual route definitions

Special attention:
- `loan_approver_settings.*` routes have been restored; keep tenant-aware access and modal behavior intact if expanded further

---

# 7. Recommended Cutover Strategy

## Step 1
Add new workspace routes and pages without removing old routes.

## Step 2
Switch sidebar navigation to workspace routes.

## Step 3
Inside workspace pages, link to existing list/detail/create routes as needed.

## Step 4
Standardize modal and quick-review flows.

## Step 5
After QA, remove obsolete sidebar duplication.

---

# 8. Definition of Migration Success

The migration is successful when:
- all current admin routes still work
- the new sidebar points to grouped workspaces
- no current feature is lost
- actions move closer to where work is done
- the admin experience in CAVIC feels more like a microfinance operations console and less like a raw CRUD backend
