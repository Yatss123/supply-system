# Figure 5: Use Case Diagram — PECIT Supply Office Inventory Management System

This figure depicts the principal actors and use cases of the PECIT Supply Office Inventory Management System. Core actors include Department Staff, Supply Officer, Dean, Admin/SuperAdmin, Supplier, and a System Scheduler for automated tasks. Major use cases cover supply requests, approvals, issuing/borrowing/returning items, restocking and receiving deliveries, inventory/location updates, status changes, cycle counting, reporting, RBAC, audit logging, forecasting and reorder policies, and notifications. Audit logging is included in high‑risk and state‑changing actions to ensure traceability and compliance.

Render the diagram using PlantUML (desktop plugin or online renderer). The source below is identical to `figure_5_use_case.puml`:

```plantuml
@startuml
title Figure 5: Use Case Diagram – PECIT Supply Office Inventory Management System
left to right direction
skinparam shadowing false
skinparam packageStyle rectangle
skinparam actorStyle awesome

actor "Department Staff" as Dept
actor "Supply Officer" as Officer
actor "Dean" as Dean
actor "Admin / SuperAdmin" as Admin
actor "Supplier" as Supplier
actor "System Scheduler" as Scheduler

rectangle "Inventory Management System" {
  usecase "Submit Supply Request" as UC_SupplyRequest
  usecase "Review & Approve Supply Request" as UC_ApproveRequest
  usecase "Issue Items to Department" as UC_IssueItems
  usecase "Record Manual Receipt" as UC_ManualReceipt
  usecase "Create Restock Request" as UC_RestockRequest
  usecase "Receive Supplier Delivery" as UC_ReceiveDelivery
  usecase "Update Inventory & Locations" as UC_UpdateInventory
  usecase "Inter-department Loan Request & Approval" as UC_InterDeptLoan
  usecase "Borrow & Return Items" as UC_BorrowReturn
  usecase "Cycle Counting" as UC_CycleCounting
  usecase "Status Change (Active/Hold/Archived)" as UC_StatusChange
  usecase "Generate Reports & Dashboards" as UC_Reports
  usecase "Manage Roles & Permissions" as UC_RBAC
  usecase "Audit Trail & Logs" as UC_Audit
  usecase "Forecast & Reorder Policy (EOQ/ROP/Safety Stock)" as UC_ForecastReorder
  usecase "Notifications & Reminders" as UC_Notifications
  usecase "Validate Allocation Budget & Limits" as UC_ValidateBudget
  usecase "Verify Department Allocation" as UC_VerifyAllocation
  usecase "Review Request Details" as UC_ReviewDetails
  usecase "Scan QR / Barcode" as UC_Scan
  usecase "Variance Investigation" as UC_VarianceInvestigation
  usecase "Approval Workflow" as UC_ApprovalWorkflow
}

Dept --> UC_SupplyRequest
Dept --> UC_BorrowReturn
Dept --> UC_InterDeptLoan

Officer --> UC_ApproveRequest
Officer --> UC_IssueItems
Officer --> UC_ManualReceipt
Officer --> UC_RestockRequest
Officer --> UC_ReceiveDelivery
Officer --> UC_UpdateInventory
Officer --> UC_CycleCounting
Officer --> UC_StatusChange
Officer --> UC_Reports

Dean --> UC_ApproveRequest
Dean --> UC_Reports

Admin --> UC_RBAC
Admin --> UC_Reports

Supplier --> UC_ReceiveDelivery

Scheduler --> UC_Notifications
Scheduler --> UC_ForecastReorder

UC_SupplyRequest ..> UC_Audit : <<include>>
UC_ApproveRequest ..> UC_Audit : <<include>>
UC_IssueItems ..> UC_Audit : <<include>>
UC_ManualReceipt ..> UC_Audit : <<include>>
UC_RestockRequest ..> UC_Audit : <<include>>
UC_ReceiveDelivery ..> UC_Audit : <<include>>
UC_UpdateInventory ..> UC_Audit : <<include>>
UC_InterDeptLoan ..> UC_Audit : <<include>>
UC_BorrowReturn ..> UC_Audit : <<include>>
UC_CycleCounting ..> UC_Audit : <<include>>
UC_StatusChange ..> UC_Audit : <<include>>
UC_RBAC ..> UC_Audit : <<include>>

UC_ForecastReorder ..> UC_Reports : outputs KPIs
UC_RestockRequest ..> UC_ReceiveDelivery : triggers purchase
UC_IssueItems ..> UC_UpdateInventory : updates stock levels
UC_ReceiveDelivery ..> UC_UpdateInventory : updates stock levels

UC_SupplyRequest ..> UC_ValidateBudget : <<include>>
UC_IssueItems ..> UC_VerifyAllocation : <<include>>
UC_ApproveRequest ..> UC_ReviewDetails : <<include>>
UC_BorrowReturn ..> UC_Scan : <<include>>
UC_CycleCounting ..> UC_VarianceInvestigation : <<extend>>
UC_StatusChange ..> UC_ApprovalWorkflow : <<include>>

@enduml
```