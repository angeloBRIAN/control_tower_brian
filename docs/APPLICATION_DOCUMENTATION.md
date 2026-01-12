# Control Tower Application Documentation

**Version:** 1.1  
**Last Updated:** January 2026

---

## Overview

Control Tower is a workshop management system for tracking vehicle service jobs, from entry through invoicing. It integrates with DMS (Dealer Management System) via data imports and provides comprehensive tracking, reporting, and data quality tools.

---

## Core Modules

### 1. Job Management

**Purpose:** Track workshop jobs from creation to invoice.

| Feature | Description |
|---------|-------------|
| Job List | View all uninvoiced/invoiced jobs with filters |
| Job Detail | Complete job info with timeline, remarks, invoice history |
| Status Tracking | Uninvoiced → Invoiced workflow |
| Work Status Kanban | 13-step workflow tracking with drag-and-drop |
| Remarks System | Add timestamped remarks with role tracking |
| Need Parts Flag | Mark jobs requiring spare parts |

**Key Fields:**
- WIP (Job Number), Plate Number, Customer Name
- Service Advisor, Foreman, Technician
- Job Date, Promise Date, Deadline
- Total Sales, Estimated Amount
- Department (W=Workshop, B=Body Paint)
- Type Sale (INT=Internal, WAR=Warranty, CASH=Cash)

**Work Status Steps:**
1. Belum diproses (Tunggu Antrian)
2. ACC Pengerjaan
3. Check di Bengkel
4. Pengerjaan
5. Buka RQ (Order Parts) - *Auto-updated from Part Tracking*
6. Parts Datang (Parts Received) - *Auto-updated from Part Tracking*
7. Body Paint
8. Wrapping/Acc Tambahan
9. Pemberkasan
10. Proses Close Job
11. Proses Invoice - *Auto-updated from Finance Kanban*
12. Menunggu Pembayaran - *Auto-updated from Finance Kanban*
13. Sudah Dibayar - *Auto-updated from Finance Kanban*

---

### 2. Job Kanban Board

**Purpose:** Visual workflow management for job work status.

**Access:** Jobs → Kanban View

| Feature | Description |
|---------|-------------|
| Drag & Drop | Move jobs between status columns |
| Role-Based Restrictions | Each role can only change to specific statuses |
| Remark Prompt | Add optional remark when changing status |
| Auto-Updated Statuses | 5, 6, 11, 12, 13 are controlled by other systems |

**Role Permissions:**

| Role | Can Change To |
|------|--------------|
| Admin/Manager | All statuses |
| Control Tower | 1, 2, 3, 4, 7, 8, 9, 10 |
| Foreman/SA | 1, 2, 3, 4, 7, 8 (own assigned jobs only) |
| Sparepart | Only via Part Tracking Kanban |
| Finance | Only via Finance Kanban |

---

### 3. Part Tracking Kanban

**Purpose:** Track parts requisition (RQ) from order to receipt.

**Access:** Operations → Part Tracking → Kanban View

**Workflow:**
```
┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐
│  PENDING  │ → │  BUKA RQ  │ → │  ORDERED  │ → │ CONFIRMED │ → │  SHIPPED  │ → │ RECEIVED  │
│  (Jobs)   │   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│   │(PartOrder)│
└───────────┘   └───────────┘   └───────────┘   └───────────┘   └───────────┘   └───────────┘
```

**Features:**

| Feature | Description |
|---------|-------------|
| Pending = Jobs | Shows jobs with need_part=true, not yet RQ opened |
| Multi-RQ per Job | One job can have multiple RQ entries |
| 1-Step Movement | Can only drag to adjacent column |
| RQ Number Prompt | Enter RQ number when moving Pending → Buka RQ |
| Order Details Modal | Enter order #, dates when Buka RQ → Ordered |
| Work Status Sync | Job updates to "6. Parts Datang" when ALL RQs received |

**Role Permissions:**

| Role | Can Do |
|------|--------|
| Admin | All actions |
| Control Tower | Open RQ (Pending → Buka RQ) |
| Foreman | Open RQ for own assigned jobs only |
| Sparepart | All status changes (Buka RQ → Received) |
| SA | View only, filtered to own jobs |

