# Admin Panel Specification

> Private dashboard for restaurant management. **Vue 3 UI** inside Laravel, talking to Laravel API.

---

## Admin Panel Goal

A private dashboard where the restaurant can:

- Manage menu + offers
- Receive and process orders
- Control website settings (logo, hours, delivery fee, WhatsApp)
- See basic sales stats

---

## Admin Roles

### 1) Owner (Full access)

- Can edit restaurant settings
- Manage staff users
- Full access to everything

### 2) Staff (Limited)

- Can manage orders
- Can update menu/offers (optional toggle)
- Cannot change billing/restaurant ownership settings

---

## Admin Layout (UI Structure)

### Top Bar

- Restaurant name + logo
- Quick search (menu items / orders)
- Notifications (new order)
- Profile menu (settings, logout)

### Left Sidebar

- Dashboard
- Orders
- Menu
  - Categories
  - Items
- Offers
- Customers (optional)
- Reports (basic)
- Settings
- Staff/Users (owner only)

### Main Area

- Data tables + filters
- Create/Edit forms in modal or dedicated page
- Clean mobile-friendly responsive layout

---

## Pages & Features

### 1) Dashboard (Home)

**Purpose:** Quick overview

**Widgets:**

- Today's Orders (count)
- Pending Orders
- Revenue Today (optional)
- Top selling items (optional)
- Orders chart (last 7 days)

**Quick actions:**

- "Add Menu Item"
- "Create Offer"
- "View Pending Orders"

---

### 2) Orders Management (Most Important Page)

**Purpose:** Restaurant runs the business from here.

#### Order List

- **Tabs/filters:**
  - All
  - Pending
  - Accepted
  - Preparing
  - Out for delivery
  - Delivered
  - Cancelled
- **Search by:**
  - order ID
  - customer phone
  - customer name
- Sort by newest first
- Show: time since order ("5 min ago")

#### Order Details (Drawer or Page)

- Customer info: name, phone, address
- Delivery type: Delivery / Pickup
- Items list:
  - item name
  - qty
  - price
  - subtotal
- Totals:
  - subtotal
  - delivery fee
  - discount
  - grand total
- Customer note (special instructions)

#### Actions

- Change status with 1 click
- Print order (optional)
- Send WhatsApp update to customer (optional)
- Cancel with reason

#### Real-time / Notifications

- When a new order comes:
  - sound + toast notification
  - badge count on Orders menu
- *Real-time can be added later; MVP can be polling every 10–15 sec*

---

### 3) Menu Management

Split into **Categories** and **Items**.

#### 3.1 Categories

- Add/Edit/Delete categories
- Reorder categories (drag drop)
- Toggle active/inactive
- Category image (optional)

#### 3.2 Menu Items

**Item table view:**

- Image thumbnail
- Name
- Category
- Price
- Status (Available / Unavailable)
- Featured (show on homepage)
- Actions (edit, delete)

**Item create/edit form fields:**

- Name
- Slug (auto)
- Category
- Description
- Price
- Discount price (optional)
- Food type: Veg / Non-veg (optional)
- Spicy level (optional)
- Preparation time (optional)
- Image upload (1–3 images)
- Availability toggle
- Sort order / priority

**Extra options (future-ready):**

- Add-ons (extra cheese, fries, etc.)
- Variants (Small/Medium/Large)

---

### 4) Offers / Promotions

**Purpose:** Restaurant can run campaigns anytime.

#### Offers list

- Title
- Banner image
- Type:
  - percentage discount
  - fixed amount discount
  - Buy 1 Get 1
  - free delivery
- Start date / End date
- Active toggle
- Show on homepage toggle

#### Offer create/edit fields

- Title
- Description
- Offer type + value
- Minimum order amount (optional)
- Coupon code (optional)
- Expiry date
- Banner image
- Terms & conditions (optional)

---

### 5) Restaurant Settings (Controls the Customer Website)

**Purpose:** Owner can change branding and business info without developer.

#### Sections

**Branding**

- Restaurant name
- Logo
- Cover/banner image
- Theme color (primary color)
- Short tagline

**Contact**

- Phone
- WhatsApp number
- Email
- Address
- Google map link

**Business Rules**

- Opening hours (per day)
- Delivery fee
- Minimum order amount
- Delivery radius (optional)
- Pickup enabled toggle
- Delivery enabled toggle
- Estimated delivery time

**SEO (optional)**

- meta title
- meta description

---

### 6) Staff / Users (Owner Only)

- Create staff user
- Assign role (Owner/Staff)
- Reset password
- Disable user access
- Audit: last login (optional)

---

### 7) Reports (Simple but valuable)

**MVP reports:**

- Orders by day (last 7/30 days)
- Best selling items
- Revenue summary (if you track totals)

**Export:**

- CSV export for orders (optional)

---

## 8) System Features (Admin Quality)

These make it "product-level":

### Permissions & Security

- Sanctum auth
- Policies: user can only access their `restaurant_id` data
- Rate limit for auth endpoints

### Data Quality

- Soft delete where needed
- Image validation + resizing
- Slug uniqueness per restaurant

### UX Features

- Fast table loading (pagination)
- Filters + search everywhere
- Confirm dialogs on delete
- Toast notifications after save
- Empty states (no menu items yet)

---

## What the Admin Panel Controls on the Customer Site

Whatever admin changes should **instantly reflect** on the customer-facing website:

- Menu items + categories
- Availability (out of stock)
- Offers banner + promo page
- Restaurant info + hours + delivery fee

---

*Last updated: March 4, 2025*
