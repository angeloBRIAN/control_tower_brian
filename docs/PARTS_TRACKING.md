# Parts Tracking

The Parts Tracking module helps manage spare parts orders throughout the repair process, ensuring jobs aren't blocked waiting for parts.

## Overview

When a job needs parts:
1. Mark job as "Need Part" in work status
2. Create parts order record
3. Track through ordering → arrival → installation
4. Job continues when parts are ready

---

## Accessing Parts Tracking

### Dashboard Widget

The **Parts Tracking** widget shows:
- **Pending** - Awaiting order placement
- **Due Soon** - Arriving within 3 days
- **Overdue** - Past expected date

### Dedicated View

**Operations → Parts Tracking**

URL: `/parts-tracking`

### Kanban Board

**Jobs → Parts Kanban**

URL: `/jobs/parts-kanban`

---

## Parts Order Lifecycle

```
Identified → Ordered → In Transit → Received → Installed
```

| Status | Description | Action Required |
|--------|-------------|-----------------|
| **Identified** | Parts needed, not yet ordered | Place order |
| **Pending Order** | Awaiting PO creation | Create PO |
| **Ordered** | PO sent to supplier | Wait for delivery |
| **In Transit** | Shipped by supplier | Track shipment |
| **Received** | Arrived at warehouse | Move to workshop |
| **Installed** | Installed on vehicle | Close parts order |

---

## Creating a Parts Order

### From Job Detail

1. Open job detail page
2. Click **Add Parts Order** button
3. Fill in:
   - Part name/description
   - Quantity
   - Supplier
   - Expected date
4. Click Save

### From Parts Module

1. Go to **Operations → Parts Tracking**
2. Click **+ New Order**
3. Select job (search by WIP/plate)
4. Enter parts details
5. Save

---

## Parts Order Fields

| Field | Description | Required |
|-------|-------------|----------|
| `job_id` | Linked job | Yes |
| `part_name` | Part description | Yes |
| `part_number` | OEM/Aftermarket code | No |
| `quantity` | Units needed | Yes |
| `supplier` | Vendor name | No |
| `order_date` | When ordered | Auto |
| `expected_date` | Estimated arrival | Yes |
| `received_date` | Actual arrival | On receipt |
| `status` | Current status | Auto |
| `notes` | Additional info | No |
| `cost` | Part cost | No |

---

## Updating Parts Status

### Quick Status Update

1. On Parts list, click status badge
2. Select new status from dropdown
3. Saved automatically

### Bulk Update

1. Check multiple parts orders
2. Click **Bulk Update** button
3. Select new status
4. Apply to all selected

### Via Kanban

1. Drag card to new column
2. Status updates automatically

---

## Dashboard Integration

### Parts Tracking Widget

Shows three quick stats:
- 🟡 **Pending** - Orders not yet placed
- 🔵 **Due Soon** - Arriving in ≤3 days
- 🔴 **Overdue** - Past expected date

Click any number to see the filtered list.

### Needs Parts Widget

Shows jobs currently blocked by parts:
- Job number and plate
- Days waiting
- Quick link to job

---

## Notifications

### Automatic Alerts

| Event | Who's Notified |
|-------|----------------|
| Parts arrived | Assigned SA, Foreman |
| Parts overdue | Sparepart team, Manager |
| All parts ready | SA (job can continue) |

### Push Notifications

If push is enabled, notifications go to:
- Browser notifications
- Mobile (if PWA installed)

---

## Reports

### Parts Status Report

**Reports → Parts Status**

Shows:
- Orders by status
- Average wait time
- Overdue analysis
- Supplier performance

### Export

Export to Excel/PDF:
- All pending orders
- Overdue orders
- Supplier summary

---

## Permissions

| Role | Capabilities |
|------|-------------|
| **Admin** | Full access |
| **Manager** | Full access |
| **Sparepart** | Create, edit, update status |
| **SA** | View, create for own jobs |
| **Foreman** | View only |
| **Finance** | View with costs |

---

## API Reference

### Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/parts-orders` | List all |
| POST | `/parts-orders` | Create |
| GET | `/parts-orders/{id}` | View |
| PUT | `/parts-orders/{id}` | Update |
| DELETE | `/parts-orders/{id}` | Delete |
| PATCH | `/parts-orders/{id}/status` | Quick status update |

---

## Best Practices

1. **Set realistic ETAs** - Helps planning
2. **Update promptly** - Mark received same day
3. **Add notes** - Record tracking numbers
4. **Check daily** - Review pending/overdue
5. **Communicate** - Notify SA when parts arrive
