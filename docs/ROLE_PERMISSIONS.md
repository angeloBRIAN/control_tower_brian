# Role Permission System Documentation

This application uses an ERPNext-style dynamic role permission system.

## Overview

- **Roles** - Customizable roles (admin can create new ones)
- **DocType Permissions** - Read/Write/Create/Delete per model
- **Field Permissions** - Control which fields each role can edit
- **Kanban Permissions** - Control which statuses each role can change to

## Adding a New DocType

When you add new features/menus, follow these steps:

### Step 1: Register DocType in Role Model

Edit `app/Models/Role.php`:

```php
public static function getDocTypes(): array
{
    return [
        'Job' => 'Workshop Jobs',
        'Vehicle' => 'Vehicles',
        // ... existing ...
        'NewFeature' => 'New Feature Name',  // ← Add here
    ];
}
```

### Step 2: Add Field Definitions (Optional)

If your DocType has editable fields, edit `app/Models/FieldPermission.php`:

```php
public static function getFieldsForDocType(string $doctype): array
{
    $fields = [
        // ... existing ...
        'NewFeature' => [
            'title' => 'Title',
            'status' => 'Status',
            'description' => 'Description',
        ],
    ];
    return $fields[$doctype] ?? [];
}
```

### Step 3: Use Permissions in Code

**In Controller:**
```php
public function index()
{
    if (!auth()->user()->canRead('NewFeature')) {
        abort(403, 'Access denied');
    }
    // ...
}

public function store(Request $request)
{
    if (!auth()->user()->canDo('NewFeature', 'create')) {
        abort(403);
    }
    // ...
}
```

**In Views:**
```blade
{{-- Show field only if user can read it --}}
@if(auth()->user()->canReadField('NewFeature', 'title'))
    <input name="title" 
           value="{{ $item->title }}"
           @if(!auth()->user()->canWriteField('NewFeature', 'title')) 
               disabled readonly 
           @endif>
@endif
```

### Step 4: Update Default Permissions

Edit `database/seeders/RolePermissionSeeder.php` to set defaults for existing roles.

Then run:
```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Available Permission Methods

| Method | Description |
|--------|-------------|
| `canDo($doctype, $action)` | Check read/write/create/delete/export |
| `canRead($doctype)` | Shortcut for canDo($doctype, 'read') |
| `canWrite($doctype)` | Shortcut for canDo($doctype, 'write') |
| `canReadField($doctype, $field)` | Check field read permission |
| `canWriteField($doctype, $field)` | Check field write permission |

## Admin UI

Access Role Management at: **Administration → Role Permissions**

From there you can:
- Create custom roles
- Configure DocType permissions (matrix view)
- Configure Field permissions per DocType

---

## System Roles

### Administrator

Full access to all features, user management, data cleanup.

### Manager

All operations, master data, audit. Cannot manage users.

### Control Tower

| Feature | Permission |
|---------|------------|
| View Jobs | All jobs |
| Job Kanban | Can change to statuses 1, 2, 3, 4, 7, 8, 9, 10 |
| Restricted Statuses | Cannot change to 5, 6, 11, 12, 13 |
| Part Tracking | Can open RQ (Pending → Buka RQ) |
| Add Remarks | All jobs |

### Service Advisor (SA)

| Feature | Permission |
|---------|------------|
| View Jobs | All jobs |
| Job Kanban | Own assigned jobs only, statuses 1-4, 7-8 |
| Restricted Statuses | Cannot change to 5, 6, 9, 10, 11, 12, 13 |
| Part Tracking | View only (filtered to own jobs) |
| Add Remarks | Own assigned jobs |

### Foreman

| Feature | Permission |
|---------|------------|
| View Jobs | All jobs |
| Job Kanban | Own assigned jobs only, statuses 1-4, 7-8 |
| Restricted Statuses | Cannot change to 5, 6, 9, 10, 11, 12, 13 |
| Part Tracking | Can open RQ for own assigned jobs |
| Add Remarks | Own assigned jobs |

### Sparepart

| Feature | Permission |
|---------|------------|
| View Jobs | All jobs |
| Job Kanban | Cannot use (use Part Tracking instead) |
| Part Tracking | All status changes (Buka RQ → Received) |
| Cannot Do | Open RQ (Pending → Buka RQ) |
| Add Remarks | Jobs with need_part only |

### Finance

| Feature | Permission |
|---------|------------|
| View Jobs | Invoiced jobs only |
| Job Kanban | Cannot use (use Finance Kanban) |
| Finance Kanban | Full access (3 columns) |
| Add Remarks | Invoiced jobs only |

---

## Kanban Permission Details

### Job Kanban Work Status Restrictions

| Role | Cannot Change To |
|------|-----------------|
| Admin | None (full access) |
| Manager | None (full access) |
| Control Tower | 5, 6, 11, 12, 13 |
| Foreman | 5, 6, 9, 10, 11, 12, 13 |
| SA | 5, 6, 9, 10, 11, 12, 13 |
| Sparepart | All (use Part Tracking) |
| Finance | All (use Finance Kanban) |

**Status Reference:**
- 5 = Buka RQ (auto from Part Tracking)
- 6 = Parts Datang (auto from Part Tracking)
- 9 = Pemberkasan
- 10 = Proses Close Job
- 11-13 = Finance statuses (auto from Finance Kanban)

### Part Tracking Kanban Permissions

| Action | Allowed Roles |
|--------|--------------|
| Open RQ (Pending → Buka RQ) | Admin, Control Tower, Foreman (own jobs) |
| Update Status (Buka RQ → Received) | Admin, Sparepart |
| View Only | SA, Finance |

### Default Filters by Role

| Role | Part Tracking Default View |
|------|---------------------------|
| SA | Own assigned jobs |
| Foreman | Own assigned jobs |
| Sparepart | All jobs with need_part |
| Others | All jobs with need_part |

---

## Role Comparison Matrix

| Role | Job Kanban | Finance Kanban | Part Tracking | Add Remarks |
|------|-----------|----------------|---------------|-------------|
| Admin | ✅ All | ✅ All | ✅ All | ✅ All |
| Manager | ✅ All | ✅ All | ✅ All | ✅ All |
| Control Tower | ✅ (not 5,6,11-13) | ❌ | ✅ Open RQ | ✅ All |
| SA | ✅ Own (1-4,7,8) | ❌ | 👁 View | ✅ Own |
| Foreman | ✅ Own (1-4,7,8) | ❌ | ✅ Open RQ (own) | ✅ Own |
| Sparepart | ❌ | ❌ | ✅ All except Open RQ | ✅ need_part |
| Finance | ❌ | ✅ All | ❌ | ✅ Invoiced |

**Legend:**
- ✅ = Full access
- ❌ = No access
- 👁 = View only
- Own = Own assigned jobs only

---

## Assigning Roles

1. Go to **Admin → Users**
2. Edit user or create new
3. Set role from dropdown
4. Save

**Important:** For SA and Foreman roles, also link the user to a Service Advisor or Foreman record in Master Data to enable the "own jobs" filtering.
