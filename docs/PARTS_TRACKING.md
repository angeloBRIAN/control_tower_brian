# Parts Tracking

The Parts Tracking module manages spare parts requisitions (RQ) for workshop jobs, tracking each order from requisition to receipt.

## Overview

When a job needs parts:
1. Mark job as "Need Part" (toggles the `need_part` flag)
2. Job appears in **Part Tracking Kanban → Pending** column
3. Drag to "Buka RQ" and enter RQ number
4. Sparepart team moves through: Ordered → Confirmed → Shipped → Received
5. When ALL RQs for a job are received, work_status auto-updates to "6. Parts Datang"

---

## Accessing Parts Tracking

### Kanban Board (Recommended)

**Operations → Part Tracking → Kanban View**

URL: `/parts-tracking/kanban`

This is the primary interface for managing part orders.

### List View

**Operations → Part Tracking → List View**

URL: `/part-orders`

Table view of all part orders with filtering.

### Dashboard Widget

The **Parts Tracking** widget shows:
- **Pending** - Jobs awaiting RQ
- **Due Soon** - Arriving within 7 days
- **Overdue** - Past expected date

---

## Part Tracking Workflow

```
┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐
│  PENDING  │ → │  BUKA RQ  │ → │  ORDERED  │ → │ CONFIRMED │ → │  SHIPPED  │ → │ RECEIVED  │
│  (Jobs)   │   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│
└───────────┘   └───────────┘   └───────────┘   └───────────┘   └───────────┘   └───────────┘
```

| Status | Description | What Happens |
|--------|-------------|--------------|
| **Pending** | Job needs parts, displayed as Job card | Mark job as "Need Part" |
| **Buka RQ** | RQ opened, waiting to order | Creates PartOrder, prompts for RQ# |
| **Ordered** | Order placed with supplier | Prompts for order #, dates |
| **Confirmed** | Supplier confirmed order | Optional remark |
| **Shipped** | Parts in transit | Optional remark |
| **Received** | Parts arrived at workshop | Auto-update job work_status when all received |

---

## Key Features

### Multiple RQs per Job

A single job can have multiple PartOrder entries (multiple RQs). Each RQ is tracked independently. The job's work_status only updates to "6. Parts Datang" when **ALL** associated RQs reach a final status (Received, Installed, or Cancelled).

### 1-Step Movement Only

Cards can only be moved one column at a time. You cannot skip steps (e.g., cannot drag directly from Buka RQ to Received).

### Smart Modals

Different prompts appear based on the transition:
- **Pending → Buka RQ**: RQ Number input
- **Buka RQ → Ordered**: Order number, order date, expected date
- **Other transitions**: Optional remark

---

## Creating a Part Request

### From Job Detail Page

1. Open job detail page
2. Check the **Needs Parts** checkbox
3. Job will appear in Part Tracking Kanban → Pending column

### From Job List

1. Go to Jobs list
2. Click the "Need Part" toggle button for a job
3. Confirm the action
4. Job appears in Kanban

> **Note:** RQ numbers are now entered in the Kanban by dragging the job to "Buka RQ" column, not during the "Need Part" toggle.

---

## Working with the Kanban

### Opening an RQ (Pending → Buka RQ)

1. Find job in Pending column
2. Drag to **Buka RQ** column
3. Enter RQ number in popup modal
4. Click Save
5. PartOrder created, job work_status = "5. Buka RQ"

### Ordering Parts (Buka RQ → Ordered)

1. Find PartOrder card in Buka RQ column
2. Drag to **Ordered** column
3. Enter required fields:
   - Order Number
   - Order Date
   - Expected Date
   - Notes (optional)
4. Click Save

### Processing Through Columns

For Ordered → Confirmed → Shipped → Received:
1. Drag card to next column
2. Add optional remark
3. Click Save

### Receiving Parts

When a PartOrder is moved to **Received**:
- If ALL PartOrders for the job are now received → Job work_status becomes "6. Parts Datang"
- Activity logged for audit trail

---

## Default Filters by Role

When you first visit the Kanban, it filters based on your role:

| Role | Default View |
|------|-------------|
| **SA** | Own assigned jobs only |
| **Foreman** | Own assigned jobs only |
| **Sparepart** | All jobs with need_part |
| **Admin/Control Tower** | All jobs with need_part |

Click "Clear" to see all jobs.

---

## Permissions

| Role | Capabilities |
|------|-------------|
| **Admin** | All actions |
| **Control Tower** | Open RQ (Pending → Buka RQ) |
| **Foreman** | Open RQ for own assigned jobs only |
| **Sparepart** | All status changes (Buka RQ → Received) |
| **SA** | View only (filtered to own jobs) |
| **Finance** | No access to Part Tracking |

### Who Can Do What

| Action | Roles Allowed |
|--------|--------------|
| View Kanban | All roles |
| Open RQ (Pending → Buka RQ) | Admin, Control Tower, Foreman (own jobs) |
| Update Status (Buka RQ onwards) | Admin, Sparepart |
| Edit PartOrder details | Admin, Sparepart |
| Delete PartOrder | Admin |

---

## Part Order Fields

| Field | Description | When Set |
|-------|-------------|----------|
| `job_id` | Linked job | On creation |
| `rq` | RQ number | Pending → Buka RQ |
| `no_order_part` | Order number | Buka RQ → Ordered |
| `order_date` | When ordered | Buka RQ → Ordered |
| `expected_date` | Estimated arrival | Buka RQ → Ordered |
| `received_date` | Actual arrival | On Received |
| `status` | Current status | Auto-updated |
| `notes` | Additional info | Optional |

---

## Dashboard Integration

### Parts Tracking Widget

Shows quick stats:
- 🟡 **Pending** - Jobs awaiting RQ
- 🔵 **Due Soon** - Arriving in ≤7 days
- 🔴 **Overdue** - Past expected date

Click any number to see filtered list.

### Needs Parts Widget

Shows jobs currently blocked by parts:
- Job number and plate
- Days waiting
- Quick link to job

---

## API Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/part-orders` | List all part orders |
| GET | `/parts-tracking/kanban` | Kanban view |
| POST | `/part-orders/create-from-job` | Create RQ from job |
| POST | `/part-orders/{id}/status` | Update status |
| GET | `/part-orders/{id}/edit` | Edit part order |
| DELETE | `/part-orders/{id}` | Delete part order |

---

## Best Practices

1. **Enter accurate RQ numbers** - Helps track requisitions
2. **Set realistic expected dates** - Aids in planning
3. **Update status promptly** - Mark received same day
4. **Add notes** - Record tracking numbers, supplier info
5. **Check daily** - Review pending/overdue orders
6. **Multiple RQs** - Use separate RQ for each supplier/order

---

## Troubleshooting

### Card won't move to column

- **Check permissions**: Only certain roles can move to certain columns
- **1-step only**: You can only move to adjacent columns
- **Foreman restriction**: Foremen can only open RQ for their assigned jobs

### Job not appearing in Pending

- Ensure "Need Part" is checked on the job
- Job must NOT be invoiced
- Check filter settings (may be filtered to specific SA/Foreman)

### Work status not updating to "6. Parts Datang"

- Check if ALL RQs for the job are received
- If any RQ is still in progress, job stays at current status
