# Tuition Finder

A small full-stack web application where guardians and students post the tuitions
they need, and tutors browse, search and filter those posts and contact the poster.

Built for **CSE 471 – Web and Internet Programming** (group project).

**Live site:** https://tuition.free.je

**Repository:** _add your GitHub URL here_

---

## 1. Features

| Requirement | Where it is implemented |
|---|---|
| Registration, login, logout | `register.php`, `login.php`, `logout.php` |
| Passwords stored hashed | `password_hash()` in `register.php`, `password_verify()` in `login.php` |
| Create / Read / Update / Delete | `post_create.php`, `browse.php` + `post_details.php`, `post_edit.php`, `post_delete.php` |
| Search and filter | `browse.php` – keyword search plus class, subject, medium and max-salary filters |
| Session-based access control | `require_login()` in `includes/functions.php`; ownership checks in edit/delete |
| Admin dashboard (extra feature) | `admin_dashboard.php`, `admin_users.php`, `admin_posts.php`, guarded by `require_admin()` |
| Input validation | Client side in `assets/js/validation.js`, server side in every PHP page |
| SQL injection prevention | Prepared statements (MySQLi `prepare` / `bind_param`) everywhere |
| XSS prevention | All output escaped through the `e()` helper (`htmlspecialchars`) |
| CSRF protection | Token generated in `csrf_token()`, verified by `verify_csrf()` on every POST |

### Pages

1. **Home** (`index.php`) – landing page: hero with a quick search box, live statistics,
   popular subject shortcuts, the latest tuitions, how it works, features and a call to action
2. **Register** (`register.php`) – create an account as a guardian or a tutor
3. **Login** (`login.php`) – start a session
4. **Browse Tuitions** (`browse.php`) – search and filter the whole board
5. **Tuition Details** (`post_details.php`) – full post; contact info shown to logged-in users only
6. **My Posts** (`my_posts.php`) – dashboard with the user's own posts, Edit and Delete
7. **Post / Edit a Tuition** (`post_create.php`, `post_edit.php`) – the create and update forms

### Admin area (extra feature)

Three more pages are visible only to a user whose role is `admin`. The **Admin** link
appears in the navigation bar automatically for that user.

