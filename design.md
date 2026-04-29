# CAVIC Admin UI Design Upgrade Spec

**Source reference:** `newcavic.webp`
**Created:** 2026-04-18
**Purpose:** Translate the visual direction in `newcavic.webp` into a practical UI spec for upgrading the Laravel admin experience without breaking existing routes or workflows.

---

## 1. What this image shows

The reference image presents a **modern financial operations dashboard** with these clear traits:

- a soft neutral app background
- a large rounded white application shell
- a calm, compact **left sidebar navigation**
- a clean top utility area for notifications and user profile
- prominent **KPI summary cards** across the top
- a modular grid of analytics cards
- large content panels with subtle borders and rounded corners
- quiet colors with a muted teal primary accent
- generous spacing and low visual noise
- strong emphasis on readability over decoration

This design direction is a strong fit for CAVIC because it feels like a **microfinance operations console** rather than a generic CRUD backend.

---

## 2. Design goal for CAVIC

Use this design direction to upgrade the admin UI into a workspace-driven console that is:

- calmer
- cleaner
- easier to scan
- more task-oriented
- more trustworthy for financial operations
- more consistent across dashboard, lists, requests, approvals, and settings

This must align with the project plan in `AGENTS.md`, especially:

- grouped admin navigation
- fewer cluttered sidebar links
- task-based workspaces
- modal-first short CRUD
- full-page large workflows
- preserved routes and functionality during rollout

---

## 3. Core visual principles

### 3.1 Calm financial UI
The design should feel dependable and operational, not flashy.

Use:
- soft neutrals
- restrained accent colors
- consistent spacing
- subtle borders
- light shadows only where needed

Avoid:
- saturated colors everywhere
- heavy gradients in primary work areas
- dense text blocks
- inconsistent card styles
- crowded tables and headers

### 3.2 Information hierarchy first
The image works because it makes the most important information obvious:

1. page title
2. KPI cards
3. key charts / work queues
4. detailed tables and secondary widgets

CAVIC should adopt the same order, but with microfinance-specific content:
- overdue exposure
- due today
- pending approvals
- disbursement readiness
- cash position
- request exceptions

### 3.3 Workspace over menu sprawl
The image keeps the sidebar short and lets the page body carry the detail.

For CAVIC this means:
- top-level sidebar = major workspaces only
- page-level tabs = workspace sections
- page-level buttons = actions
- modals = quick CRUD and short review flows

---

## 4. UI style translation for CAVIC

## 4.1 App shell
Adopt a framed layout similar to the reference image:

- outer page background in light gray
- inner app shell in white
- large radius container around the main application area
- sidebar visually separated by a very light divider
- content area with wide padding

### Recommended shell behavior
- desktop: sidebar fixed or sticky on the left
- content area scrolls independently when practical
- mobile/tablet: sidebar collapses into an off-canvas drawer

---

## 4.2 Color system
Approximate the visual tone from the image with a CAVIC-friendly palette.

### Primary palette
- **Primary teal:** `#3F686D`
- **Primary teal dark:** `#32555A`
- **Primary teal soft:** `#E7F1F0`

### Neutral palette
- **App background:** `#F4F4F2`
- **Surface:** `#FFFFFF`
- **Surface muted:** `#FAFAF8`
- **Border:** `#E7E9E4`
- **Text primary:** `#2E3338`
- **Text secondary:** `#6F787F`
- **Text muted:** `#9AA2A8`

### Semantic colors
- **Success:** `#17B26A`
- **Success soft:** `#EAF8F0`
- **Danger:** `#F04452`
- **Danger soft:** `#FDECEE`
- **Warning:** `#D9A441`
- **Warning soft:** `#FFF6E1`
- **Info:** `#4E8DA6`
- **Info soft:** `#EAF4F8`

### Usage rules
- teal = active state, primary emphasis, selected navigation
- green = positive movement, completed items, healthy statuses
- red = overdue, failed, rejected, risk
- amber = pending, attention needed, not yet resolved
- neutrals = default structure and text

