# VitaPro Appointments FSE

A comprehensive WordPress plugin for healthcare appointment booking systems, designed specifically for Full Site Editing (FSE) themes with complete Elementor compatibility.

## Description

VitaPro Appointments FSE is a powerful and flexible appointment booking plugin that provides healthcare professionals with a complete solution for managing appointments, services, and professionals. Built with modern WordPress standards, FSE compatibility, and full Elementor integration in mind.

## Features

### Core Functionality
- **Service Management**: Create and manage healthcare services with pricing, duration, and descriptions
- **Professional Management**: Manage healthcare professionals with bios, specialties, and working hours
- **Appointment Booking**: Complete booking system with calendar integration
- **Availability Management**: Flexible working hours and holiday management
- **Email Notifications**: Automated email confirmations and reminders

### Gutenberg Blocks
- **Booking Form Block**: Interactive appointment booking form
- **Service List Block**: Display available services in grid or list layout
- **Professional List Block**: Showcase healthcare professionals
- **Availability Calendar Block**: Visual calendar showing available dates
- **My Appointments Block**: User dashboard for managing appointments

### Elementor Widgets
- **Appointment Booking Form Widget**: Full-featured booking form with extensive styling options
- **Service List Widget**: Customizable service display with grid/list layouts
- **Professional List Widget**: Professional showcase with image and bio options
- **Availability Calendar Widget**: Interactive calendar with custom styling
- **My Appointments Widget**: User dashboard with appointment management

### Admin Features
- **Comprehensive Settings**: Extensive configuration options
- **Custom Fields**: Add custom fields to booking forms
- **Email Templates**: Customizable email templates
- **Appointment Management**: Full appointment lifecycle management
- **Reports and Analytics**: Basic reporting functionality

### Frontend Features
- **Responsive Design**: Mobile-friendly interface
- **FSE Theme Integration**: Seamless integration with Full Site Editing themes
- **Elementor Compatibility**: Complete Elementor widget suite with advanced styling options
- **User Dashboard**: Frontend appointment management for patients
- **Calendar Integration**: Interactive calendar for date selection
- **Multi-step Booking**: Intuitive step-by-step booking process

## Installation

1. Download the plugin files
2. Upload the `vitapro-appointments-fse` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin settings under 'VitaPro Appointments' in the admin menu

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- FSE-compatible theme (recommended)
- Elementor 3.0+ (optional, for Elementor widgets)

## Configuration

### Initial Setup

1. **Services**: Navigate to VitaPro Appointments > Services to create your healthcare services
2. **Professionals**: Add healthcare professionals under VitaPro Appointments > Professionals
3. **Settings**: Configure general settings, email templates, and custom fields
4. **Working Hours**: Set up working hours for each professional
5. **Holidays**: Configure holidays and non-working days

### Gutenberg Block Usage

#### Booking Form Block
Add the booking form block to any page or post where you want patients to book appointments.

**Attributes:**
- `serviceId`: Pre-select a specific service
- `professionalId`: Pre-select a specific professional
- `showServiceStep`: Show/hide service selection step
- `showProfessionalStep`: Show/hide professional selection step
- `formId`: Unique form identifier

#### Service List Block
Display a list of available services with customizable layout options.

**Attributes:**
- `layout`: Grid or list layout
- `columns`: Number of columns (grid layout)
- `showImage`: Display service images
- `showDescription`: Display service descriptions
- `showPrice`: Display service pricing
- `showDuration`: Display service duration
- `categoryId`: Filter by service category
- `limit`: Limit number of services displayed

#### Professional List Block
Showcase healthcare professionals with their information and specialties.

**Attributes:**
- `layout`: Grid or list layout
- `columns`: Number of columns (grid layout)
- `showImage`: Display professional photos
- `showBio`: Display professional bios
- `showServices`: Display professional services
- `serviceId`: Filter by specific service
- `limit`: Limit number of professionals displayed

#### Availability Calendar Block
Display an interactive calendar showing available appointment dates.

**Attributes:**
- `serviceId`: Filter by specific service
- `professionalId`: Filter by specific professional
- `monthsToShow`: Number of months to display
- `showLegend`: Show calendar legend

#### My Appointments Block
Provide a user dashboard for managing appointments (requires user login).

**Attributes:**
- `showUpcoming`: Display upcoming appointments
- `showPast`: Display past appointments
- `allowCancellation`: Allow appointment cancellation
- `upcomingLimit`: Limit upcoming appointments shown
- `pastLimit`: Limit past appointments shown

### Elementor Widget Usage

All Gutenberg blocks are also available as Elementor widgets with enhanced styling options:

