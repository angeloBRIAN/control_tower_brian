# Announcements & Broadcasts

The broadcast system allows authorized users to send announcements to all users or specific roles, displayed on dashboards and via push notifications.

## Overview

Announcements are company-wide messages that:
- Appear in the **Announcements** dashboard widget
- Can trigger **push notifications** to all targeted users
- Can be **pinned** to stay at the top
- Can have **scheduled** publish and expiry dates
- Allow users to **dismiss** individually

## Accessing Announcements

### For Administrators

Navigate to: **Administration → Announcements**

Or URL: `/admin/announcements`

### Permissions Required

Only users with the `broadcast_announcements` permission can create announcements.

**Default roles with this permission:**
- Admin
- Manager

## Creating an Announcement

1. Go to **Administration → Announcements**
2. Click **"+ New Announcement"**
3. Fill in the form:

### Form Fields

| Field | Description |
|-------|-------------|
| **Title** | Short, descriptive headline |
| **Message** | Rich text content (WYSIWYG editor) |
| **Mark as Important** | Adds red highlight styling |
| **Pin to Top** | Keeps at top of list |
| **Send Push Notification** | Sends push to all users |
| **Target Audience** | All users or specific roles |
| **Publish Date** | Schedule for later (optional) |
| **Expiry Date** | Auto-hide after date (optional) |

### WYSIWYG Editor Features

The message editor supports:
- **Bold**, *Italic*, ~~Strikethrough~~
- Headers (H1, H2, H3)
- Bullet and numbered lists
- Text colors and backgrounds
- Links
- Text alignment

## Managing Announcements

### List View

Shows all announcements with:
- Title and importance badges
- Author name
- Target (All Users / X roles)
- Status (Active, Scheduled, Expired)
- Publish date

### Actions

| Action | Description |
|--------|-------------|
| **Edit** | Modify content and settings |
| **Resend** | Re-broadcast push notifications |
| **Delete** | Permanently remove |

## User Experience

### Dashboard Widget

Users see announcements in the **Announcements** dashboard widget:

- Important announcements have red left border
- Pinned announcements show 📌 icon
- Click X to dismiss (hides from your view only)
- Shows author and time ago

### Push Notifications

When enabled, push notifications are sent via:
1. In-app notification bell 🔔
2. Browser push notification (if subscribed)

## Targeting Options

### All Users
Default setting. Announcement visible to everyone.

### Specific Roles
Select one or more roles:
- Admin
- Manager
- Control Tower
- Service Advisor
- Foreman
- Finance
- Sparepart

Only users with selected roles will see the announcement.

## Scheduling

### Publish Date
- Leave empty = immediate publish
- Set date/time = scheduled for future

### Expiry Date
- Leave empty = never expires
- Set date/time = auto-hides after

## Dismissal

Users can dismiss announcements:
1. Click the **X** button on the widget
2. Announcement slides away
3. Won't appear for that user again
4. Other users still see it

Admins can see dismissal count in the edit view.

## API Reference

### Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/admin/announcements` | List all |
| POST | `/admin/announcements` | Create |
| GET | `/admin/announcements/create` | Create form |
| GET | `/admin/announcements/{id}/edit` | Edit form |
| PUT | `/admin/announcements/{id}` | Update |
| DELETE | `/admin/announcements/{id}` | Delete |
| POST | `/admin/announcements/{id}/resend` | Resend push |
| POST | `/announcements/{id}/dismiss` | Dismiss (AJAX) |

### Model Methods

```php
// Get announcements for user
Announcement::getForUser($user, $limit = 5);

// Broadcast push notifications
$announcement->broadcast();

// Dismiss for user
$announcement->dismiss($user);

// Check permission
Announcement::canCreate($user);
```

## Best Practices

1. **Keep titles concise** - Users scan quickly
2. **Use Important sparingly** - Only for critical info
3. **Set expiry dates** - Keep dashboard clean
4. **Target appropriately** - Don't spam irrelevant roles
5. **Check mobile** - Push notifications work on phones too