Do not use bright colors as large background fills except for small chips, badges, and alerts.

---

## 4.3 Typography
The reference uses a modern neutral sans-serif.

### Recommendation
Use one of these:
- `Inter`
- `Manrope`
- `Plus Jakarta Sans`

Preferred: **Inter** for consistency and readability.

### Typography scale
- Page title: `32px / 700`
- Section title: `24px / 600`
- Card title: `16px - 18px / 600`
- Body text: `14px - 16px / 400-500`
- Small supporting text: `12px - 13px / 400-500`
- KPI value: `40px - 48px / 700`

### Text rules
- use darker text for important figures
- keep secondary copy muted
- avoid very small text in tables
- use sentence case, not all caps, for most labels

---

## 4.4 Spacing and shape
The reference image relies heavily on generous spacing and rounded rectangles.

### Radius system
- app shell radius: `24px - 32px`
- major card radius: `18px - 20px`
- inputs/selects/buttons: `10px - 14px`
- pills/badges: `999px`

### Spacing system
Use an 8px base scale:
- `4, 8, 12, 16, 20, 24, 32, 40`

### Shadow and border rules
- most cards should use **subtle border first**, not heavy shadow
- use shadows lightly for floating or elevated components
- prefer this pattern:
  - background: white
  - border: `1px solid #E7E9E4`
  - shadow: very soft or none

---

## 5. Layout anatomy to implement

## 5.1 Sidebar
The reference sidebar is compact, sectional, and easy to scan.

### CAVIC target sidebar
Top-level items only:
1. Dashboard
2. Action Center
3. Members
4. Loans
5. Finance
6. Reports
7. Administration
8. Messages

### Sidebar style
- white or off-white panel
- subtle right border
- logo / product title at top
- search field below logo
- menu grouped with small section labels
- active item shown with solid teal background and white text or high-contrast selected state
- inactive items simple and quiet
- small icon beside each item

### Sidebar behavior
- show only modules, not action links
- move actions like `Add Member`, `New Transaction`, `Compose Message`, `Bulk Import` into page headers
- preserve old route access while changing how navigation is presented

---

## 5.2 Top utility bar
The screenshot uses a minimal top-right utility area.

### CAVIC adaptation
Top bar / top utility row should contain:
- notification bell
- quick alerts badge
- branch or organization context if needed
- current user avatar + name
- optional global search or quick switch later

Keep the top bar simple. Do not overload it with secondary navigation.

---

## 5.3 Page header
Each major workspace should begin with a clear header.

### Header structure
- title
- one-line description or context
- breadcrumb row
- optional quick stats strip
- action buttons aligned right

### Example
**Loans**
- subtitle: “Monitor pipeline, approvals, disbursements, repayments, and overdue exposure.”
- actions: `New Loan`, `Post Repayment`, `Export`

This aligns with the planned reusable header partial:
- `resources/views/layouts/others/page-header.blade.php`

---

## 5.4 Workspace tabs
Instead of many sidebar items, use tabs inside each workspace.

### Placement rule
All major admin workspace tabs should be placed in the same top strip pattern now used on the Dashboard:
- tabs sit in the row directly below the navbar
- this replaces greeting/salutation-style content in admin workspace pages
- contextual selectors such as branch selection should sit on the right side of that same row when needed
- avoid placing primary workspace tabs lower in the page body when the page is a major admin workspace

### Visual treatment
- horizontal underline tabs, matching the Dashboard pattern
- quiet default state
- active tab uses the CAVIC brand teal
- compact spacing and a clean baseline underline
- wrap on smaller screens or collapse into a dropdown when necessary

### CAVIC workspace tabs

#### Dashboard
- Overview
- Portfolio Health
- Collections Snapshot
- Branch Performance

#### Action Center
- Member Requests
- Pending Loans
- Deposit Requests
- Withdraw Requests
- Due Today
- Exceptions

