# CAVIC Admin UX Final Regression Checklist

_Last updated: 2026-04-17_

## Purpose
Use this checklist for final browser-based admin QA after the admin-first UX rollout.

## Route and render verification already completed
The following were confirmed by CLI route checks and render smoke tests:

### Core admin workspaces
- [x] `dashboard.index`
- [x] `action_center.index`
- [x] `members.workspace`
- [x] `loans.workspace`
- [x] `finance.index`
- [x] `reports.index`
- [x] `administration.index`

### Collections follow-up routes
- [x] `loan_collection_follow_ups.create`
- [x] `loan_collection_follow_ups.store`

### Loan approver settings routes
- [x] `loan_approver_settings.index`
- [x] `loan_approver_settings.create`
- [x] `loan_approver_settings.store`
- [x] `loan_approver_settings.edit`
- [x] `loan_approver_settings.update`
- [x] `loan_approver_settings.destroy`

### Modal/detail routes used from admin operations
- [x] `deposit_requests.show`
- [x] `withdraw_requests.show`
- [x] `messages.show`
- [x] `transactions.show`
- [x] `bank_transactions.show`

### Supporting admin routes used by workspace links
- [x] `members.pending_requests`
- [x] `users.index`
- [x] `roles.index`
- [x] `currency.index`
- [x] `email_templates.index`
- [x] `leaders.index`
- [x] `wallets.index`
- [x] `savings_accounts.index`
- [x] `savings_products.index`
- [x] `loan_products.index`
- [x] `loan_payments.index`
- [x] `loan_payments.create`
- [x] `loans.upcoming_loan_repayments`
- [x] `permission.index`
- [x] `settings.index`
- [x] `branches.index`
- [x] `automatic_methods.index`
- [x] `interest_calculation.calculator`
- [x] `expenses.index`
- [x] `expense_categories.index`
- [x] `transaction_categories.index`
- [x] `deposit_requests.index`
- [x] `withdraw_requests.index`

## Browser QA checklist

### 1. Navigation
- [ ] Sidebar shows task-based workspaces cleanly for admin
- [ ] Dashboard, Action Center, Members, Loans, Finance, Reports, Administration, Messages all open correctly
- [ ] No action-only links feel misplaced in sidebar
- [ ] Breadcrumbs are readable and skip noisy tenant/dashboard segments properly

### 2. Dashboard
- [ ] Quick links open correct workspaces
- [ ] Today’s Priorities values look reasonable
- [ ] Exception cards render without layout breaks
- [ ] Collections priority queue shows last follow-up chips correctly
- [ ] Promise Follow-up Queue exports via toolbar
- [ ] Recent Resolutions exports via toolbar
- [ ] Branch and Collector Follow-up Performance export via toolbar

### 3. Action Center
- [ ] Pending members queue opens correctly
- [ ] Deposit request modal opens from queue
- [ ] Withdraw request modal opens from queue
- [ ] Collections priority queue log action opens follow-up modal
- [ ] Branch Collections Pressure / Follow-up Performance tables render correctly
- [ ] Recent Resolutions and Promise Follow-up Queue export correctly

### 4. Members workspace
- [ ] Pending onboarding table opens full queue correctly
- [ ] Missing document visibility appears accurate
- [ ] Branch summary and leaders preview render correctly
- [ ] Major list links open expected legacy pages

### 5. Loans workspace
- [ ] Pipeline tab shows pending, active, ready, overdue segments clearly
- [ ] Blocked Before Disbursement table shows blocker chips correctly
- [ ] Due Today / Next 7 Days tables render correctly
- [ ] Collections analytics cards respond to date filter only for analytics layer
- [ ] Collection follow-up modal saves successfully
- [ ] Promise Follow-up Queue exports correctly
- [ ] Recent Resolutions exports correctly
- [ ] Collector and Branch performance exports correctly
- [ ] Approver Settings link opens the restored configuration page

### 6. Loan approver settings
- [ ] Approver settings index opens in tenant admin without route error
- [ ] Configure Approvers button opens AJAX modal
- [ ] Edit action opens AJAX modal
- [ ] Non-AJAX direct page access works for create/edit fallback
- [ ] Save/update redirects or modal responses behave correctly

### 7. Finance workspace
- [ ] Pending cash and bank transaction counts feel accurate
- [ ] Banking/reconciliation links open correctly
- [ ] Savings product/account summary cards render correctly
- [ ] Gateway/method links open expected management pages

### 8. Reports Center
- [ ] Report grouping is understandable and links work
- [ ] Highlight cards and branch reporting snapshot render correctly
- [ ] No dead report links remain

### 9. Administration workspace
- [ ] User, role, currency, template tables render correctly
- [ ] Settings shortcuts open expected destinations
- [ ] Permission/roles navigation remains intact

### 10. Exports and filters
- [ ] `table-export` toolbars appear on newly enhanced collections tables
- [ ] CSV export filenames are meaningful
- [ ] Excel export behaves consistently with existing export helper
- [ ] Collection date range filter updates analytics-only sections without changing live snapshot counts

### 11. Safety checks
- [ ] App still behaves correctly before follow-up migration in guarded areas
- [ ] Tenant-aware route generation stays intact in browser
- [ ] AJAX modals still use `#main_modal` / `#secondary_modal` infrastructure
- [ ] No major JS regressions in table/export/modal behavior

## Known non-blocking CLI-only issue
- Synthetic render warning still appears for `membership_type` on null in a compiled view.
- This has not blocked successful tenant admin render smoke checks.

## Recommended sign-off rule
Ship admin UX closeout once:
1. all browser checklist items above pass,
2. loan approver settings flow is verified live,
3. collections resolution semantics are accepted by business stakeholders.