**Default Filters by Role:**

| Role | Default View |
|------|-------------|
| SA | Own assigned jobs |
| Foreman | Own assigned jobs |
| Sparepart | All jobs with need_part |
| Others | All jobs with need_part |

---

### 4. Finance Kanban

**Purpose:** Track invoice and payment status.

**Access:** Operations → Finance Kanban

**Workflow:**
```
Open Invoice → Sent to Customer → Paid (Partial) → Paid (Full)
```

**Auto-Updates Job Work Status:**
- Invoice created → "11. Proses Invoice"
- Invoice pending → "12. Menunggu Pembayaran"
- Invoice paid → "13. Sudah Dibayar"

---

### 5. Vehicle Management

**Purpose:** Track vehicles and their service history.

| Feature | Description |
|---------|-------------|
| Vehicle List | All vehicles with job counts |
| Vehicle Detail | Info + job history with sales stats |
| Workshop Status | Toggle "In Workshop" flag |
| Customer Link | Click to view customer detail |

**Stats Cards:** Total Jobs, Uninvoiced, Projected Sales, Invoiced Sales

---

### 6. Customer Lookup

**Purpose:** View customers aggregated from jobs and vehicles.

| Feature | Description |
|---------|-------------|
| Customer List | Unique customers with vehicle/job counts |
| Customer Detail | Stats, vehicles, job history |
| Search | Find customers by name |
| Sales Tracking | Projected (uninvoiced) vs Invoiced sales |

---

### 7. Data Import

**Purpose:** Import data from Excel/ODS files exported from DMS.

#### Import Types:

| Type | Sheet/Source | Purpose |
|------|--------------|---------|
| **Job Progress** | Progress Job sheet | Import/update uninvoiced jobs |
| **Invoiced** | Invoiced data export | Mark jobs as invoiced, create invoice history |
| **Booking** | Booking sheet | Import customer bookings |
| **PDI** | PDI sheet | Pre-Delivery Inspection records |
| **Towing** | Towing sheet | Towing service records |

**Import Features:**
- Import history with success/failed counts
- Failed row logging with error details
- View import details with failed row breakdown
- Automatic customer name sanitization

**Access:** Operations → Import History

---

### 8. Invoice History

**Purpose:** Track multiple invoice events per job (invoices, credit notes).

| Feature | Description |
|---------|-------------|
| Invoice Records | Each invoice/CN stored as JobInvoice |
| Credit Note Detection | Negative amounts auto-detect as CN |
| Total Calculation | Sum of effective amounts (CN negative) |
| Type Sale & Department | Visualized with badges |

**View:** Job Detail → Invoice History section

---

### 9. Customer Duplicate Management

**Purpose:** Detect and merge similar customer names to maintain data quality.

#### Duplicate Detection
- Uses Levenshtein distance (>80% similarity)
- Uses similar_text comparison (>80% match)
- Normalizes common patterns (PT/PT., commas, spaces)

#### Source Classification

| Source Type | Meaning | Action Required |
|-------------|---------|-----------------|
| **DMS Import** | Duplicate from Invoice/Uninvoiced import | Fix in main DMS system |
| **Job Progress Import** | User error during import | Train users |
| **Manual Entry** | User typed incorrectly | Train users |

#### Merge Workflow
1. Go to Customers → Merge Duplicates
2. Toggle groups to merge
3. Select canonical name (name to keep)
4. Click "Merge All Selected Groups"
5. All jobs/vehicles updated to canonical name

**All merges logged for reporting**

---

### 10. Reports

| Report | Purpose | Filters |
|--------|---------|---------|
| **Uninvoiced Jobs** | Jobs pending invoice | Search, Date range |
| **Invoiced Jobs** | Completed jobs | Search, Date range |
| **Needs Parts** | Jobs flagged for parts | Search |
| **Customer Merges** | Merge history for DMS cleanup | Source, Date, Search |
| **Report Builder** | Custom filtering + saved reports | Multiple filters |