#### Members
- All Members
- Onboarding / Requests
- Documents
- Branches
- Leaders
- Import
- Custom Fields

#### Loans
- Pipeline
- Disbursements
- Repayments
- Due / Upcoming
- Collections
- Loan Products
- Approvals
- Calculator

#### Finance
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

#### Reports
- Executive KPIs
- Portfolio
- Collections
- Accounts
- Transactions
- Expenses
- Banking
- Branch Performance
- Revenue

#### Administration
- Users
- Roles & Permissions
- Settings
- Currency
- Notification Templates

#### Messages
- Inbox
- Sent

---

## 6. Core component patterns from the design

## 6.1 KPI cards
The top cards in the image are a key pattern.

### CAVIC KPI card structure
- icon
- title
- large value
- supporting comparison or status chip
- optional overflow menu

### Dashboard KPI examples
- Total Active Members
- Active Borrowers
- Portfolio Outstanding
- Overdue Amount
- PAR Exposure
- Due Today
- Disbursements This Month
- Deposits This Month
- Withdrawals This Month
- Cash Position

### Style
- white card
- rounded corners
- thin border
- strong large number
- small green/red delta line below

---

## 6.2 Analytics panels
The image includes larger visual insight panels.

### CAVIC equivalents
Replace generic revenue widgets with:
- repayment heatmap
- collections aging buckets
- loan pipeline funnel
- branch performance comparison
- daily cash movement chart
- disbursement trend chart

### Style rules
- each panel has a clear title
- controls like period filters should sit top-right
- use one visual focus per panel
- do not crowd too many charts into one card

---

## 6.3 Recent activity list
The right-side activity feed in the image is useful.

### CAVIC recent activity examples
- loan approved
- loan disbursed
- deposit posted
- withdrawal request flagged
- overdue account escalated
- member approved
- reconciliation mismatch detected

### Style
- icon container on left
- primary action label
- secondary metadata below
- amount/status aligned right where useful
- green/red/amber semantic indicators

---

## 6.4 Tables
The bottom area of the screenshot uses a soft modern table.

### Table style rules
- compact but breathable row height
- light header background or plain white with stronger bottom border
- rounded parent card
- muted separators
- status as pills
- row actions in final column or kebab menu
- checkbox column optional
- toolbar controls should feel like product controls, not raw plugin defaults
- use the CAVIC brand teal for active pagination, filters, exports, and primary row actions
- keep table action styling consistent across all admin pages

### Data table and action pattern
Use the table reference image (`datatable1.png` in the project root) as the consistent pattern for admin tables:
- a clean toolbar above the table with export/filter/search controls
- column visibility control with checked states for toggled columns
- polished pagination and record summary controls, not raw plugin defaults
- soft outlined action controls with the brand color, not unrelated framework colors
- a compact overflow-style action affordance on the far right where appropriate
- airy dropdown menus for row actions
- row action dropdowns are modal-first: actions such as View, Details, Edit, Documents, Approve, Reject, and short operational reviews should open in `#main_modal` or `#secondary_modal` instead of navigating away
- full-page row action links should be treated as fallback/direct links only, or reserved for genuinely long workflows that need the whole screen
- subtle selected-row emphasis without heavy fills
- minimal grid noise and stronger readability for the content itself

### Canonical admin DataTable standard
The dashboard recent transactions table is now the source-of-truth pattern for all admin DataTables.

Reference implementation:
- `resources/views/backend/admin/dashboard-admin.blade.php`
- `public/backend/assets/css/styles.css`

All future admin tables should follow this exact structure unless there is a strong workflow reason not to:
- toolbar split into three zones:
  - left: page length
  - center: export buttons
  - right: column visibility, table filters, and search
