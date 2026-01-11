# Dashboard & Widgets

The personalized dashboard system allows you to customize your view with 28 different widgets, organized by position and visibility preferences.

## Accessing Dashboard Customization

1. Go to the **Dashboard** (home page)
2. Click the **palette icon (🎨)** in the top-right of the hero section
3. Or navigate to: `/dashboard/customize`

## Available Widgets

### Core Widgets

| Widget | Description | Best For |
|--------|-------------|----------|
| **Overview Stats** | Uninvoiced, Needs Parts, Invoiced, In Workshop counts | All users |
| **My Jobs** | Jobs assigned to you | SA, Foreman |
| **Work Status** | Status distribution breakdown | All users |
| **Recent Jobs** | Last 5 open jobs in system | All users |
| **Needs Parts** | Jobs waiting for parts | All users |
| **Parts Tracking** | Pending, Due Soon, Overdue orders | Sparepart, Admin |
| **Job Trend Chart** | 7-day new vs invoiced trend | Manager, CT |
| **Job Aging** | Age distribution doughnut chart | Manager, CT |
| **SA Revenue** | Top 5 Service Advisor ranking | Manager, Finance |
| **Quick Actions** | Shortcuts to common tasks | All users |
| **Today's Bookings** | Scheduled bookings for today | SA, CT |
| **Pending Invoices** | Invoices awaiting payment | Finance |
| **Saved Filters** | Your saved report filters | All users |

### Productivity Widgets

| Widget | Description | Best For |
|--------|-------------|----------|
| **Notifications** | Unread notifications with quick actions | All users |
| **My Performance** | Personal KPIs (jobs closed, revenue, avg days) | SA, Foreman |
| **Team Workload** | Visual technician assignment chart | Foreman, Manager |
| **SLA Alerts** | Jobs approaching/exceeding thresholds | Manager, CT |
| **Customer Follow-ups** | Customers due for contact | SA |
| **Overdue Jobs** | Jobs exceeding X days uninvoiced | All users |

### Calendar Widgets

| Widget | Description | Best For |
|--------|-------------|----------|
| **Week Calendar** | Mini week view with events | SA, Foreman |
| **Upcoming PDI** | Scheduled PDI inspections | SA, CT |
| **Towing Schedule** | Upcoming towing pickups | CT |

### Finance Widgets

| Widget | Description | Best For |
|--------|-------------|----------|
| **Daily Revenue** | Today's invoiced vs target | Finance, Manager |
| **Receivables Aging** | Overdue payment breakdown | Finance |
| **Top Customers** | Highest value customers this month | Manager |

### System Widgets

| Widget | Description | Best For |
|--------|-------------|----------|
| **System Status** | Backup, disk usage, scheduler health | Admin |
| **Announcements** | Company-wide announcements | All users |
| **Activity Feed** | Recent activity on your jobs | SA, Foreman |

## Customizing Your Dashboard

### Enable/Disable Widgets

1. Go to **Dashboard → Customize** (🎨 icon)
2. Toggle the switch next to each widget
3. Grey = disabled, Blue = enabled
4. Click **Save Changes**

### Reorder Widgets

1. Drag widgets using the handle (≡) icon
2. Drop in desired position
3. Changes save automatically via AJAX

### Reset to Default

1. Click **Reset to Default** button
2. Confirms and restores your role's default layout

## Role-Based Defaults

Each role has an optimized default widget set:

- **Service Advisor**: Stats, Notifications, My Jobs, My Performance, Bookings, Follow-ups
- **Foreman**: Stats, Notifications, My Jobs, Team Workload, Performance, Parts
- **Finance**: Stats, Notifications, Daily Revenue, Pending Invoices, Receivables
- **Sparepart**: Stats, Notifications, Parts Tracking, Needs Parts, Overdue
- **Manager**: Stats, Notifications, SLA Alerts, Trends, Revenue, Team, Top Customers
- **Admin**: All key widgets including System Status
- **Control Tower**: Stats, Notifications, SLA, Work Status, PDI, Towing

## Tips

- Widgets you can't access for your role won't appear in the list
- Chart widgets require Chart.js (included)
- Large widgets are full-width, small widgets are half-width
- Dashboard loads only data for enabled widgets (better performance)
