# Microfinance Enhancement Layer

## Purpose
This document upgrades the **CAVIC** admin UI/UX plan from a strong generic operational redesign to a much stronger **microfinance-specific admin experience**.

It is intentionally scoped to the **admin role only for now**. Future role-specific variants can be derived later for teller, credit officer, collector, or branch manager.

---

# 1. Why This Layer Exists

A good admin redesign improves navigation.
A great microfinance admin redesign improves:
- loan lifecycle visibility
- overdue and collections handling
- disbursement readiness
- member onboarding and KYC visibility
- branch-aware operations
- cash and reconciliation awareness
- exception-driven workflows
- executive and operational KPI visibility

This enhancement layer should be applied on top of:
- `AGENTS.md`
- `docs/admin-ux/current-navigation-audit.md`
- `docs/admin-ux/target-information-architecture.md`

---

# 2. Admin-Only Scope

## In scope now
- admin dashboard enhancements
- admin action center enhancements
- member onboarding/KYC visibility for admin
- loan pipeline, disbursement, and collections UX for admin
- finance/teller-like admin workflows
- reconciliation and exception visibility
- admin reporting enhancements

## Out of scope for now
- dedicated teller portal
- dedicated credit officer workspace
- dedicated collector workspace
- dedicated branch manager workspace
- personalized role-based menus beyond admin

## Rule
Design should still be modular enough that these role-specific experiences can be carved out later.

---

# 3. Target Upgrade Themes

## 3.1 Move from CRUD navigation to operational command center
The admin UI should not feel like a collection of forms and lists.
It should feel like a command center for:
- onboarding
- approval
- disbursement
- collections
- finance control
- reporting

## 3.2 Make time-sensitive work highly visible
Admins should instantly see:
- what is pending approval
- what is due today
- what is overdue
- what is ready for disbursement
- what is blocked by missing KYC or approval
- what financial exceptions require intervention

## 3.3 Make status central to UX
Status should not be hidden in tables only.
Use:
- stage chips
- aging badges
- exception flags
- due-today indicators
- blocked / ready indicators

---

# 4. Dashboard Upgrade Requirements

## 4.1 Dashboard must support two layers
### Executive layer
- active members
- active borrowers
- total portfolio outstanding
- overdue amount
- PAR-style exposure summary
- disbursements this month
- collections this month
- deposit/withdraw trend
- current liquidity / cash position

### Operational layer
- pending member requests
- pending loans
- ready-for-disbursement loans
- due today
- overdue cases
- pending financial requests
- reconciliation exceptions
- branch exceptions or branch workload summary

## 4.2 Dashboard cards should answer these questions fast
- What needs action today?
- What is risky right now?
- Which area is stuck?
- Is cash/liquidity healthy?
- Which branch needs attention?

## 4.3 Recommended dashboard widgets
- Pending approvals summary
- Due today / overdue summary
- Portfolio outstanding vs overdue
- Disbursement trend
- Collections trend
- Branch performance snapshot
- Finance exception panel

---

# 5. Action Center Upgrade Requirements

## 5.1 Action Center should be truly operational
It should not just list requests.
It should prioritize action.

## 5.2 Required queues
- Member Requests
- Pending Loans
- Ready for Disbursement
- Deposit Requests
- Withdraw Requests
- Due Today
- Overdue Follow-up
- Exceptions

## 5.3 Exception examples
- loan approved but missing required setup
- repayment posting issue
- request with incomplete data
- reconciliation mismatch
- incomplete KYC blocking activation

## 5.4 UX rules
- due today and overdue should rank above less urgent items
- approve/reject/disburse actions should be one click away where safe
- details should open in modal or large modal when possible

---

# 6. Members Workspace Upgrade Requirements

## 6.1 Members must show lifecycle, not only records
Recommended lifecycle markers:
- applicant
- pending approval
- incomplete KYC
- active
- dormant
- active borrower
- overdue borrower

## 6.2 Required tabs or filters
- All Members
- Onboarding / Requests
- KYC & Documents
- Active Borrowers
- Overdue Exposure
- Branches
- Leaders
- Import
- Custom Fields

## 6.3 Key questions the admin should answer quickly
- Who still needs approval?
- Who is blocked by missing KYC?
- Which members already have active loans?
- Which members are tied to overdue accounts?
- Which branch has onboarding issues?

---

# 7. Loans Workspace Upgrade Requirements