- do not expose raw plugin-default DataTables controls when a styled CAVIC control exists
- use a dedicated `Columns` visibility control rather than relying on misaligned plugin markup
- the `Columns` visibility menu must anchor to the Columns button, use a high shared overlay z-index, and scroll internally when it is taller than the viewport
- keep all toolbar controls vertically centered in one shared row
- export buttons should be compact and visually lighter than the filters/search controls
- filters and search should use the same height, radius, and alignment rules
- toolbar controls should feel like a product workspace, not a jQuery plugin toolbar

### Row and table behavior standard
All admin DataTables should also inherit the dashboard table behavior:
- compact row height with approximately 30% less vertical space than the legacy tables
- striped rows for scanability
- clear hover highlight across the full row
- hover state must visually carry into the actions cell/button as part of the same row
- soft bordered table card with rounded outer container
- wide tables must show a visible horizontal scrollbar inside the table card instead of clipping columns
- avoid DataTables split-header horizontal scrolling unless it is proven aligned; prefer one real table inside an overflow wrapper
- readable header contrast with minimal grid noise
- action column kept narrow and visually consistent
- status chips kept compact so data density stays high

### Implementation rule
When updating any admin table:
- use the dashboard recent transactions table as the baseline before inventing a new variation
- preserve workflow-specific filters, but keep the shared toolbar layout and table density
- if a table needs custom actions, fit them into this pattern instead of adding a second toolbar
- if DataTables plugin markup fights the layout, replace that specific control with a custom CAVIC control rather than stacking more CSS hacks on top
- if the table can exceed the viewport or card width, set an explicit minimum table width and rely on the shared `.table-responsive` / `.admin-datatable-table-wrap` horizontal scrollbar

### CAVIC tables that should use this style
- members list
- loans list
- repayments due
- deposit requests
- withdrawal requests
- transactions
- expenses
- bank transactions
- users and roles

---

## 6.5 Status badges
The image uses subtle chips for status.

### Badge rules
Use pill badges with soft backgrounds.

Examples:
- `Completed` → green soft background
- `Pending` → amber soft background
- `Overdue` → red soft background
- `Under Review` → info soft background
- `Rejected` → red soft background
- `Disbursed` → teal or green soft background

Make statuses first-class visual markers across the app.

---

## 6.6 Filters and selectors
The image uses compact filter dropdowns like “Monthly”.

### CAVIC filter bar pattern
- date range
- branch selector
- status filter
- method/product filter
- assigned officer/collector filter where relevant
- export button
- reset filters link

Use a reusable filter bar partial:
- `resources/views/backend/admin/partials/filter-bar.blade.php`

### Date range picker standard
Date range filters should use the shared CAVIC workspace control rather than raw browser inputs.

Required behavior:
- visible label should describe the field, for example `Date Range`, not repeat the selected date
- visible input should use the system date format `d/m/YYYY`, for example `26/04/2026` or `01/04/2026 - 26/04/2026`
- submitted values should remain hidden ISO fields, for example `from_date=2026-04-01` and `to_date=2026-04-26`
- single-day ranges should display as one date instead of duplicating the same date twice
- picker should initialize after page assets are loaded so workspace pages attach reliably

Visual rules:
- input uses a calendar icon, rounded 14px control, CAVIC teal focus ring, and white surface
- Apply and Reset actions should match workspace button styling and align with the filter controls
- popup calendar uses rounded 20px panel, soft border, subtle shadow, teal selected dates, pale teal in-range dates, and compact range presets
- popup must render above cards/tables and remain usable on mobile with viewport-aware width

---

## 6.7 Empty states
Empty states must look intentional, not broken.

### Empty state structure
- simple icon
- short title
- one-line explanation
- primary action button
- optional secondary link

Example:
- “No pending loan approvals”
- “All current approvals have been reviewed.”
- `View Disbursements`

---

## 7. Mapping the image to CAVIC admin workspaces

## 7.1 Dashboard
Use the reference image most directly here.

### Recommended dashboard sections
1. **KPI strip**
   - Active Members
   - Portfolio Outstanding
   - Overdue Amount
   - Due Today

