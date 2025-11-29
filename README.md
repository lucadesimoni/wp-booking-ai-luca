# WP booking Luca

A simple and modern booking system for WordPress with calendar management, email notifications, and price calculations.

## Features

- **Admin Calendar Interface**: Visual calendar view for managing all bookings
- **Frontend Booking Form**: Clean, modern booking form with date selection
- **Frontend Calendar Widget**: Monthly calendar widget showing availability and allowing date selection
- **Price Calculation**: Automatic price calculation based on number of adults and kids per night
- **Email Notifications**: Automatic email confirmations and cancellation notices
- **Booking Management**: Unique links for guests to view, modify, or cancel their bookings
- **Modern Design**: Responsive design that matches modern website aesthetics
- **WordPress Best Practices**: Follows all WordPress coding standards and security best practices

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-booking-system/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **WP booking Luca > Settings** to configure:
   - Price per adult (per night)
   - Price per kid (per night)
   - Currency
   - Email settings
   - Admin notification email
   - Chalet maximum capacity

## Usage

### Displaying the Booking Form

Add the booking form to any page or post using the shortcode:

```
[wp_booking_form]
```

You can also customize the title:

```
[wp_booking_form title="Book Your Stay"]
```

### Booking Management Page

Create a new page and add the shortcode:

```
[wp_booking_manage]
```

Guests will access this page via a unique token sent in their confirmation email. The URL format will be:
`yoursite.com/your-page/?token=BOOKING_TOKEN`

### Frontend Calendar Widget

**Option 1: Gutenberg Block (Recommended)**
1. Edit a page using Gutenberg editor
2. Click "+" to add a block
3. Search for "Booking Calendar"
4. Add the block and configure the title

**Option 2: Classic Widget**
1. Go to **Appearance > Widgets**
2. Find the **Booking Calendar** widget
3. Drag it to your desired sidebar
4. Configure the widget title (optional)

The calendar displays:
- Available dates (green)
- Booked dates (red)
- Click on dates to select them in the booking form

### Calendar Shortcode

You can also display the calendar using a shortcode:

```
[wp_booking_calendar]
```

Or with a custom title:

```
[wp_booking_calendar title="Check Availability"]
```

### Admin Features

- **Calendar View**: Navigate to **WP booking Luca** in the admin menu to see a visual calendar of all bookings
- **All Bookings**: View a list of all bookings with details and actions
- **Settings**: Configure pricing, email settings, and chalet capacity

## Configuration

### Pricing

Set your pricing in **Bookings > Settings**:
- Price per adult per night
- Price per kid per night
- Currency symbol (e.g., CHF, EUR, USD)

### Email Settings

Configure email settings in **WP booking Luca > Settings**:
- Email from address
- Email from name
- Admin notification email (receives notifications for new bookings)

## How It Works

1. **Guest makes a booking**: Fills out the booking form with dates, guest count, and contact information
2. **Price is calculated**: Automatically calculated based on number of nights, adults, and kids
3. **Availability is checked**: System verifies the selected dates are available
4. **Confirmation email sent**: Guest receives an email with booking details and a unique management link
5. **Admin can view**: All bookings appear in the admin calendar and list view
6. **Guest can manage**: Guest can view or cancel their booking using the unique link

## Database

The plugin creates a custom table `wp_wpbs_bookings` to store all booking data. The table is automatically created when the plugin is activated.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Support

For issues or questions, please check the plugin documentation or contact support.

## License

GPL-2.0 or later
