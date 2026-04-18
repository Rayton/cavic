# CAVIC Admin UX Final Done / Pending Matrix

_Last updated: 2026-04-17_

## Done

### Navigation / architecture
- Admin sidebar regrouped into workspace-oriented modules
- Legacy routes preserved while workspaces act as hubs
- Shared page header and improved breadcrumbs implemented

### Dashboard
- Operational hero and quick links added
- Today’s Priorities and exception cards added
- Branch performance, collections pressure, follow-up analytics, and recent resolutions surfaced
- Collections analytics tables made exportable

### Action Center
- Live operational queues added
- Request/message modal-first detail pattern implemented
- Collections priority, branch pressure, branch/collector performance, promise queue, and recent resolutions surfaced
- Collections analytics tables made exportable

### Members workspace
- Pending members, recent members, active borrowers, branch summary, KYC/document visibility, and leaders preview implemented

### Loans workspace
- Loan lifecycle pipeline visibility implemented
- Disbursement readiness and blocker visibility implemented
- Due today / upcoming / overdue operational views implemented
- Persisted collection follow-up logging implemented
- Date-range filtered collections analytics implemented
- Promise queue, recent resolutions, branch/collector productivity, and exportable collections tables implemented
- Loan approver settings link added to workspace

### Finance workspace
- Live transactions, requests, bank transactions, finance exception cards, branch finance pressure, savings summaries, and method/gateway links implemented

### Reports Center
- Grouped reporting hub implemented
- Descriptions, highlights, and branch reporting snapshot added

### Administration workspace
- Users, roles, settings summary, currencies, and notification templates surfaced with real counts and preview tables

### Supporting operational UX
- AJAX modal detail reuse for deposit requests, withdraw requests, and messages
- Follow-up persistence migration, model, modal, controller, and guarded behavior implemented
- Loan approver settings routes restored and page/modal fallback implemented

## Pending

### Browser QA
- Full tenant-admin browser walkthrough for all major workspaces
- Modal interaction QA for detail and follow-up flows
- Export button QA in browser across new collections tables

### Business validation
- Confirm final semantics for `Resolved` vs recovered vs promise kept
- Confirm disbursement readiness assumptions with business rules
- Confirm whether manual resolution should remain unrestricted

### Polish
- Minor visual consistency pass on card/table spacing and helper copy
- Optional extra help text around analytics-only date filtering
- Optional extension of collections resolution analytics into Reports Center

### Technical cleanup
- Investigate the existing compiled-view `membership_type` null warning outside this UX scope
- Optional final route/reference sweep beyond primary admin workspaces

## Current assessment
- Admin-first UX implementation: **substantially complete**
- Remaining work type: **QA + polish + business confirmation**
- Approximate completion: **97%**