2. **Operations insight row**
   - collections heatmap or due calendar
   - recent activity / alerts list

3. **Detail row**
   - transactions or disbursements table
   - portfolio mix / savings / branch breakdown chart

4. **Exception row**
   - overdue buckets
   - pending approvals
   - reconciliation mismatches

The dashboard should feel closest to the provided image.

---

## 7.2 Action Center
This should use the same card-and-panel language but emphasize queues.

### Visual priority
- red/amber alert counts at the top
- tabbed request queues
- due today cards
- exception panels
- quick approve/reject/review actions

This page should feel like the operational equivalent of the image, with less analytics and more workflow urgency.

---

## 7.3 Members
Apply the reference layout to a member lifecycle workspace.

### Top area
- title + member-focused actions
- summary cards:
  - Total Members
  - Pending Approval
  - Incomplete KYC
  - Dormant Members

### Body
- tab navigation
- lifecycle or funnel summary
- members table
- side panels for recent requests or document exceptions

---

## 7.4 Loans
This should become one of the strongest workspace upgrades.

### Top KPI cards
- Submitted
- Approved
- Ready for Disbursement
- Active Loans
- Overdue Loans
- Total Outstanding

### Main body
- pipeline panel
- due/upcoming collections panel
- approvals queue
- repayments table
- overdue aging cards

The visual style should remain calm even when surfacing risk.

---

## 7.5 Finance
This workspace should adapt the visual language for cash operations.

### Suggested top cards
- Wallet Balance
- Savings Balance
- Cash In Today
- Cash Out Today
- Pending Requests
- Reconciliation Exceptions

### Main panels
- teller/cash ops queue
- recent transactions
- bank transactions
- reconciliation summary
- liquidity breakdown

---

## 7.6 Reports
The reference image suggests a card-based analytics center.

### Reports center layout
- report category tiles or tabs
- summary KPI cards
- chart panels
- export controls
- branch and period filters

Do not make Reports look like a raw list of links.

---

## 7.7 Administration
Use the same clean shell, but quieter than Dashboard.

### Visual emphasis
- settings sections as grouped cards
- tabbed controls for roles, users, currency, templates
- modal-first for short CRUD like roles and currency

---

## 7.8 Messages
Use the design language, but keep a communication-friendly layout.

### Suggested layout
- inbox/sent tabs
- list panel + detail preview panel on large screens
- compose action in page header
- modal preview for quick reading when appropriate

---

## 8. CAVIC-specific component requirements

## 8.1 Loan lifecycle styling
The AGENTS plan requires lifecycle visibility.

Represent the following clearly:
- Draft
- Submitted
- Under Review
- Approved
- Ready for Disbursement
- Disbursed / Active
- Overdue
- Closed
- Rejected

### Recommendation
- use chips for status
- use progress or funnel cards for aggregate views
- use red emphasis for overdue buckets
- keep lifecycle panels visible at top of Loans workspace

---

## 8.2 Overdue and exception visibility
Overdue work must be more visible than in the current admin.

### Required patterns
- red soft-highlight cards for overdue counts and amount
- “Exceptions” card or panel on dashboard and Action Center
- aging buckets with strong labels:
  - 1–7 days
  - 8–30 days
  - 31–60 days
  - 61+ days

Do not bury these inside filters only.

---

## 8.3 Branch-aware summaries
The image suggests top-level operational visibility.

For CAVIC, add branch-aware controls to:
- dashboard
- finance
- reports
- members
- loans

Branch selector should live in the filter bar or header actions area.

---

## 8.4 Modal consistency
Per project rules, the reference style should also influence modal design.

### Modal design
- rounded corners
- strong title area
- clear footer actions
- clean form spacing
- consistent widths for small / medium / large review modals
- modal overlays must sit above DataTables controls: DataTables export menus use the shared overlay layer, the custom Columns menu may reach `z-index: 4000`, and admin modals must render above them at `z-index: 5000` or higher
- stacked modal flows should use a higher secondary layer than the main modal, for example `#main_modal` at `5000` and `#secondary_modal` at `5020`

