# 3.3.5 Use Case Diagram — PECIT Supply Office Inventory Management System

This use case diagram depicts the interactions between the primary users and the PECIT Supply Office Inventory Management System. The main actors include:

- Supply Office Staff: Responsible for managing inventory, processing departmental requests, updating stock records, and generating reports.
- Department Requestors: Submit supply requests and track request statuses.
- Suppliers: Provide supplies and update delivery information.

Render the diagram using PlantUML. Source file: `docs/thesis/3_3_5_use_case_diagram.puml`.

```plantuml
@startuml
title 3.3.5 Use Case Diagram — PECIT Supply Office Inventory Management System
left to right direction
skinparam shadowing false
skinparam packageStyle rectangle
skinparam actorStyle awesome

actor "Supply Office Staff" as Staff
actor "Department Requestors" as Requestor
actor "Suppliers" as Supplier

rectangle "Inventory Management System" {
  usecase "Submit Supply Request" as UC_Submit
  usecase "Track Request Status" as UC_Track
  usecase "Review & Approve Requests" as UC_Approve
  usecase "Issue Items to Department" as UC_Issue
  usecase "Update Stock Records" as UC_UpdateStock
  usecase "Create Restock Request" as UC_Restock
  usecase "Receive Supplier Delivery" as UC_Receive
  usecase "Update Delivery Information" as UC_UpdateDelivery
  usecase "Generate Reports" as UC_Reports
}

Requestor --> UC_Submit
Requestor --> UC_Track

Staff --> UC_Approve
Staff --> UC_Issue
Staff --> UC_UpdateStock
Staff --> UC_Restock
Staff --> UC_Reports

Supplier --> UC_Receive
Supplier --> UC_UpdateDelivery

UC_Receive ..> UC_UpdateStock : <<include>>
UC_Issue ..> UC_UpdateStock : <<include>>
UC_Approve ..> UC_Issue : <<include>>
UC_Restock ..> UC_Receive : <<include>>

@enduml
```