#### Elementor Widget Features
- **Advanced Styling Controls**: Typography, colors, spacing, borders, shadows
- **Responsive Design Options**: Different settings for desktop, tablet, and mobile
- **Animation Support**: Entrance animations and hover effects
- **Custom CSS Classes**: Add custom CSS classes for further customization
- **Live Preview**: Real-time preview in Elementor editor

#### Finding VitaPro Widgets
1. Open Elementor editor
2. Look for the "VitaPro Appointments" category in the widget panel
3. Drag and drop widgets onto your page
4. Configure settings in the left panel

#### Widget-Specific Features

**Booking Form Widget:**
- Form styling options (background, borders, shadows)
- Button customization (colors, typography, hover effects)
- Step indicator styling
- Form field styling

**Service List Widget:**
- Card layout customization
- Image styling and hover effects
- Typography controls for titles, descriptions, and prices
- Grid/list layout options with responsive columns

**Professional List Widget:**
- Professional card styling
- Image border radius and sizing options
- Bio text styling
- Service tag customization

**Availability Calendar Widget:**
- Calendar color schemes
- Day state styling (available, unavailable, selected)
- Header and navigation styling
- Legend customization

**My Appointments Widget:**
- Appointment card styling
- Status badge customization
- Typography controls
- Action button styling

## Customization

### Styling
The plugin includes comprehensive CSS that integrates with both FSE themes and Elementor:

1. **Theme.json Integration**: The plugin respects theme.json color palettes and typography settings
2. **Elementor Integration**: Full Elementor styling controls with live preview
3. **Custom CSS**: Add custom CSS through your theme, WordPress Customizer, or Elementor
4. **CSS Variables**: The plugin uses CSS custom properties for easy theming

### Email Templates
Customize email templates by editing the files in the `templates/email/` directory:

- `new-booking-admin.php`: New booking notification for admin
- `new-booking-patient.php`: Booking confirmation for patient
- `reminder-patient.php`: Appointment reminder for patient
- `cancellation-admin.php`: Cancellation notification for admin
- `cancellation-patient.php`: Cancellation confirmation for patient

### Custom Fields
Add custom fields to the booking form through the admin settings:

1. Navigate to VitaPro Appointments > Settings > Custom Fields
2. Click "Add Custom Field"
3. Configure field type, label, and options
4. Save settings

## Elementor Compatibility

### Supported Elementor Features
- **Theme Builder**: Use widgets in headers, footers, and templates
- **Popup Builder**: Add booking forms to popups
- **WooCommerce Builder**: Integrate with WooCommerce pages
- **Dynamic Content**: Support for Elementor Pro dynamic content
- **Global Widgets**: Save widgets as global templates
- **Responsive Editing**: Different settings for each device

### Elementor Pro Features
- **Form Integration**: Connect with Elementor Pro forms
- **Dynamic Tags**: Use appointment data in dynamic content
- **Theme Builder Integration**: Use in custom post templates
- **Popup Triggers**: Trigger popups based on appointment actions

### Performance Optimization
- **Conditional Loading**: Elementor assets only load when needed
- **Optimized CSS**: Minimal CSS footprint for Elementor widgets
- **Lazy Loading**: Images and content load efficiently
- **Caching Compatibility**: Works with popular caching plugins

## Hooks and Filters

### Actions
- `vitapro_appointment_created`: Fired when a new appointment is created
- `vitapro_appointment_status_changed`: Fired when appointment status changes
- `vitapro_before_booking_form`: Before booking form is rendered
- `vitapro_after_booking_form`: After booking form is rendered
- `vitapro_elementor_widget_registered`: After Elementor widgets are registered

### Filters
- `vitapro_booking_form_fields`: Modify booking form fields
- `vitapro_email_template_args`: Modify email template arguments
- `vitapro_available_time_slots`: Modify available time slots
- `vitapro_appointment_statuses`: Modify appointment status options
- `vitapro_elementor_widget_settings`: Modify Elementor widget settings

## Troubleshooting

### Common Issues

**Booking form not displaying**
- Ensure the block/widget is properly configured
- Check that services and professionals are created
- Verify theme compatibility

**Elementor widgets not appearing**
- Ensure Elementor is installed and activated
- Check that you're using a compatible Elementor version (3.0+)
- Clear Elementor cache

**Email notifications not sending**
- Check WordPress email configuration
- Verify SMTP settings if using custom email
- Check spam folders

**Calendar not loading**
- Ensure JavaScript is enabled
- Check for JavaScript conflicts
- Verify AJAX endpoints are accessible

**Styling issues**
- Check theme compatibility
- Verify CSS is loading properly
- Check for CSS conflicts
- Clear Elementor cache if using Elementor

### Debug Mode
Enable WordPress debug mode to troubleshoot issues:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);