### Use modals for
- all action-dropdown items by default, so the admin app feels quick and responsive without unnecessary full-page navigation
- short create/edit/detail/review flows launched from tables, cards, workspace queues, and action menus
- member row actions such as quick details, edit member, and member documents when launched from Members workspace or member list action dropdowns; preserve full-page member routes as direct-link fallbacks
- member view dialogs should use a compact `modal-lg` tabbed layout that includes profile details, account overview, transactions, loans, documents, email, and SMS without requiring navigation to a standalone page
- branches
- leaders
- currency
- categories
- bank accounts
- bank transactions
- custom fields
- member documents
- roles
- guarantors
- request detail previews

### Keep full pages for
- direct route fallbacks for users who open URLs manually or need a shareable page
- long multi-step create workflows that are not launched from an action dropdown
- loan create/edit/view when the workflow requires full-screen review, large forms, collateral/guarantor context, or extensive supporting data
- reports center
- settings center
- bulk import by default, except member bulk import may use a large/fullscreen modal when launched from member list or workspace actions while preserving the direct full-page route fallback
- transaction create/edit

### Action Dropdown Rule
Every table or workspace action dropdown should prefer dialogs over standalone pages. If an action can be completed, reviewed, approved, edited, previewed, or lightly configured in a focused overlay, open it via `ajax-modal`/`ajax-modal-2`. Use full pages only when the user explicitly navigates to the module, opens a direct link, or enters a workflow too large for a modal.

---

## 9. Implementation guidance for Laravel views

## 9.1 Blade structure
This design should be implemented incrementally through partials and shared patterns.

### Likely files to create/update
- `resources/views/layouts/menus/admin.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/others/page-header.blade.php`
- `resources/views/layouts/others/breadcrumbs.blade.php`
- `resources/views/backend/admin/partials/module-tabs.blade.php`
- `resources/views/backend/admin/partials/filter-bar.blade.php`
- `resources/views/backend/admin/partials/quick-actions.blade.php`
- `resources/views/backend/admin/partials/empty-state.blade.php`
- `public/backend/assets/css/styles.css`
- `public/backend/assets/css/responsive.css`

### Menu partial split recommended
- `resources/views/layouts/menus/admin/dashboard.blade.php`
- `resources/views/layouts/menus/admin/action-center.blade.php`
- `resources/views/layouts/menus/admin/members.blade.php`
- `resources/views/layouts/menus/admin/loans.blade.php`
- `resources/views/layouts/menus/admin/finance.blade.php`
- `resources/views/layouts/menus/admin/reports.blade.php`
- `resources/views/layouts/menus/admin/administration.blade.php`
- `resources/views/layouts/menus/admin/messages.blade.php`

---

## 9.2 CSS token recommendation
Create reusable CSS custom properties so the new style is easy to maintain.

```css
:root {
  --cavic-bg: #F4F4F2;
  --cavic-surface: #FFFFFF;
  --cavic-surface-muted: #FAFAF8;
  --cavic-border: #E7E9E4;
  --cavic-text: #2E3338;
  --cavic-text-soft: #6F787F;
  --cavic-text-muted: #9AA2A8;
  --cavic-primary: #3F686D;
  --cavic-primary-dark: #32555A;
  --cavic-primary-soft: #E7F1F0;
  --cavic-success: #17B26A;
  --cavic-success-soft: #EAF8F0;
  --cavic-danger: #F04452;
  --cavic-danger-soft: #FDECEE;
  --cavic-warning: #D9A441;
  --cavic-warning-soft: #FFF6E1;
  --cavic-info: #4E8DA6;
  --cavic-info-soft: #EAF4F8;

  --radius-app: 28px;
  --radius-card: 20px;
  --radius-control: 12px;
  --shadow-soft: 0 8px 24px rgba(31, 41, 55, 0.04);
}
```

---