**Customer Merge Report Exports:** Excel, CSV, PDF (with color-coded DMS issues)

---

### 11. Data Tracker

**Purpose:** Track any record by WIP, plate number, or customer name across all data sources.

**Searches across:** Jobs, Vehicles, Bookings, PDI, Towing

**Output:** Timeline of all related records with dates and status

---

### 12. Master Data

| Entity | Purpose |
|--------|---------|
| **Service Advisors** | SA master list for jobs |
| **Foremen** | Foreman master list |

**Access:** Admin/Manager roles only

---

### 13. Administration

| Feature | Purpose | Access |
|---------|---------|--------|
| **User Management** | Create/edit users, assign roles | Admin only |
| **LDAP Settings** | Configure LDAP authentication | Admin only |
| **Data Cleanup** | Truncate data tables | Admin only |

---

### 14. Audit System

**Purpose:** Track all data changes for accountability.

**Audited Models:**
- Job, Vehicle, Booking, PdiRecord, TowingRecord
- CustomerMergeLog, JobInvoice, PartOrder

**Logged Data:**
- User who made change
- Action (created, updated, deleted)
- Old and new values
- Timestamp, IP address

**View:** Audit → Audit Logs

---

## User Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Full access, user management, data cleanup |
| **Manager** | All operations, master data, audit |
| **Control Tower** | Job management, remarks, imports, Kanban (except 5,6,11,12,13) |
| **SA (Service Advisor)** | View jobs, add remarks, Kanban (1-4, 7-8 for own jobs) |
| **Foreman** | View jobs, add remarks, Kanban (1-4, 7-8 for own jobs), Open RQ |
| **Sparepart** | Part Tracking Kanban management |
| **Finance** | Finance Kanban, invoice management |

---

## Key Workflows

### New Job Entry via Import
```
1. Export Progress Job from DMS → Excel/ODS
2. Import → Upload file → Select sheet
3. Jobs created/updated with import_id tracking
4. View jobs in Jobs list
```

### Mark Job as Invoiced
```
1. Export Invoiced data from DMS
2. Import → Invoiced type
3. Job status → invoiced
4. Invoice record created in job_invoices
```

### Part Tracking Flow
```
1. Mark job as "Needs Parts" (job list or detail)
2. Job appears in Part Tracking Kanban → Pending column
3. Drag to "Buka RQ" → Enter RQ number → PartOrder created
4. Drag to "Ordered" → Enter order#, dates (Sparepart role)
5. Continue: Confirmed → Shipped → Received
6. When ALL RQs received → Job work_status = "6. Parts Datang"
```

### Handle Duplicate Customers
```
1. Dashboard shows alert if duplicates detected
2. Click "Review & Merge" 
3. Select groups to merge
4. Choose canonical name
5. Merge → all references updated
6. Check Customer Merge Report for DMS cleanup list
```

### Generate DMS Cleanup Report
```
1. Reports → Customer Merges
2. Filter by "DMS Import" source
3. Export to Excel/PDF
4. Use list to clean source data in DMS
```

---

## Database Schema (Key Tables)

| Table | Purpose |
|-------|---------|
| `jobs` | Main job records |
| `vehicles` | Vehicle master |
| `job_invoices` | Invoice history per job |
| `part_orders` | Part requisitions (RQs) |
| `imports` | Import history |
| `customer_merge_logs` | Merge audit trail |
| `bookings` | Booking records |
| `pdi_records` | PDI records |
| `towing_records` | Towing records |
| `audit_logs` | Change audit trail |
| `users` | User accounts |
| `remarks` | Job remarks |
| `job_activities` | Job activity timeline |

---

## Artisan Commands

| Command | Purpose |
|---------|---------|
| `php artisan data:clean-customer-names` | Sanitize existing customer names |

---

## Technical Stack

- **Framework:** Laravel 10+
- **Database:** MySQL
- **Frontend:** Blade + Bootstrap 5
- **Icons:** Bootstrap Icons
- **Excel:** PhpSpreadsheet
- **PDF:** Dompdf
- **Auth:** Local + LDAP support