| Page | What it does |
|---|---|
| `admin_dashboard.php` | Statistics: users by role, posts by status, areas covered, average salary, a bar chart of the most requested subjects and the five latest posts |
| `admin_users.php` | Every registered user with a post count, a search box, and delete (a deleted user's posts are removed by the `ON DELETE CASCADE` foreign key) |
| `admin_posts.php` | Every post on the site with keyword and status filters; the admin can close, reopen or delete any post regardless of who created it |

Two rules protect the admin role:

- The registration form only offers `guardian` and `tutor`, and the server checks the
  submitted role against that list, so nobody can register as an admin.
- `admin_users.php` refuses to delete any account whose role is `admin`, including its own.

---

## 2. Technology

- **Frontend:** HTML5, hand-written CSS (no framework), vanilla JavaScript
- **Backend:** PHP 8 (procedural, MySQLi with prepared statements)
- **Database:** MySQL 8 / MariaDB 10
- **Server:** Apache (XAMPP locally, shared hosting in production)

---

## 3. Project structure

```
tuition-finder/
├── index.php                 # Home page
├── register.php              # Registration
├── login.php                 # Login
├── logout.php                # Logout
├── browse.php                # Search + filter listing
├── post_details.php          # Single post view
├── my_posts.php              # Owner dashboard
├── post_create.php           # Create a post
├── post_edit.php             # Update a post
├── post_delete.php           # Delete a post (POST only)
├── admin_dashboard.php       # Admin: statistics
├── admin_users.php           # Admin: manage users
├── admin_posts.php           # Admin: moderate posts
├── config/
│   └── db.php                # Database credentials + connection
├── includes/
│   ├── functions.php         # Session, auth guard, CSRF, escaping, validation
│   ├── header.php            # Shared header and navigation
│   ├── footer.php            # Shared footer
│   └── post_form_fields.php  # Fields shared by the create and edit forms
├── assets/
│   ├── css/style.css
│   ├── js/validation.js      # Client-side form validation
│   └── js/main.js            # Mobile menu, flash message auto-hide
└── database/
    └── schema.sql            # Database schema + sample data
```

---

## 4. Database design

**users**

| Column | Type | Notes |
|---|---|---|
| user_id | INT | Primary key, auto increment |
| full_name | VARCHAR(100) | |
| email | VARCHAR(150) | Unique |
| phone | VARCHAR(20) | 11-digit BD mobile number |
| role | ENUM('guardian','tutor','admin') | `admin` can only be set directly in SQL |
| password_hash | VARCHAR(255) | bcrypt hash, never the plain password |
| created_at | TIMESTAMP | |

**tuition_posts**

| Column | Type | Notes |
|---|---|---|
| post_id | INT | Primary key, auto increment |
| posted_by | INT | **Foreign key** → `users(user_id)`, `ON DELETE CASCADE` |
| title | VARCHAR(150) | |
| class_level | VARCHAR(50) | |
| subject | VARCHAR(100) | Indexed |
| area | VARCHAR(100) | Indexed |
| salary | INT | BDT per month |
| days_per_week | TINYINT | 1–7 |
| medium | ENUM('Bangla','English','Both') | |
| description | TEXT | Optional |
| status | ENUM('open','closed') | |
| created_at | TIMESTAMP | |

Relationship: **one user → many tuition posts** (1:N).

---

## 5. Local setup (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this folder into `C:\xampp\htdocs\` so the path is `C:\xampp\htdocs\tuition-finder`.
3. Open <http://localhost/phpmyadmin>, go to the **SQL** tab, paste the whole content of
   `database/schema.sql` and run it. This creates the database, both tables and the sample data.
4. Open `config/db.php` and check the credentials (XAMPP defaults are already set:
   host `localhost`, user `root`, empty password, database `tuition_finder`).
5. Visit <http://localhost/tuition-finder/>.

### Demo accounts

| Email | Password | Role |
|---|---|---|
| guardian@demo.com | `Pass@123` | Guardian (owns the sample posts) |
| tutor@demo.com | `Pass@123` | Tutor |
| admin@demo.com | `Pass@123` | Admin (can open the admin dashboard) |

---

## 6. Deploying to InfinityFree (free PHP + MySQL hosting)

This project is deployed at <https://tuition.free.je>. To repeat the deployment:

1. Create a free account at <https://infinityfree.com> and add a hosting account.
2. In the control panel open **MySQL Databases** and create one. The panel then shows the
   database name (`if0_xxxxxxxx_name`), the username, the host (`sqlNNN.infinityfree.com`)
   and states that the password is your vPanel password.
3. Open **phpMyAdmin** from the panel, select the database, and import
   `database/schema_infinityfree.sql` — this is the same schema without the
   `CREATE DATABASE` and `USE` statements, which InfinityFree does not allow.
4. Edit `config/db.php` with the four values from step 2. `DB_HOST` must be the
   `sqlNNN.infinityfree.com` value, **not** `localhost`.
5. Upload every file to the `htdocs` folder using the **File Manager** or FTP (FileZilla).
6. Open the site and test registration, login and posting.

> Keep the hosting credentials out of version control: commit `config/db.php` with the
> local XAMPP defaults and set the real values only on the server.

> GitHub Pages and Netlify only serve static files — they cannot run PHP or MySQL,
> so they will not work for this project.

---

## 7. Security notes

- **SQL injection:** every query that uses user input goes through
  `prepare()` + `bind_param()`. The dynamic filter query in `browse.php` also builds
  its WHERE clause with placeholders, never by concatenating the values.
- **XSS:** no user value is printed directly; everything goes through `e()`
  (`htmlspecialchars` with `ENT_QUOTES`).
- **CSRF:** every POST form carries a random token that is compared with the session
  token using `hash_equals()`. A request without a valid token gets HTTP 403.
- **Password storage:** `password_hash()` with the default bcrypt algorithm; the plain
  password is never stored or logged.
- **Session fixation:** `session_regenerate_id(true)` is called right after a successful login.
- **Broken access control:** `post_edit.php` and `post_delete.php` check that the post
  belongs to the logged-in user, and `posted_by` is also part of the UPDATE/DELETE
  `WHERE` clause as a second layer.
- **Deletion by link:** `post_delete.php` refuses anything that is not a POST request.

---

## 8. Test cases

| # | Test | Input | Expected result | Status |
|---|---|---|---|---|
| 1 | Valid registration | Name, valid email, 01712345678, Test1234 | Account created, redirect to login, password stored as bcrypt hash | Pass |
| 2 | Invalid registration | Name "Ab", email "bad", phone "123", mismatched passwords | Form redisplayed with one error message per field, nothing inserted | Pass |
| 3 | Login with wrong password | Correct email, wrong password | "Invalid email or password", no session started | Pass |
| 4 | Access control | Guest opens `my_posts.php` | Redirected to `login.php?msg=login_required` | Pass |
| 5 | Create post | Valid tuition data | Row inserted, redirected to My Posts with a success message | Pass |
| 6 | Update post | Change salary to 9000 and status to closed | Row updated in the database | Pass |
| 7 | Delete post | Owner clicks Delete and confirms | Row removed, success message shown | Pass |
| 8 | Ownership check | User B opens `post_edit.php?id=<A's post>` | HTTP 403, and a forged delete leaves the row untouched | Pass |
| 9 | Search / filter | `subject=Physics`, `max_salary=4500`, combined filters | Only matching posts are listed, count line updates | Pass |
| 10 | SQL injection | Keyword `' OR '1'='1` | Treated as plain text, 0 results, no database error | Pass |
| 11 | XSS | Title `<script>alert(1)</script> tutor` | Rendered escaped as text, the script never executes | Pass |
| 12 | CSRF | POST to `post_create.php` without a token | HTTP 403 "Invalid request" | Pass |
| 13 | Privacy | Guest opens a post detail page | Phone and email hidden, prompt to log in | Pass |
| 14 | Logout | Click Logout, then open `my_posts.php` | Session destroyed, redirected to login | Pass |
| 15 | Admin guard | Guardian opens `admin_dashboard.php`, `admin_users.php`, `admin_posts.php` | HTTP 403 on all three; a guest is redirected to login | Pass |
| 16 | Privilege escalation | Registration posted with `role=admin` | Rejected by the server's role whitelist, account not created as admin | Pass |
| 17 | Admin moderation | Admin closes and reopens a post owned by another user | Status changes in the database | Pass |
| 18 | Cascade delete | Admin deletes a user who has posts | User and all of their posts removed together | Pass |
| 19 | Admin self-protection | Admin submits a delete for an admin account | Refused with an error message, account still present | Pass |

---

## 9. Group members

| Name | Student ID | Responsibility |
|---|---|---|
| _(name)_ | _(id)_ | Frontend (HTML/CSS, layout) |
| _(name)_ | _(id)_ | Authentication and security |
| _(name)_ | _(id)_ | CRUD pages |
| _(name)_ | _(id)_ | Database design and hosting |
| _(name)_ | _(id)_ | Testing and report |

---

## 10. References

- PHP Manual (2026) *password_hash*. Available at: https://www.php.net/manual/en/function.password-hash.php
- PHP Manual (2026) *mysqli::prepare*. Available at: https://www.php.net/manual/en/mysqli.prepare.php
- OWASP (2026) *Cheat Sheet Series*. Available at: https://cheatsheetseries.owasp.org/