## 9.3 Reusable utility classes
Add a consistent class system for:
- app shell
- sidebar sections
- workspace page header
- content cards
- KPI cards
- metric deltas
- status pills
- filter bars
- empty states
- responsive grid layouts

Avoid one-off styling for each page.

---

## 10. Responsive behavior

The reference image is desktop-first, but CAVIC must remain usable on smaller screens.

### Tablet
- collapse 4 KPI cards into 2-column rows
- convert wide analytics rows to stacked cards
- allow tab bars to wrap

### Mobile
- off-canvas sidebar
- stacked cards
- horizontal scroll only where unavoidable for tables
- action buttons collapse into dropdown or primary + overflow
- charts simplified or shortened

### Do not
- keep four wide KPI cards on tiny screens
- allow sidebar to permanently consume mobile width
- make tables unreadably compressed without an alternative pattern

---

## 11. Accessibility rules

The new UI must improve aesthetics **without losing clarity**.

### Required
- sufficient text contrast
- visible focus states on buttons, tabs, links, inputs
- color should not be the only status signal
- icon-only actions need tooltips or labels where necessary
- table rows and badges must remain readable

### Recommendation
Use icons + text for important statuses:
- overdue
- completed
- pending
- rejected
- approved

---

## 12. Content and copy guidance

The reference image uses concise labels. CAVIC should do the same.

### Good examples
- `Pending Loans`
- `Due Today`
- `Overdue Amount`
- `Ready for Disbursement`
- `Reconciliation Exceptions`

### Avoid
- overly technical labels when a simpler one exists
- duplicate actions in multiple locations
- vague labels like `Manage`, `View More`, `Other`

---

## 13. Phased rollout based on this design

## Phase 1 — Safe visual foundation
- introduce color tokens, card styles, badges, spacing rules
- refactor sidebar into grouped modules
- improve page headers and breadcrumbs
- keep existing routes intact

## Phase 2 — Dashboard and shared patterns
- apply the new shell and dashboard card system
- add reusable tabs, filter bars, quick actions, empty states
- improve recent activity and tables

## Phase 3 — Workspaces
- Action Center
- Members workspace
- Loans workspace
- Finance workspace
- Reports center
- Administration workspace

## Phase 4 — Modal standardization
- align modal visuals to the new system
- convert short CRUD flows to modal-first where planned

## Phase 5 — Polish
- branch-aware filters
- exception surfacing
- small-screen refinements
- consistency pass across admin pages

---

## 14. Definition of success for this design upgrade

The UI upgrade is successful when:

- the admin feels closer to a modern finance dashboard
- sidebar clutter is reduced
- major workspaces are easier to understand
- overdue items and approvals are easier to spot
- actions move from the sidebar into page-level context
- cards, tables, badges, and filters feel visually consistent
- modal CRUD looks intentional and modern
- legacy routes and workflows still work

---

## 15. Direct design translation checklist

Use this as the implementation reference when upgrading views.

### Must replicate from the image
- rounded white app shell
- slim left sidebar with grouped navigation
- calm teal accent system
- large KPI cards at the top
- modular analytics / content cards
- soft table styling
- clean spacing and quiet borders
- simple top-right utility area

### Must adapt for CAVIC-specific needs
- microfinance KPIs instead of revenue SaaS metrics
- Action Center queues and exceptions
- loan lifecycle visibility
- overdue aging and due-today emphasis
- branch-aware reporting
- modal-first configuration CRUD

### Must not copy literally
- third-party branding from the reference
- SaaS-only labels like revenue/net income if not relevant
- placeholder data structure that does not map to real CAVIC workflows

---

## 16. Final note

`newcavic.webp` should be treated as a **visual direction reference**, not a pixel-perfect template. The goal is to capture its strengths:

- clarity
- softness
- modularity
- executive readability
- low clutter
- modern financial-dashboard feel

Then adapt them to the real CAVIC admin architecture defined in `AGENTS.md`.
