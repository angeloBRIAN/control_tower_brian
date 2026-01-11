# Kanban Boards

Control Tower provides three specialized Kanban boards for visual workflow management:

1. **Job Status Kanban** - Track jobs through work stages
2. **Finance Kanban** - Monitor invoice and payment status
3. **Parts Tracking Kanban** - Manage parts orders

---

## 1. Job Status Kanban

Visual workflow board for tracking job progress through work stages.

### Access

**Jobs → Kanban View** (or click the Kanban icon on Jobs list)

URL: `/jobs/kanban`

### Columns (Work Status)

| Column | Description | Color |
|--------|-------------|-------|
| **Booking** | Customer has booked, vehicle not yet arrived | Blue |
| **Check-in** | Vehicle arrived, inspection pending | Cyan |
| **Waiting Approval** | Estimate sent, awaiting customer approval | Yellow |
| **Need Part** | Waiting for parts to arrive | Orange |
| **In Progress** | Active work being done | Purple |
| **Quality Check** | Work complete, QC pending | Teal |
| **Ready for Delivery** | Ready to hand back to customer | Green |
| **Delivered** | Vehicle returned to customer | Slate |

### Features

- **Drag & Drop** - Move jobs between columns
- **Quick View** - Click card to see job summary modal
- **Filters** - Filter by SA, Foreman, Date range
- **Search** - Search by WIP, plate, customer
- **Card Info** - Shows: WIP, Plate, Customer, Days open, SA
- **Color Coding** - Cards colored by age (green → yellow → red)

### Using Drag & Drop

1. Click and hold a job card
2. Drag to the target column
3. Release to update status
4. Change is saved automatically
5. Activity logged in audit trail

### Card Colors (Age Indicators)

| Color | Meaning |
|-------|---------|
| **Green border** | Fresh job (< 3 days) |
| **Yellow border** | Aging job (3-7 days) |
| **Orange border** | Getting old (7-14 days) |
| **Red border** | Overdue (> 14 days) |

---

## 2. Finance Kanban

Track invoice and payment status for the finance team.

### Access

**Jobs → Finance Kanban** (visible to Finance/Admin/Manager)

URL: `/jobs/finance-kanban`

### Columns (Invoice Status)

| Column | Description |
|--------|-------------|
| **Uninvoiced** | Job complete, no invoice created |
| **Invoice Created** | Invoice generated, not sent |
| **Invoice Sent** | Invoice sent to customer |
| **Partial Payment** | Some payment received |
| **Paid** | Fully paid |
| **Overdue** | Payment past due date |

### Features

- **Revenue Display** - Shows invoice amount on each card
- **Due Date Alert** - Red highlight for overdue invoices
- **Bulk Actions** - Select multiple for batch updates
- **Export** - Download AR aging report

### Card Information

Each card displays:
- Job number / WIP
- Customer name
- Invoice amount (Rp)
- Invoice date
- Due date
- Days overdue (if applicable)

---

## 3. Parts Tracking Kanban

Monitor parts orders through procurement stages.

### Access

**Jobs → Parts Tracking** (or via dashboard Parts widget)

URL: `/jobs/parts-kanban`

### Columns (Parts Status)

| Column | Description |
|--------|-------------|
| **Pending Order** | Parts identified, not yet ordered |
| **Ordered** | Parts ordered from supplier |
| **In Transit** | Parts shipped, awaiting delivery |
| **Received** | Parts arrived at workshop |
| **Installed** | Parts installed on vehicle |

### Features

- **ETA Display** - Expected arrival date
- **Supplier Tags** - Quick view of vendor
- **Urgency Colors** - Highlight urgent orders
- **Due Soon Alert** - Yellow for arriving soon
- **Overdue Alert** - Red for late orders

### Card Information

Each card displays:
- Job number / WIP
- Plate number
- Parts description (or count)
- Supplier name
- Order date
- Expected date
- Days waiting

---

## Common Controls

### Filtering (All Boards)

| Filter | Description |
|--------|-------------|
| **Search** | WIP, plate, customer name |
| **Date Range** | Filter by job date |
| **Service Advisor** | Filter by assigned SA |
| **Department** | Workshop or Body Paint |

### View Options

| Option | Description |
|--------|-------------|
| **Compact View** | Smaller cards, more visible |
| **Detailed View** | Full card info |
| **Auto Refresh** | Updates every 60 seconds |
| **Show Counts** | Column totals in header |

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `G` → `K` | Go to Kanban |
| `/` or `S` | Focus search |
| `R` | Refresh board |
| `1-9` | Focus column by number |

---

## Drag & Drop Rules

### Who Can Drag

| Role | Can Drag |
|------|----------|
| Admin | All cards |
| Manager | All cards |
| Control Tower | All cards |
| SA | Own assigned jobs |
| Foreman | Own assigned jobs |
| Finance | Finance kanban only |
| Sparepart | Parts kanban only |

### Automatic Actions

When moving cards, the system may:
- Update `updated_at` timestamp
- Log change in audit trail
- Send notification to assigned SA/Foreman
- Update dashboard stats

---

## Performance Tips

1. **Use filters** - Reduce visible cards for faster rendering
2. **Limit date range** - Don't load years of data
3. **Compact view** - Better for many cards
4. **Close unused columns** - Collapse empty columns

---

## Mobile Usage

Kanban boards are responsive:
- Swipe horizontally to view columns
- Tap card for quick view
- Long-press to drag
- Pull down to refresh
