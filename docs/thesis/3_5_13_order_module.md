# 3.5.13 Order Module (To-Order Workflow)

## Purpose
- Centralizes procurement actions for low-stock consumables by building supplier-ready orders.
- Bridges inventory monitoring (`minimum_stock_level` vs. `availableQuantity`) with purchasing operations.
- Produces consolidated `RestockRequest` records and dispatches supplier notifications to initiate fulfillment.

## Core Data Model
- `RestockRequest` (`app/Models/RestockRequest.php`)
  - Fillable: `supply_id`, `quantity`, `status`, `supplier_id`, `requested_department_id`, `items_json`.
  - Relationships:
    - `supply()` → `Supply`
    - `supplier()` → `Supplier`
    - `requestedDepartment()` → `Department` (alias `requested_department_id`)
  - Notes:
    - `items_json` stores a consolidated list when an order spans multiple supplies for the same supplier. Shape: `{ items: [{ supply_id, supply_name, quantity, unit }...], total_quantity }`.
    - `status` values observed: `pending`, `ordered`, `delivered`.

## Routes and Views
- Routes (`routes/web.php`)
  - `GET /to-order` → `RestockRequestController@toOrderIndex` (`name: to-order.index`)
  - `GET /to-order/add` → `RestockRequestController@toOrderAdd` (`name: to-order.add`)
  - `GET /to-order/order-list` → `RestockRequestController@toOrderOrderList` (`name: to-order.order-list`)
  - `GET /to-order/create` → `RestockRequestController@toOrderCreate` (`name: to-order.create`)
  - `POST /to-order/submit` → `RestockRequestController@toOrderSubmit` (`name: to-order.submit`)
- Views (`resources/views/restock_requests/`)
  - `to_order_index.blade.php` — low-stock discovery list
  - `to_order_order_list.blade.php` — grouped selections by supplier
  - `to_order.blade.php` — single-supply staging view
  - `index.blade.php`, `show.blade.php`, `edit.blade.php`, `order.blade.php` — general restock request pages

## Operational Workflows
- Discovery (`toOrderIndex`)
  - Computes candidate supplies: `available = supply.availableQuantity()`; `minLevel = (supply.minimum_stock_level || 0)`.
  - Marks `toOrder` when `available <= minLevel` (with a suggested quantity `minLevel - available`).
  - Supports search and filters (`ordered`, `not_ordered`, `all`), excluding session-selected IDs.
  - Tracks already ordered supplies via `RestockRequest.status = 'ordered'`, mapping supply IDs and the requesting department.
- Selection (`toOrderAdd` / session management)
  - Adds chosen supply IDs to `session('to_order_list')` ensuring uniqueness.
- Grouping (`toOrderOrderList`)
  - Loads selected supplies with `suppliers` and `categories`.
  - Groups items per supplier; collects `unsupplied` items where a supply has no supplier association.
  - Supports scope filtering (`all` vs `selected`) and narrowing to a specific supplier.
- Submission (`toOrderSubmit`)
  - Validates session contents and chosen supplier.
  - Builds a single consolidated `RestockRequest` per supplier group:
    - `status = 'ordered'`, `supplier_id` set, `items_json` populated, `quantity` set to total.
    - Optionally stamps `requested_department_id` if provided.
  - Removes ordered supply IDs from the session.
  - Notifies the supplier (or user as fallback) via `SupplierOrderNotification` with item lines.
- Delivery & Stock Update (`markAsDelivered`)
  - Authorizes, sets `status = 'delivered'`, increments `supply.quantity` by delivered `quantity`.
  - Sends `RestockRequestNotification($restockRequest, 'delivered')` to both the acting user and the supplier.

## Authorization and Access Control
- Controller constructor enforces authentication for all actions.
- Additional admin-only restriction middleware for `order` and `markAsDelivered` methods.
- Per-action policy checks:
  - `viewAny`, `create`, `view`, `update` on `RestockRequest` are called before rendering or mutation.

## Notifications
- `SupplierOrderNotification` (`app/Notifications/SupplierOrderNotification.php`)
  - Channel: mail; renders supplier order details including items, quantities, requester, and delivery hints.
  - Used in `toOrderSubmit` to notify the supplier by email, or fallback to emailing the current user when the supplier’s preferred contact method is not email.
- `RestockRequestNotification` (`app/Notifications/RestockRequestNotification.php`)
  - Used in `markAsDelivered` to inform supplier and the acting user that delivery was completed.

## Validation and Data Integrity
- `toOrderSubmit` validates supplier existence and ensures the session list is non-empty.
- Consolidated request creation wrapped in a database transaction for consistency.
- Suggested quantities default to at least `1` to avoid zero-quantity orders.

## Edge Cases and Handling
- `unsupplied` supplies are separated and cannot be submitted under a supplier; these require fixing associations.
- If a supplier’s `preferred_contact_method` is not email, the system prepares an email to the acting user with contact instructions (phone, Messenger, etc.).
- Already ordered supplies are tracked and surfaced to avoid duplicate ordering.

## UI Considerations
- Index page offers search and filters with counts and ordered markers.
- Order list page shows supplier groups, item availability, minimum levels, and suggested quantities.
- Single-supply staging provides clarity for manual overrides when needed.

## Reporting and Oversight
- `RestockRequest.show` page loads linked `Supply`, `Supplier`, and `Department` for detailed inspection.
- The `items_json` structure enables downstream export or aggregation for purchase order records.

## Non-Functional Priorities
- Performance: query limits applied for newest-by-status lists; session-based selection keeps interactions responsive.
- Resilience: transactional order creation and guarded notifications reduce failure impacts.
- Extensibility: supplier contact preferences and consolidated `items_json` make adding channels and formats straightforward.