# Supply System User Manual

This manual describes how to use the Supply System for each role: Student, Adviser, Dean, Admin, and Super Admin. Screens and exact labels may vary slightly by deployment, but the workflows and permissions below apply broadly.

## General Access

- Sign in with your account to reach the Dashboard.
- Some features require a completed profile. Students must complete their profiles; Advisers, Deans, Admins and Super Admins may be exempt.
- Navigation typically includes: Dashboard, Supplies, Requests, Loans, Allocations, Departments, Users, Reports, and QR Actions (admin-only).

---

## Student

### What you can do

- View supply details for your department from Supplies → View.
- Create standard Loan Requests for items you need.
- Initiate Returns on items you have borrowed.
- Create Supply Requests to ask for items for your department.
- Track your own requests and borrowed items on the Dashboard and relevant pages.

### Typical workflows

1. View a Supply
   - Go to Supplies → Browse → click a supply → View.
   - Review item details (stock, category, supplier) and any request options.

2. Request a Loan
   - From a supply or the Loans page, click Request Loan.
   - Fill quantity and purpose; submit.
   - Wait for approvals (Dean/Admin, depending on policy). Track status under Loan Requests.

3. Return a Borrowed Item
   - Go to Borrowed Items or Loans → My Borrowed.
   - Select the item → Initiate Return.
   - Return verification is done by Admin; you’ll see status change once processed.

4. Create a Supply Request
   - Go to Requests → Supply Requests.
   - Click New Request, select items and quantities, submit.
   - Track status on the Supply Requests page.

### Restrictions

- You cannot approve, issue, or verify requests.
- Visibility is limited to your own requests and borrowed items.
- Profile completion may be required to access some features.

---

## Adviser

### What you can do

- Everything a Student can do, scoped to your own account.
- Create Supply Requests for your department, scoped to yourself.
- Create and track Loan Requests and Inter‑Department Loan Requests that you initiate.
- Generally bypasses profile completion enforcement.

### Typical workflows

1. Create a Supply Request (self‑scoped)
   - Requests → Supply Requests → New Request.
   - Provide items/quantities → submit. Track status.

2. Request/Track Loans
   - Loans → New Loan Request.
   - Provide details → submit. Track approval and issuance.

3. Initiate Return
   - Loans/Borrowed Items → select item → Initiate Return.

### Restrictions

- No approval or issuance authority.
- Visibility primarily limited to requests you initiated.

---

## Dean

### What you can do

- View department supplies and dashboards (Dean views).
- Approve standard Loan Requests for your department.
- Approve Inter‑Department Loans as requesting Dean or lending Dean.
- Manage monthly Department Allocations: update minimum stock, actual availability, refresh allocation cart, set reminder day.
- Manage Dean Access for users in your department (assign/revoke temporary dean privileges to Students/Advisers in your department).
- View department users and limited profiles per policy.

### Typical workflows

1. Approve a Loan Request (department)
   - Loans → Pending (Dean).
   - Open a request → Review details → Approve or Decline.

2. Approve Inter‑Department Loan
   - Inter‑Department Loans → Pending.
   - For your department as requester: approve as Dean.
   - For your department as lender: approve as Lending Dean.

3. Manage Monthly Allocations
   - Allocations → Your Department.
   - Update Minimum Stock: set thresholds for items.
   - Update Actual Availability: record current counts.
   - Refresh Allocation Cart: rebuild planned allocation items.
   - Set Reminder Day: choose monthly reminder schedule.
   - Save updates; allocation changes affect department planning and notifications.

4. Manage Dean Access for Users
   - Users → Department → select user.
   - Assign Dean Privilege (temporary) or Revoke.
   - Changes apply only within your department.

### Restrictions

- No full admin issuance or system‑wide user management.
- Actions are constrained to your department.

---

## Admin

### What you can do

- Full administrative operations across departments.
- Approve/Decline Supply Requests; add to department allocation cart.
- Create/Update/Fulfill Restock Requests.
- Approve/Decline/Issue/Verify Returns for Loan Requests.
- Admin approvals for Inter‑Department Loans.
- Manage Allocations: overview, configure limits/quantities, stage and issue, update statuses, add/remove items.
- Manage Users: roles, temporary privileges, bulk actions, export; process Profile Update Requests.
- Use QR Actions: quick issue, status change, supply request shortcuts.
- Access Reports and system‑wide dashboards.

### Typical workflows

1. Process a Supply Request
   - Requests → Supply Requests → Pending.
   - Open a request → Approve or Decline; optionally add items to allocation cart.

2. Handle a Loan Request
   - Loans → Pending.
   - Review → Approve/Decline.
   - After approval, Issue items when ready.
   - On return, Verify Return to close the loan.

3. Manage Restock Requests
   - Inventory → Restock Requests.
   - Create request for supplier; update status as items arrive; fulfill into stock.

4. Admin Inter‑Department Loans
   - Inter‑Department Loans → Admin Oversight.
   - Review cross‑department loan requests; perform admin approval steps.

5. Manage Allocations
   - Allocations → All Departments.
   - Configure limits and quantities, stage carts, issue allocations.
   - Update allocation statuses; add/remove items as needed.

6. Manage Users and Profiles
   - Users → Roles: assign roles; ensure dean uniqueness per department.
   - Users → Temporary Privileges: assign/revoke temporary Admin/Dean privileges (Admin unrestricted; Dean constrained to own department).
   - Profile Requests: review, approve, or reject user profile updates.

7. Use QR Actions
   - QR → Quick Issue, Status Change, Approve Loan via QR.
   - Follow on‑screen prompts; ensure correct item and user selection.

### Restrictions

- Destructive user actions (delete/force delete/restore) may be reserved for Super Admin.

---

## Super Admin

### What you can do

- Everything an Admin can do.
- Exclusive destructive user operations: delete, force‑delete, and restore users per policy.

### Best practices

- Use destructive actions sparingly and in accordance with organizational policy.
- Ensure backups and exports are current before bulk changes.

---

## Tips, Rules, and Troubleshooting

- Profile Completion Required: If you see access blocked, complete your profile. Advisers, Deans, Admins may be exempt.
- Permission Denied: Your role may not allow the action (e.g., approvals are for Dean/Admin). Request assistance from your department Dean or Admin.
- Department Visibility: Deans see department‑wide data; Students/Advisers see self‑scoped data.
- Locked Requests: Some requests are multi‑step. Wait for required approvals or issuance. Returns require Admin verification.
- Temporary Privileges: If granted by Admin/Dean, you may temporarily gain additional actions; these are scoped and time‑limited.
- Contact Support: For role changes or access issues, contact your Admin.

---

## Glossary

- Loan Request: A request to borrow an item.
- Inter‑Department Loan: A loan between two departments requiring both deans’ approvals and admin oversight.
- Allocation: Planned monthly distribution of items to a department (min/actual counts, staged cart, issuance).
- Restock Request: A procurement request to replenish stock from suppliers.
- QR Actions: Admin shortcuts to issue items or change statuses using QR codes.

