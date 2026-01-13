# Invertor CRM

A comprehensive Customer Relationship Management (CRM) system built with Laravel 11, designed for managing sales orders, purchase orders, inventory, distribution, and financial operations. The system includes automated workflows, real-time notifications, driver allocation, commission management, and integrations with Google Sheets and Twilio.

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Folder Structure](#folder-structure)
- [Feature List](#feature-list)
- [Application Flow](#application-flow)
- [API & Third-Party Integrations](#api--third-party-integrations)
- [Setup & Installation](#setup--installation)
- [Environment Variables](#environment-variables)
- [Running the Project](#running-the-project)
- [Cron Jobs / Background Tasks](#cron-jobs--background-tasks)
- [Common Use Cases](#common-use-cases)
- [Known Limitations](#known-limitations)
- [Future Improvements](#future-improvements)
- [Contribution Guidelines](#contribution-guidelines)
- [License](#license)

## Project Overview

Invertor CRM is a full-featured business management system that handles the complete lifecycle of sales and purchase orders. It manages inventory across storage facilities and drivers, automates order status transitions, allocates drivers based on geographic proximity, tracks commissions, and synchronizes financial data with Google Sheets. The system supports multiple user roles (Admin, Seller, Driver, Seller Manager) with granular permission-based access control.

## Tech Stack

### Backend
- **Framework**: Laravel 11.0
- **PHP**: 8.2+
- **Database**: MySQL/MariaDB/PostgreSQL/SQLite (configurable)
- **Queue**: Database-based queue system
- **Real-time**: Laravel Reverb (WebSocket) / Pusher support

### Frontend
- **CSS Framework**: Bootstrap 5.2.3
- **JavaScript**: Vanilla JS with jQuery
- **Build Tool**: Vite 5.0
- **UI Components**: DataTables (Yajra Laravel DataTables)
- **Rich Text Editor**: CKEditor

### Third-Party Packages
- `revolution/laravel-google-sheets` (^7.0) - Google Sheets integration
- `pusher/pusher-php-server` (^7.2) - Real-time broadcasting
- `yajra/laravel-datatables-oracle` (^11) - DataTables server-side processing
- `laravel/ui` (^4.5) - Authentication scaffolding
- `laravel/reverb` (@beta) - WebSocket server

## Folder Structure

```
invertor-crm-master/
├── app/
│   ├── Console/Commands/          # Artisan commands (cron jobs)
│   │   ├── StatusTrigger.php      # Automated order status changes
│   │   ├── TaskTrigger.php        # Task automation
│   │   ├── ChangeUserForOrderTrigger.php
│   │   ├── AddDataToSheet.php    # Google Sheets sync
│   │   ├── sendTwilloMsg.php     # Twilio messaging
│   │   └── GetTwilloTemplate.php
│   ├── Events/                    # Event classes
│   │   └── OrderStatusEvent.php   # Real-time order updates
│   ├── Helpers/                   # Helper functions
│   │   ├── Helper.php            # Core utility functions
│   │   └── Distance.php          # Geographic distance calculations
│   ├── Http/
│   │   ├── Controllers/          # Application controllers
│   │   ├── Middleware/           # Custom middleware
│   │   │   ├── StatusChecker.php # User status validation
│   │   │   └── ModuleAccessor.php # Permission checking
│   │   └── Requests/             # Form request validation
│   └── Models/                    # Eloquent models (54 models)
├── config/                        # Configuration files
│   ├── google.php                # Google Sheets config
│   ├── services.php              # Third-party services
│   └── ...
├── database/
│   ├── migrations/               # Database migrations (113 files)
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
├── resources/
│   └── views/                    # Blade templates
├── routes/
│   ├── web.php                   # Web routes
│   └── console.php               # Scheduled commands
└── storage/                      # Storage directory
```

## Feature List

### Core Modules

- **Sales Order Management**
  - Create, edit, view, and delete sales orders
  - Order status workflow with customizable statuses
  - Automated status transitions via triggers
  - Driver allocation based on geographic proximity
  - Price matching and validation
  - Order confirmation workflow
  - Proof image uploads
  - Scammer detection (duplicate phone numbers)

- **Purchase Order Management**
  - Create purchase orders from suppliers
  - Track purchase order items and totals
  - Supplier management

- **Product & Category Management**
  - Product CRUD operations
  - Category hierarchy
  - Product images (multiple images per product)
  - Product slugs, SKU, GTIN, MPN support
  - Website sales price and old price tracking
  - Product descriptions with rich text editor

- **Inventory/Stock Management**
  - Stock tracking for storage facilities
  - Stock tracking for individual drivers
  - Stock in/out operations
  - Stock reports (storage and driver-level)
  - Available stock calculations

- **Distribution System**
  - Distribution orders creation
  - Distribution items management
  - Distribution attachments

- **User & Role Management**
  - User CRUD with approval workflow
  - Role-based access control (RBAC)
  - Permission management (role-level and user-level)
  - User document uploads (required documents per role)
  - User status management (active/inactive/pending approval)
  - Geographic location tracking (lat/long) for drivers

- **Procurement Cost Management**
  - Procurement cost tracking
  - Sales price configuration

- **Financial Management**
  - Driver commission tracking
  - Seller commission tracking
  - Driver wallet management
  - Admin wallet management
  - Transaction ledger
  - Bank account management
  - Withdrawal request system (for sellers and drivers)
  - IBAN validation
  - Payment for delivery tracking

- **Reports**
  - Stock reports (filterable by driver, product, type)
  - Ledger reports
  - Financial reports (driver and seller commissions)
  - Payment logs

- **Sales Order Status Management**
  - Customizable order statuses
  - Status sequence configuration
  - Automated status triggers (time-based)
  - Task automation on status changes
  - User assignment automation
  - Status-based role filtering

- **Notifications**
  - In-app notifications
  - Real-time updates via WebSockets
  - Twilio SMS/WhatsApp notifications
  - Notification history tracking

- **Settings**
  - Application settings (title, logo, favicon)
  - Twilio credentials configuration
  - Google Sheets integration settings
  - Commission settings

- **Contact Us**
  - Contact form management

## Application Flow

### Sales Order Lifecycle

1. **Order Creation**
   - Seller creates a sales order with customer details, products, and delivery location
   - System validates product availability and prices
   - Order is assigned an order number (format: `SO-YYYY-#####`)

2. **Order Confirmation**
   - Order may require confirmation by seller manager
   - Once confirmed, order moves to "New" status (status ID: 1)

3. **Driver Allocation**
   - System calculates available drivers within delivery zone using geographic coordinates
   - Drivers are notified via in-app notifications and Twilio messages
   - Drivers can accept or reject orders

4. **Status Transitions**
   - Orders move through customizable statuses
   - Automated triggers can change statuses based on time conditions
   - Tasks can be automatically added at specific statuses
   - Responsible users can be automatically assigned

5. **Order Completion**
   - When order reaches "Closed Win" status (status ID: 10), it's marked for Google Sheets sync
   - Commission calculations are triggered
   - Financial records are updated

### Driver Allocation Algorithm

- Uses driver's latitude/longitude and order delivery location
- Calculates distance using Haversine formula
- Filters drivers within configured delivery radius
- Allocates multiple drivers for order acceptance
- Notifies drivers via real-time events and SMS/WhatsApp

### Commission Flow

- Commissions calculated based on configured commission prices
- Driver commissions tracked in `driver_wallets`
- Seller commissions tracked in `admin_wallets`
- Withdrawal requests can be created by sellers/drivers
- Admin approves/rejects withdrawal requests
- Transactions recorded in ledger

## API & Third-Party Integrations

### Google Sheets Integration

- **Purpose**: Synchronize sales orders, transactions, and commission withdrawal data
- **Package**: `revolution/laravel-google-sheets`
- **Configuration**: Set in `config/google.php` and Settings table
- **Sync Frequency**: Every 5 minutes (via cron)
- **Data Synced**:
  - Completed sales orders (status = 10)
  - Approved transactions
  - Commission withdrawal histories

### Twilio Integration

- **Purpose**: Send SMS/WhatsApp notifications to drivers and sellers
- **Configuration**: Stored in Settings table
  - `twilioAccountSid`
  - `twilioAuthToken`
  - `twilioUrl`
  - `twilioFromNumber`
  - `twilloTemplateUrl`
- **Usage**:
  - Order status change notifications
  - Driver allocation notifications
  - Order reminders
  - Custom template-based messages

### Real-Time Broadcasting

- **Technology**: Laravel Reverb / Pusher
- **Channel**: `card-trigger`
- **Events**:
  - `order-status-change` - Order status updates
  - `add-task-to-order` - Task assignments
  - `order-allocation-info` - Driver allocation updates

## Setup & Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL/MariaDB/PostgreSQL or SQLite
- Web server (Apache/Nginx)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd invertor-crm-master
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   - Update `.env` with your database credentials
   - Run migrations:
     ```bash
     php artisan migrate
     ```

6. **Seed database** (optional)
   ```bash
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

8. **Set storage permissions**
   ```bash
   php artisan storage:link
   # Ensure storage/ and bootstrap/cache/ are writable
   ```

9. **Configure queue worker** (for background jobs)
   ```bash
   php artisan queue:work
   ```

10. **Set up cron jobs** (see [Cron Jobs](#cron-jobs--background-tasks) section)

## Environment Variables

Create a `.env` file in the root directory with the following variables:

```env
# Application
APP_NAME="Invertor CRM"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Europe/London
APP_URL=http://localhost
APP_LOCALE=en

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invertor_crm
DB_USERNAME=root
DB_PASSWORD=

# Broadcasting (Reverb/Pusher)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Or for Pusher:
# BROADCAST_CONNECTION=pusher
# PUSHER_APP_ID=
# PUSHER_APP_KEY=
# PUSHER_APP_SECRET=
# PUSHER_APP_CLUSTER=mt1

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=file

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail (optional)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Google Sheets (configured via Settings table in admin panel)
# GOOGLE_CLIENT_ID=
# GOOGLE_CLIENT_SECRET=
# GOOGLE_REDIRECT=
# GOOGLE_DEVELOPER_KEY=
# GOOGLE_SERVICE_ENABLED=true

# Redis (optional, for cache/sessions)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Note**: Twilio credentials and Google Sheets configuration are stored in the database (`settings` table) and can be configured via the admin settings page.

## Running the Project

### Local Development

1. **Start the development server**
   ```bash
   php artisan serve
   ```
   Access at: `http://localhost:8000`

2. **Start Vite dev server** (for frontend hot-reload)
   ```bash
   npm run dev
   ```

3. **Start Reverb server** (for WebSocket support)
   ```bash
   php artisan reverb:start
   ```

4. **Start queue worker**
   ```bash
   php artisan queue:work
   ```

### Production

1. **Optimize application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

2. **Build frontend assets**
   ```bash
   npm run build
   ```

3. **Set up web server** (Apache/Nginx)
   - Point document root to `public/` directory
   - Configure URL rewriting

4. **Set up supervisor** (for queue workers)
   - Configure supervisor to run `php artisan queue:work`

5. **Set up cron jobs** (see next section)

## Cron Jobs / Background Tasks

The application uses Laravel's task scheduler. Add the following to your crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

All scheduled commands are defined in `routes/console.php`:

1. **`status:trigger`** - Runs every minute
   - Executes automated order status changes
   - Handles status transition triggers
   - Allocates drivers when order moves to "New" status
   - Sends notifications

2. **`task:trigger`** - Runs every minute
   - Executes automated task assignments
   - Adds tasks to orders based on triggers

3. **`change_user:trigger`** - Runs every minute
   - Executes automated user assignment changes
   - Changes responsible user for orders

4. **`add:data-to-sheet`** - Runs every 5 minutes
   - Synchronizes completed sales orders to Google Sheets
   - Synchronizes approved transactions
   - Synchronizes commission withdrawal histories

### Manual Command Execution

You can also run commands manually:

```bash
php artisan status:trigger
php artisan task:trigger
php artisan change_user:trigger
php artisan add:data-to-sheet
```

## Common Use Cases

### Creating a Sales Order

1. Navigate to Sales Orders → Create
2. Select seller, customer details, delivery address
3. Add products with quantities
4. System validates availability and prices
5. Save order (may require confirmation)
6. Order moves to "New" status and drivers are allocated

### Managing Order Status

1. Navigate to Sales Order Status
2. Configure status sequence and automation rules
3. Set up triggers for automatic status changes
4. Configure tasks to be added at specific statuses
5. Orders automatically transition based on triggers

### Driver Allocation

1. When order reaches "New" status, system calculates nearby drivers
2. Drivers receive notifications (in-app and SMS/WhatsApp)
3. Drivers can accept/reject orders
4. Admin can manually reassign drivers if needed

### Commission Withdrawal

1. Seller/Driver navigates to Financial Report
2. Adds bank account details (with IBAN validation)
3. Creates withdrawal request
4. Admin reviews and approves/rejects
5. Transaction recorded and synced to Google Sheets

### Stock Management

1. Purchase orders create stock entries in storage
2. Distribution orders move stock from storage to drivers
3. Sales orders consume stock from drivers
4. Stock reports show available inventory by location

## Known Limitations

Based on code analysis:

1. **Geographic Allocation**: Driver allocation relies on accurate lat/long coordinates. Inaccurate coordinates may result in no driver allocation.

2. **Google Sheets Sync**: Requires valid Google service account credentials and sheet ID. Sync fails silently if credentials are invalid.

3. **Twilio Notifications**: Requires valid Twilio credentials. Messages may fail if account has insufficient credits or invalid phone numbers.

4. **Real-time Updates**: Requires WebSocket server (Reverb/Pusher) to be running. Falls back to polling if WebSocket connection fails.

5. **Status Triggers**: Time-based triggers use server time. Ensure server timezone is correctly configured.

6. **Permission System**: Permission checking requires both role-level and user-level permissions to be granted (AND logic). Admin role (ID: 1) bypasses all checks.

7. **Soft Deletes**: Many models use soft deletes. Deleted records are hidden but not permanently removed.

8. **Database**: Default connection is SQLite for development. Production should use MySQL/PostgreSQL.

## Future Improvements

Based on code structure and patterns:

1. **API Development**: Create RESTful API endpoints for mobile applications
2. **Multi-language Support**: Add i18n support (currently hardcoded English/Russian)
3. **Advanced Reporting**: Add more comprehensive analytics and reporting dashboards
4. **Email Notifications**: Enhance email notification system (currently uses log driver)
5. **Testing**: Add comprehensive test coverage (currently minimal)
6. **Documentation**: API documentation using tools like Swagger/OpenAPI
7. **Performance Optimization**: Implement caching strategies, database indexing optimization
8. **Mobile App**: Develop companion mobile app for drivers
9. **Payment Gateway Integration**: Add payment processing for orders
10. **Advanced Search**: Implement full-text search for orders and products
11. **Audit Logging**: Comprehensive audit trail for all operations
12. **Export Functionality**: PDF/Excel export for reports

## Contribution Guidelines

1. **Code Style**: Follow PSR-12 coding standards
2. **Laravel Conventions**: Adhere to Laravel best practices
3. **Database Migrations**: Always create migrations for schema changes
4. **Testing**: Write tests for new features
5. **Documentation**: Update README and code comments for new features
6. **Pull Requests**: 
   - Create feature branches from `main`
   - Provide clear description of changes
   - Ensure all tests pass
   - Update documentation as needed

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the `composer.json` file for details.

---

**Note**: This README is generated based on codebase analysis. For specific implementation details, refer to the source code and inline documentation.
