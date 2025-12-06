# Loan Requests Endpoint Payload (Updated)

This document describes the updated contract for creating loan requests via `POST /loan-requests`.

## Summary

- The endpoint now accepts a single `request` JSON payload describing one or more items.
- Legacy fields (`supply_id`, `supply_ids[]`, `items[]`, `quantity_requested`) are removed from the endpoint contract.
- Each submission creates individual `LoanRequest` rows per item (no batch header).

## Route

- `POST /loan-requests`
- `POST /loan-requests/{supply}` (supply-scoped; all items must reference the given supply)

## Required Fields

- `department_id` (integer, exists in `departments.id`)
- `needed_from_date` (date, `>= today`)
- `expected_return_date` (date, `> needed_from_date`)
- `request` (string, JSON array)

## Optional Fields

- `purpose` (string, max 1000)

## Request JSON Schema

`request` must be a JSON array of items. Each item object contains:

- `supply_id` (integer, exists in `supplies.id`, supply must be borrowable)
- `quantity` (integer, min 1, must not exceed available stock)

Example:

```
department_id=5
needed_from_date=2025-10-20
expected_return_date=2025-11-10
purpose=Thesis experiments
request=[{"supply_id":12,"quantity":2},{"supply_id":19,"quantity":1}]
```

## Behavior

- On submission, the server creates one `LoanRequest` per item with a status of `pending`.
- The response redirects to `GET /loan-requests` with a success message summarizing created items.

## Notes

- Validation enforces JSON correctness, borrowability, and stock availability per item.
- UI is updated to submit only the `request` JSON; the form no longer posts `items[]`.
- Listing pages display request summaries (`Supply × Quantity`) and link to individual requests.

---

## Inter-Department Loan Requests (Consolidated under `/loan-requests`)

To streamline integrations, inter-department loan functionality is available under the consolidated path `POST /loan-requests/inter-department` while the legacy `POST /inter-department-loans` remains available during the transition period.

### Route

- `POST /loan-requests/inter-department` (consolidated)
- `POST /inter-department-loans` (legacy; maintained for backward compatibility)

### Required Fields

- `items_payload` (string, JSON array of objects with `issued_item_id` and `quantity`)
- `planned_start_date` (date, `>= today`)
- `expected_return_date` (date, `>= planned_start_date`)
- `purpose` (string, max 1000)

### Optional Fields

- `notes` (string, max 1000)
- Admin-only: `admin_lending_department_id` (integer) and `admin_receiving_department_id` (integer, must be different)

### Items JSON Schema

`items_payload` must be a JSON array. Each item object contains:

- `issued_item_id` (integer, exists in `issued_items.id`)
- `quantity` (integer, min 1, must not exceed available quantity)

Example:

```
planned_start_date=2025-10-20
expected_return_date=2025-11-10
purpose=3D printer filament borrowing
items_payload=[{"issued_item_id":101,"quantity":2},{"issued_item_id":118,"quantity":1}]
```

### Behavior

- Creates one `InterDepartmentLoanRequest` per item.
- Dean-originated requests auto-approve and proceed to lending department review.
- Admin submissions require explicit lending/receiving department selection and enforce cross-department rules.

### Transition Notes

- All legacy inter-department routes remain available but are mirrored under `/loan-requests/inter-department/*` for unified API surface.
- Clients are encouraged to migrate to the consolidated path; no data model changes are required.