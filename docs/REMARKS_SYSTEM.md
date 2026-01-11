# Remarks & Comments System

The remarks system allows users to add comments with images, reply to existing remarks, and mention users for notifications.

## Overview

Remarks (comments) can be added to:
- Jobs
- Bookings
- PDI Records
- Towing Records

## Adding a Remark

1. Go to any job/record detail page
2. Scroll to the **Remarks** section
3. Type your message in the text area
4. Optionally attach an image
5. Click **Add Remark**

## Features

### Text Formatting

Basic formatting supported:
- Line breaks preserved
- URLs auto-linked
- @mentions converted to links

### Image Attachments

Upload images with your remarks:

1. Click the **📎 image icon** next to the text area
2. Select an image file (JPG, PNG, GIF, WebP)
3. Image preview appears below
4. Submit with your remark

**Image Guidelines:**
- Max file size: 5MB
- Compressed automatically on upload
- Click image to view full size in lightbox

### @Mentions

Notify specific users:

1. Type `@` followed by username
2. User receives a notification
3. Mention becomes a clickable link

**Example:** `@john.doe please check this job`

### Reply to Remarks

Reply to existing remarks to create threaded discussions:

1. Click **Reply** under any remark
2. Reply box appears below
3. Type your response
4. Submit

**Replies show:**
- Nested under parent remark
- "In reply to [user]" indicator
- Collapsible thread view

## Remark Display

Each remark shows:
- User avatar/initials
- User name and role badge
- Timestamp (relative time)
- Remark content
- Image thumbnail (if any)
- Reply button
- Delete button (own remarks only)

## Notifications

Users are notified when:
- Someone replies to their remark
- They are @mentioned
- A remark is added to their assigned job

## Permissions

| Action | Who Can Do It |
|--------|---------------|
| Add remark | All authenticated users |
| Reply to remark | All authenticated users |
| View remarks | Users who can view the record |
| Delete remark | Remark author, Admin |
| Delete any remark | Admin only |

## Image Lightbox

Click any remark image to:
- View full-size image
- Navigate between images (arrow keys)
- Download original
- Close with X or Escape

## API Reference

### Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/jobs/{job}/remarks` | List job remarks |
| POST | `/jobs/{job}/remarks` | Add remark |
| POST | `/remarks/{remark}/reply` | Reply to remark |
| DELETE | `/remarks/{remark}` | Delete remark |

### Request Example (Add Remark)

```
POST /jobs/123/remarks
Content-Type: multipart/form-data

content: "Parts arrived today"
image: [file upload]
```

### Response

```json
{
  "success": true,
  "remark": {
    "id": 456,
    "content": "Parts arrived today",
    "user_name": "John Doe",
    "created_at": "2 minutes ago",
    "image_url": "/storage/remarks/abc123.jpg",
    "can_delete": true
  }
}
```

## Best Practices

1. **Be specific** - Reference parts, dates, customer requests
2. **Use @mentions** - Notify relevant team members
3. **Attach photos** - Visual evidence is valuable
4. **Reply in thread** - Keep discussions organized
5. **Update regularly** - Track job progress with remarks