## 7.1 Loans must become a lifecycle pipeline
Recommended stages:
- Draft
- Submitted
- Under Review
- Approved
- Ready for Disbursement
- Disbursed / Active
- Overdue
- Closed
- Rejected

## 7.2 Required tabs
- Pipeline
- Disbursements
- Repayments
- Due / Upcoming
- Collections
- Loan Products
- Approvals
- Calculator

## 7.3 Collections requirements
Collections should show:
- due today
- overdue buckets (e.g. 1–7, 8–30, 31–60, 61+ days)
- total overdue amount
- next action needed
- quick link to loan/member detail
- quick repayment posting where allowed

## 7.4 Disbursement requirements
Disbursement tab should show:
- approved loans awaiting disbursement
- blocked disbursements
- missing prerequisites
- readiness indicators

## 7.5 Key questions the admin should answer quickly
- What is stuck in approval?
- What is approved but not disbursed?
- What is due today?
- What is overdue and how severe?
- Which loans need immediate intervention?

---

# 8. Finance Workspace Upgrade Requirements

## 8.1 Finance must support admin cash operations better
Even without a dedicated teller role yet, admin should have visibility into:
- cash movement
- pending requests
- branch cash position
- unresolved financial exceptions
- balancing / reconciliation status

## 8.2 Required tabs
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

## 8.3 Teller / Cash Ops requirements
Inside admin scope this should support:
- quick posting awareness
- cash in / cash out visibility
- branch cash summary
- end-of-day readiness indicators

## 8.4 Reconciliation requirements
Show:
- unresolved mismatches
- suspicious balances
- missing confirmations
- posted vs expected summaries
- unresolved finance exceptions

## 8.5 Key questions the admin should answer quickly
- What money moved today?
- Which requests are still pending?
- Is branch/admin cash balanced?
- Are there finance exceptions that need intervention?

---

# 9. Reports Upgrade Requirements

## 9.1 Reports must answer business questions, not just expose tables
Recommended reporting categories:
- Executive KPIs
- Portfolio
- Collections
- Accounts
- Transactions
- Expenses
- Banking
- Branch Performance
- Revenue

## 9.2 High-value report questions
- How much of the portfolio is overdue?
- What was disbursed this period?
- What was collected this period?
- Which branch has the highest overdue burden?
- What is current cash / liquidity status?
- Where are the operational bottlenecks?

## 9.3 Admin reporting priorities
- due today and overdue trend views
- branch comparison views
- disbursement vs collection trends
- liquidity / cash summary

---

# 10. Cross-Cutting UX Patterns Required

## 10.1 Status chips
Use visible chips for:
- Pending
- Under Review
- Approved
- Ready for Disbursement
- Active
- Due Today
- Overdue
- Blocked
- Completed
- Rejected

## 10.2 Aging badges
Collections and repayments should use aging buckets that are visible at list level.

## 10.3 Exception banners / panels
Where applicable, surface:
- missing KYC
- failed posting
- mismatch detected
- incomplete approval chain
- disbursement blocked

## 10.4 Quick actions
High-frequency admin actions should be easy to access:
- Approve
- Reject
- Disburse
- Post Repayment
- Review Request
- Open Member
- Open Loan

---

# 11. Recommended Backlog Additions

To raise the plan into top-tier territory, add these implementation tickets:

- UX-032 Add admin loan lifecycle pipeline and status model to workspace UX
- UX-033 Add collections management tab inside Loans
- UX-034 Add disbursement queue and readiness visibility
- UX-035 Add member onboarding and KYC visibility to Members workspace
- UX-036 Add teller / cash operations tab inside Finance
- UX-037 Add reconciliation and day-end visibility inside Finance
- UX-038 Add executive KPI dashboard layer for admin
- UX-039 Add branch performance and exception visibility across dashboard/reports

---

# 12. Quality Standard Target

After applying this enhancement layer, the admin plan should feel much closer to:
- a microfinance operations console
- a portfolio monitoring dashboard
- a branch-aware financial control center

It should feel less like:
- a generic CRUD admin panel
- a loosely grouped accounting back office

---

# 13. Definition of Success

This enhancement layer is successful when the admin can answer these questions in seconds:
- What needs attention today?
- What is overdue?
- What is ready to disburse?
- What is blocked and why?
- Is onboarding/KYC getting stuck?
- Are cash and finance operations healthy?
- Which branch or area is under pressure?

---

# 14. Related Documents
- `AGENTS.md`
- `docs/admin-ux/current-navigation-audit.md`
- `docs/admin-ux/target-information-architecture.md`
