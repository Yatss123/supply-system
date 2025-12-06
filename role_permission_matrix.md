# Role-Based Permission Matrix

## Roles Overview
- **Super Admin**: Full system access, can manage all users and roles
- **Admin**: Administrative access, can manage supplies, requests, and approvals
- **Dean**: Department-level management, limited to their department
- **Adviser**: Department-level assistance, limited permissions within department
- **Student**: Basic user access, can create requests and view own data

## Permission Matrix

### User Management
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| View all users | ✅ | ✅ | 🔒 Own Dept | ❌ | ❌ |
| Create users | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit user profiles | ✅ | ✅ | 🔒 Own Dept | ❌ | 🔒 Own |
| Delete users | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assign roles | ✅ | ❌ | ❌ | ❌ | ❌ |
| Bulk user actions | ✅ | ❌ | ❌ | ❌ | ❌ |

### Profile Management
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Update own profile | ✅ | ✅ | ✅ | ✅ | 🔒 Requires Approval |
| Approve profile updates | ✅ | ✅ | ❌ | ❌ | ❌ |
| View profile requests | ✅ | ✅ | ❌ | ❌ | ❌ |

### Supply Management
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| View supplies | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create supplies | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit supplies | ✅ | ✅ | ❌ | ❌ | ❌ |
| Delete supplies | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage variants | ✅ | ✅ | ❌ | ❌ | ❌ |
| Toggle supply status | ✅ | ✅ | ❌ | ❌ | ❌ |

### Department Management
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| View departments | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create departments | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit departments | ✅ | ✅ | 🔒 Own Dept | ❌ | ❌ |
| Delete departments | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assign dean | ✅ | ✅ | ❌ | ❌ | ❌ |

### Supply Requests
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Create requests | ✅ | ✅ | ✅ | ✅ | ✅ |
| View all requests | ✅ | ✅ | 🔒 Own Dept | 🔒 Own Dept | 🔒 Own |
| Approve requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reject requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| Fulfill requests | ✅ | ✅ | ❌ | ❌ | ❌ |

### Loan Requests
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Create loan requests | ✅ | ✅ | ✅ | ✅ | ✅ |
| View all loan requests | ✅ | ✅ | 🔒 Own Dept | 🔒 Own Dept | 🔒 Own |
| Approve loan requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reject loan requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| Fulfill loan requests | ✅ | ✅ | ❌ | ❌ | ❌ |

### Inter-Department Loans
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Create inter-dept loans | ✅ | ✅ | ✅ | ✅ | ❌ |
| View inter-dept loans | ✅ | ✅ | 🔒 Own Dept | 🔒 Own Dept | ❌ |
| Approve lending | ✅ | ✅ | 🔒 Own Dept | ❌ | ❌ |
| Confirm borrowing | ✅ | ✅ | 🔒 Own Dept | ❌ | ❌ |
| Admin approve | ✅ | ✅ | ❌ | ❌ | ❌ |

### QR Actions
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Quick Issue | ✅ | ✅ | ❌ | ❌ | ❌ |
| Quick Status Change | ✅ | ✅ | ❌ | ❌ | ❌ |
| Quick Borrow Request | ✅ | ✅ | ✅ | ✅ | ✅ |
| Approve QR borrow | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reject QR borrow | ✅ | ✅ | ❌ | ❌ | ❌ |

### Issued Items
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| View issued items | ✅ | ✅ | 🔒 Own Dept | 🔒 Own Dept | 🔒 Own |
| Create issued items | ✅ | ✅ | ❌ | ❌ | ❌ |
| Return items | ✅ | ✅ | ✅ | ✅ | ✅ |
| Export issued items | ✅ | ✅ | ❌ | ❌ | ❌ |

### Restock Requests
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Create restock requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| View restock requests | ✅ | ✅ | ❌ | ❌ | ❌ |
| Approve restock | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reject restock | ✅ | ✅ | ❌ | ❌ | ❌ |
| Mark delivered | ✅ | ✅ | ❌ | ❌ | ❌ |

### Manual Receipts
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Create manual receipts | ✅ | ✅ | ❌ | ❌ | ❌ |
| View manual receipts | ✅ | ✅ | ❌ | ❌ | ❌ |
| Approve receipts | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reject receipts | ✅ | ✅ | ❌ | ❌ | ❌ |

### Categories & Suppliers
| Feature | Super Admin | Admin | Dean | Adviser | Student |
|---------|-------------|-------|------|---------|---------|
| Manage categories | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage suppliers | ✅ | ✅ | ❌ | ❌ | ❌ |

## Legend
- ✅ Full Access
- 🔒 Limited Access (with restrictions)
- ❌ No Access

## Department Scoping Rules
1. **Dean**: Can only view/manage users and requests within their assigned department
2. **Adviser**: Can only view/manage requests within their department
3. **Student**: Can only view/manage their own data and requests
4. **One Dean per Department**: System enforces only one Dean can be assigned per department

## Approval Workflows
1. **Profile Updates**: Students require admin approval for profile changes
2. **Supply Requests**: Only admins can approve/reject supply requests
3. **Loan Requests**: Only admins can approve/reject loan requests
4. **Inter-Department Loans**: Require both department dean approval and admin approval
5. **Restock Requests**: Only admins can manage restock workflows

## Security Notes
- All routes require authentication
- Role middleware should be applied to sensitive routes
- Server-side permission checks must be implemented in controllers
- Client-side hiding is not sufficient for security
- Audit logging should track all role-sensitive actions