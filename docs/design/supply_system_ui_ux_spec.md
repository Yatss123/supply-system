# Supply System UI/UX Specification

## Design Aesthetic
- Card-based layout with rounded corners, soft shadows, and ample whitespace.
- Neutral surfaces with vibrant accents for actions and statuses (success, warning, error, info).
- Light and dark themes with accessible contrast; typography uses clear hierarchy and generous line-height.
- Consistent iconography for supplies, requests, issuance, borrowing, returns, restock, QR actions, and approvals.

## Information Architecture
- Primary modules prioritized by operational frequency:
  - Dashboard (KPIs, low-stock, to-order, pending approvals).
  - Issued Items and Returns (quick issue, receive returns, scanning support).
  - Borrowing and Loan Requests (create, approve, return, verify).
  - Supply Requests & Batches (department carts, request review, approvals, audit trail).
  - Restock Requests (approve/reject/fulfill, delivered, to-order lists).
  - QR Quick Actions (issue/borrow/return/status change via signed links).
- Secondary modules:
  - Supply Catalog & Variants (status toggle, SKU, availability metrics).
  - Department Allocations (min/max, monthly caps, issue-to-max, cart refresh).
  - Suppliers, Categories, Departments (master data management).
  - Locations & Units (bin/room, UOM consistency).
  - Reports (issued/ordered activity, missing/damaged).
  - Users & Roles (role assignment, temporary privileges).
  - Manual Receipts & Status Change Requests.
- Concise labels, icons, and meta information on cards; contextual actions placed near relevant content.

## Responsive Layout
- 12-column grid:
  - Desktop: 2–3 card columns, collapsible left sidebar, sticky top toolbar.
  - Tablet: 2-column main content, collapsible sidebar, stack secondary panels.
  - Mobile: single-column stack with bottom navigation for Home, Scan, Requests, Profile.
- Critical quick actions remain reachable (issue, borrow, return, approve, scan).

## User-Centric Features
- Personalized header: greeting, role badge, quick status (pending approvals, low stock), theme toggle.
- Inline notifications with quick actions (approve, reject, fulfill, mark returned).
- Toolbar exposes frequently used functions based on role permissions.
- Activity timeline with filters (module, user, department, status) and deep links to entities.
- Core modules aligned to system controllers and routes:
  - Dashboard: aggregated KPIs, shortcuts.
  - Supplies & Variants: search, filter, bulk status toggle.
  - Issued Items: issue flows, returns.
  - Borrowed Items: borrow, verify return, overdue flags.
  - Requests & Batches: create, review, approve; audit trail visibility.
  - Loan Requests & Inter-Department Loans: multi-step approvals, return tracking.
  - Restock Requests: fulfillment workflow, delivered.
  - Department Allocations & Cart: issue-to-max, refresh, monthly caps.
  - QR Actions: scan to trigger signed routes for issue/borrow/return/status change.
  - Reports: missing/damaged, activity.
  - Admin Panels: moderation, role management, system settings.

## Interaction & Micro-UX
- Hover/tap feedback, progressive disclosure, non-blocking toasts.
- Skeleton loaders for list/table fetches; optimistic UI where safe (e.g., cart updates).
- Empty states offer guidance and next-step CTAs (e.g., “No restock requests — view To Order”).
- Scanner-friendly inputs: auto-focus SKU fields; enter-to-submit; debounce; barcode scanners treated as keyboards.
- QR flows: show action summary, confirm when privileged, display signed URL validity and user/role context.
- Conflict resolution prompts for concurrent updates (stock changed while issuing).

## Accessibility
- Meets WCAG AA contrast; respects reduced motion; uses rem-based typography.
- Keyboard navigation with visible focus rings; skip-to-content and landmark roles.
- ARIA roles for data tables, dialogs, toasts; live regions for async updates.
- Clear error messaging with recovery options; timeouts do not lock critical actions.

## Visual System
- Neutral backgrounds; accent colors map to domain statuses:
  - Available, Low Stock, To Order, Ordered, Issued, Borrowed, Returned, Damaged, Missing, Pending Approval.
- 8px spacing grid; consistent card padding and gutter sizes.
- Status badges, progress rings (allocation vs issued), and alert banners.

## States & Edge Cases
- First-time admin: setup checklist (suppliers, categories, locations, units, roles).
- First-time department user: welcome card with allocation overview and guided request flow.
- In-progress flows show resume prompts; drafts visible in timeline.
- Offline/error states include friendly retry options; preserve unsaved input.
- Insufficient stock: propose partial issue, backorder, or restock request; show affected departments.
- SKU not found: suggest nearby matches/variants; allow QR scan.
- QR expired/invalid: safe failure with re-authenticate and regenerate options.

## Sample Layouts
- Desktop: primary KPIs and actionable lists first (Low Stock, To Order, Pending Approvals), then Next Steps, Timeline, Profile, Notifications.
- Mobile: modules stack vertically; quick actions at top; bottom navigation for core areas; scan button prominent.

## Component Library
- Reusable components: cards, badges, progress rings, timeline items, notifications, modals, forms, tables, filters, pagination, search.
- Domain-specific components: scanner input, QR viewer, allocation bars, audit log viewer, approval stepper, batch header.
- Empty/state components for no data, errors, offline; skeleton loaders.

## Prototyping Approach
- Start with low-fidelity wireframes of high-frequency flows (issue, borrow, return, request, restock, approvals, QR actions).
- Progress to high-fidelity mockups reflecting light/dark themes and role-based variations.
- Build click-through prototypes for onboarding, issuing/returning, request approval, inter-department loans, and restock fulfillment.
- Validate with clerks, department heads, and admins for clarity, discoverability, and responsiveness.

## Implementation Notes (System Mapping)
- Laravel + Blade + Tailwind: leverage existing stack and utility classes.
- Role-based UI: hide/show actions per permissions matrix and middleware.
- QR signed routes: ensure confirmation for sensitive actions; show user/role context.
- Representative controllers for module mapping:
  - `app/Http/Controllers/SupplyController.php`
  - `app/Http/Controllers/SupplyVariantController.php`
  - `app/Http/Controllers/IssuedItemController.php`
  - `app/Http/Controllers/BorrowedItemController.php`
  - `app/Http/Controllers/SupplyRequestController.php`
  - `app/Http/Controllers/SupplyRequestBatchController.php`
  - `app/Http/Controllers/LoanRequestController.php`
  - `app/Http/Controllers/InterDepartmentLoanController.php`
  - `app/Http/Controllers/RestockRequestController.php`
  - `app/Http/Controllers/DepartmentAllocationController.php`
  - `app/Http/Controllers/QrCodeController.php`
  - `app/Http/Controllers/QRActionController.php`
  - `app/Http/Controllers/ReportsController.php`
  - `app/Http/Controllers/SupplierController.php`, `CategoryController.php`, `DepartmentController.php`
  - `app/Http/Controllers/LocationController.php`, `UnitsController.php`
  - `app/Http/Controllers/ManualReceiptController.php`, `StatusChangeRequestController.php`

