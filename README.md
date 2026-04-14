# The HOUSE — Restaurant Reservation System

A full-stack web application for managing restaurant reservations, built with **PHP** and **MySQL**.

#  Requirements

 PHP 
 MySQL 
 Web server

> PHP extensions needed: `pdo`, `pdo_mysql`

---



#. Import the database

```bash
mysql -u root -p < init.sql
```

Or using a GUI (phpMyAdmin, TablePlus, DBeaver):
- Create a connection to your MySQL server
- Run the contents of `init.sql`

#. Configure the database connection

Edit **`config/db.php`** and update your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      //  your MySQL username
define('DB_PASS', '');          //  your MySQL password
define('DB_NAME', 'restaurant_reservation');
```



# Default Credentials

| Role  | Email                    | Password    |
|-------|--------------------------|-------------|
| Admin | admin@restaurant.com     | Admin@1234  |
| User  | john@example.com         | User@1234   |

> Passwords are stored as **bcrypt hashes** (cost factor 10). Never store plain-text passwords. (IA)

---

# Database Schema

# Tables



# `tables`


# `reservations`


**Unique constraint:** `(table_id, reservation_date, reservation_time)` — prevents double-booking.

# `roles`
Reference table documenting the two system roles (admin, user).

---

##  Features

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

## SQL Aggregation Examples

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

## Authors
-Ngoy Victor Jeff
-Leroy Malachie
-Carel Lekane


