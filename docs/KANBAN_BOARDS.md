# Kanban Boards

Control Tower provides three specialized Kanban boards for visual workflow management:

1. **Job Status Kanban** - Track jobs through work stages
2. **Finance Kanban** - Monitor invoice and payment status
3. **Part Tracking Kanban** - Manage parts requisitions (RQ)

---

## 1. Job Status Kanban

Visual workflow board for tracking job progress through 13 work stages.

### Access

**Jobs → Kanban View** (or click the Kanban icon on Jobs list)

URL: `/jobs/kanban`

### Columns (Work Status)

| # | Column | Description |
|---|--------|-------------|
| 1 | **Belum diproses** | Job created, waiting in queue |
| 2 | **ACC Pengerjaan** | Approved for work |
| 3 | **Check di Bengkel** | Under inspection |
| 4 | **Pengerjaan** | Work in progress |
| 5 | **Buka RQ** | Parts ordered (auto-updated) |
| 6 | **Parts Datang** | Parts received (auto-updated) |
| 7 | **Body Paint** | Body/paint work |
| 8 | **Wrapping/Acc** | Accessories/wrapping |
| 9 | **Pemberkasan** | Documentation |
| 10 | **Proses Close Job** | Ready to close |
| 11 | **Proses Invoice** | Invoice being created (auto) |
| 12 | **Menunggu Pembayaran** | Awaiting payment (auto) |
| 13 | **Sudah Dibayar** | Paid (auto) |

### Auto-Updated Statuses

Some columns are controlled by other systems and cannot be changed manually on this Kanban:

| Status | Controlled By |
|--------|--------------|
| 5. Buka RQ | Part Tracking Kanban |
| 6. Parts Datang | Part Tracking Kanban |
| 11-13 | Finance Kanban |

### Role Permissions

| Role | Can Change To |
|------|--------------|
| **Admin/Manager** | All statuses |
| **Control Tower** | 1, 2, 3, 4, 7, 8, 9, 10 |
| **Foreman/SA** | 1, 2, 3, 4, 7, 8 (own jobs only) |
| **Sparepart** | Use Part Tracking Kanban instead |
| **Finance** | Use Finance Kanban instead |

### Features

- **Drag & Drop** - Move jobs between columns
- **Remark Modal** - Add notes when changing status
- **Filters** - Filter by SA, Foreman, Date range
- **Search** - Search by WIP, plate, customer
- **Card Info** - Shows: WIP, Plate, Customer, Days open
- **Permission Check** - Card reverts if not allowed

### Using Drag & Drop

1. Click and hold a job card
2. Drag to the target column
3. Modal appears to add optional remark
4. Click "Confirm Change" to save
5. Activity logged in audit trail

> **Note:** If you try to drag to a restricted column, the card will snap back and you'll see a message explaining why.

---

## 2. Finance Kanban

Track invoice and payment status for the finance team.

### Access

**Operations → Finance Kanban** (visible to Finance/Admin/Manager)

URL: `/jobs/finance-kanban`

### Columns (Invoice Status)

| Column | Description |
|--------|-------------|
| **Proses Invoice** | Invoice being created |
| **Menunggu Pembayaran** | Invoice sent, awaiting payment |
| **Sudah Dibayar** | Fully paid |

### Auto-Updates Job Work Status

When finance moves cards:
- → Proses Invoice = Job work_status "11"
- → Menunggu Pembayaran = Job work_status "12"
- → Sudah Dibayar = Job work_status "13"

### Features

- **Revenue Display** - Shows invoice amount  on each card
- **Due Date Alert** - Red highlight for overdue invoices
- **Bulk Actions** - Select multiple for batch updates

---

## 3. Part Tracking Kanban

Manage parts requisitions (RQ) from request to receipt.

### Access

**Operations → Part Tracking → Kanban View**

URL: `/parts-tracking/kanban`

### Columns (Parts Status)

| Column | What It Shows |
|--------|--------------|
| **Pending** | JOBS that need parts (not PartOrders) |
| **Buka RQ** | RQ opened, waiting to order |
| **Ordered** | Order placed with supplier |
| **Confirmed** | Supplier confirmed |
| **Shipped** | In transit |
| **Received** | Parts arrived |

### Key Differences

- **Pending column shows JOBS** (not PartOrders)
- **Other columns show PartOrders** (individual RQs)
- **1-step movement only** - Cannot skip columns
- **Multi-RQ per job** - One job can have multiple RQs

### Role Permissions

| Role | Can Do |
|------|--------|
| **Admin** | All actions |
| **Control Tower** | Open RQ (Pending → Buka RQ) |
| **Foreman** | Open RQ for own assigned jobs |
| **Sparepart** | All status changes (Buka RQ → Received) |
| **SA** | View only |

### Workflow

1. **Pending → Buka RQ**: Enter RQ number, creates PartOrder
2. **Buka RQ → Ordered**: Enter order #, order date, expected date
3. **Ordered → Confirmed → Shipped**: Add optional remarks
4. **→ Received**: When ALL RQs received, job goes to "6. Parts Datang"

### Default Filters

| Role | Default View |
|------|-------------|
| SA | Own assigned jobs |
| Foreman | Own assigned jobs |
| Sparepart | All need_part jobs |
| Others | All need_part jobs |

---

## Common Controls

### Filtering (All Boards)

| Filter | Description |
|--------|-------------|
| **Search** | WIP, plate, customer name |
| **Date Range** | Filter by job date |
| **Service Advisor** | Filter by assigned SA |
| **Foreman** | Filter by assigned foreman |
| **Department** | Workshop or Body Paint |

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `G` → `K` | Go to Kanban |
| `/` or `S` | Focus search |
| `R` | Refresh board |

---

## Drag & Drop Rules

### Who Can Drag What

| Role | Job Kanban | Finance Kanban | Part Tracking |
|------|-----------|----------------|---------------|
| Admin | ✅ All | ✅ All | ✅ All |
| Manager | ✅ All | ✅ All | ✅ All |
| Control Tower | ✅ (not 5,6,11-13) | ❌ | ✅ Open RQ only |
| SA | ✅ Own jobs (1-4,7,8) | ❌ | ❌ View only |
| Foreman | ✅ Own jobs (1-4,7,8) | ❌ | ✅ Open RQ (own jobs) |
| Finance | ❌ | ✅ | ❌ |
| Sparepart | ❌ | ❌ | ✅ All (except Open RQ) |

### Automatic Actions

When moving cards, the system may:
- Update `updated_at` timestamp
- Log change in audit trail
- Send notification to assigned SA/Foreman
- Update dashboard stats
- Update job work_status (for Part/Finance Kanban)

---

## Performance Tips

1. **Use filters** - Reduce visible cards for faster rendering
2. **Limit date range** - Don't load years of data
3. **Check permissions** - Know which columns you can access

---

## Mobile Usage

Kanban boards are responsive:
- Swipe horizontally to view columns
- Tap card for quick view
- Long-press to drag
- Pull down to refresh
