# 🍽️ The HOUSE — Restaurant Reservation System

A full-stack web application for managing restaurant reservations, built with **PHP** and **MySQL**.

---

## 📁 Project Structure

```
restaurant_reservation/
├── init.sql                  ← Database schema + seed data
├── index.php                 ← Entry point (redirects based on role)
├── config/
│   └── db.php                ← DB connection + session helpers
├── auth/
│   ├── login.php             ← Authentication page
│   └── logout.php            ← Session destroy
├── includes/
│   ├── header.php            ← Shared HTML/CSS header + nav
│   └── footer.php            ← Shared footer
├── admin/
│   ├── dashboard.php         ← Stats & aggregations
│   ├── reservations.php      ← Full CRUD + pagination + filters
│   ├── tables.php            ← Table management
│   └── users.php             ← User management
└── user/
    ├── dashboard.php         ← Personal stats + upcoming booking
    ├── reserve.php           ← New reservation form
    ├── my_reservations.php   ← Paginated personal reservation list
    └── check_availability.php ← AJAX availability endpoint
```

---

## ⚙️ Requirements

| Requirement | Version |
|---|---|
| PHP | 7.4+ (8.x recommended) |
| MySQL | 5.7+ or MariaDB 10.3+ |
| Web server | Apache / Nginx (or PHP built-in server) |

> PHP extensions needed: `pdo`, `pdo_mysql`

---

## 🚀 Setup Instructions

### 1. Clone / copy the project

```bash
git clone https://github.com/your-username/restaurant-reservation.git
cd restaurant-reservation
```

### 2. Import the database

```bash
mysql -u root -p < init.sql
```

Or using a GUI (phpMyAdmin, TablePlus, DBeaver):
- Create a connection to your MySQL server
- Run the contents of `init.sql`

### 3. Configure the database connection

Edit **`config/db.php`** and update your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // ← your MySQL username
define('DB_PASS', '');          // ← your MySQL password
define('DB_NAME', 'restaurant_reservation');
```

### 4. Start the development server

Using PHP's built-in server from the project root:

```bash
php -S localhost:8000
```

Then open: **http://localhost:8000**

> For Apache/Nginx, point your virtual host document root to the project folder.

---

## 🔐 Default Credentials

| Role  | Email                    | Password    |
|-------|--------------------------|-------------|
| Admin | admin@restaurant.com     | Admin@1234  |
| User  | john@example.com         | User@1234   |

> Passwords are stored as **bcrypt hashes** (cost factor 10). Never store plain-text passwords.

---

## 🗄️ Database Schema

### Tables

#### `users`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | Auto-increment |
| name | VARCHAR(100) | NOT NULL |
| email | VARCHAR(150) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | bcrypt hash |
| role | ENUM('admin','user') | Default: 'user' |
| created_at | TIMESTAMP | Auto |

#### `tables`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| tablenumber | VARCHAR(10) | UNIQUE |
| capacity | TINYINT | CHECK > 0 |
| location | VARCHAR(80) | e.g. Main Hall, Terrace |
| isactive | TINYINT(1) | Soft-disable flag |

#### `reservations`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| user_id | INT UNSIGNED FK | → users.id |
| table_id | INT UNSIGNED FK | → tables.id |
| reservation_date | DATE | |
| reservation_time | TIME | |
| party_size | TINYINT | CHECK > 0 |
| status | ENUM | pending/confirmed/cancelled/completed |
| notes | TEXT | Optional |
| created_at | TIMESTAMP | Auto |

**Unique constraint:** `(table_id, reservation_date, reservation_time)` — prevents double-booking.

#### `roles`
Reference table documenting the two system roles (admin, user).

---

## ✨ Features

### Admin
- **Dashboard** — live statistics (COUNT, SUM, AVG, GROUP BY)
  - Total reservations, today's bookings, average party size
  - Reservations per status with percentage breakdown
  - Top 5 most-booked tables
  - Upcoming 7-day reservation overview
- **Reservations** — full CRUD, status filter, date filter, pagination (10/page), inline status quick-update
- **Tables** — add/edit/disable dining tables, see booking counts per table
- **Users** — add/edit/delete users, assign roles, see per-user reservation stats

### User
- **Dashboard** — personal stats + next upcoming reservation highlight
- **New Reservation** — table selector with real-time availability check (AJAX), time slots, capacity validation
- **My Reservations** — paginated list (8/page), filter by status, cancel future reservations

### Security
- Passwords hashed with **bcrypt** (`PASSWORD_BCRYPT`)
- Session regeneration on login (`session_regenerate_id`)
- All user input sanitised via `htmlspecialchars` before output
- PDO **prepared statements** everywhere (SQL injection prevention)
- Role-based route guards (`requireAdmin()`, `requireLogin()`)

---

## 📊 SQL Aggregation Examples

```sql
-- Average party size and total guests
SELECT AVG(party_size), SUM(party_size) FROM reservations;

-- Reservations grouped by status
SELECT status, COUNT(*) AS cnt FROM reservations GROUP BY status;

-- Most booked tables
SELECT dt.tablenumber, COUNT(r.id) AS bookings
FROM tables dt
LEFT JOIN reservations r ON r.table_id = dt.id
GROUP BY dt.id
ORDER BY bookings DESC;
```

---

## 🧑‍💻 Authors

- Group member 1
- Group member 2
- Group member 3

---

## 📄 License

MIT — free to use for academic purposes.
