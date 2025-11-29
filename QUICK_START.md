# Quick Start Testing Guide

## 5-Minute Test Setup

### Step 1: Install Plugin (1 minute)
1. Upload plugin to `/wp-content/plugins/wp-booking-system/`
2. Go to **Plugins** → Activate "WP booking Luca"

### Step 2: Configure Settings (1 minute)
1. Go to **WP booking Luca → Settings**
2. Set prices:
   - Price per Adult: `50`
   - Price per Kid: `25`
   - Currency: `CHF`
3. Set email: Your email address
4. Set Chalet Maximum Capacity: `10`
5. Click **Save Settings**

### Step 3: Create Booking Page (1 minute)
1. Go to **Pages → Add New**
2. Title: "Book Now"
3. Option A: Add shortcode: `[wp_booking_form]`
   Option B: Use Gutenberg editor and add "Booking Calendar" block
4. Click **Publish**
5. View the page

### Step 4: Test Booking (1 minute)
1. On the booking page:
   - Select check-in date (tomorrow)
   - Select check-out date (3 days later)
   - Adults: `2`, Kids: `1`
   - Fill in name, email, phone
   - Click **Book Now**
2. ✅ Check for success message
3. ✅ Check your email for confirmation

### Step 5: Verify in Admin (1 minute)
1. Go to **WP booking Luca → All Bookings**
2. ✅ See your test booking
3. Check your admin email for notification (if configured)
4. Go to **WP booking Luca → Booking Calendar**
5. ✅ See booking on calendar

## Test Widget (Optional - 2 minutes)

1. Go to **Appearance → Widgets**
2. Find **Booking Calendar** widget
3. Drag to sidebar
4. Click **Save**
5. View any page with sidebar
6. ✅ Calendar appears with availability

## Test Management Link (1 minute)

1. Open the confirmation email
2. Click **Manage Booking** link
3. ✅ Booking details displayed
4. Click **Cancel Booking**
5. ✅ Booking cancelled, email sent

## That's It! 🎉

You've tested the core functionality. For comprehensive testing, see `TESTING_GUIDE.md`.

