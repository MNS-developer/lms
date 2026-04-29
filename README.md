# 🎓 Learnix — Smart Learning Management System

> A modern, feature-rich Learning Management System built with **PHP + MySQL**, featuring a stunning **Liquid Glass UI** design system, role-based access control, and complete academic management workflows.

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-00e5ff)
![Design](https://img.shields.io/badge/UI-Liquid%20Glass-7b5ea7)

</div>

---

## ✨ What's New (v2.0 — Redesign)

- 🌊 **Liquid Glass UI** — Aurora-animated login, frosted glass cards, glowing sidebars
- 🎨 **Modern Design System** — Deep Space Navy + Electric Cyan color palette with `Syne` + `DM Sans` fonts
- 📢 **Announcements** — Admin can post announcements to students, faculty, or everyone
- ⏰ **Upcoming Deadlines** — Students see deadline alerts on their dashboard
- 📊 **Enhanced Dashboards** — Stats cards, recent activity, rich tables for all roles
- 🌑 **Dark Mode Native** — Entire UI is built dark-first
- 📱 **Responsive** — Works on mobile and tablet
- 📅 **Attendance Summary for Faculty** — After selecting a course, faculty see a table of all previously marked dates with present/absent/total counts, a status badge (Selected / Marked), and an Edit button to jump directly to any past date for corrections

---

## 🚀 Features at a Glance

| Role | Features |
|------|----------|
| **🔧 Admin** | Manage students, faculty, courses, enrollments, timetable, announcements |
| **👨‍🏫 Faculty** | Mark attendance, upload materials, create assessments, grade submissions |
| **🎓 Student** | Dashboard with deadlines, view attendance, submit assignments, download materials, transcript, roll slip |

### Core Modules
- 📚 **Course Management** — Create/assign courses with credits, faculty, descriptions
- 📋 **Enrollment Workflow** — Student applies → Admin approves/rejects
- 📊 **Attendance Tracking** — Faculty marks daily attendance; a summary table shows all marked dates with present/absent/total counts and an Edit button to revise any past session; students see percentage + chart
- 📝 **Assessments & Submissions** — Assignments/quizzes with deadlines, file uploads, remarks
- ⭐ **Grading** — Faculty grades submissions with marks and detailed feedback
- 📅 **Timetable** — Weekly schedule with day/time/room for each course
- 📄 **Transcript** — Auto-generated academic transcript per student
- 🪪 **Roll Slip** — Printable roll number slip
- 📢 **Announcements** — Targeted notices (all/students/faculty)
- 👤 **Student Profile** — Update personal info and password

---

## 🖥️ Requirements

| Requirement | Version |
|-------------|---------|
| **PHP** | 7.4+ / 8.x |
| **MySQL** | 5.7+ / 8.0 / MariaDB 10.3+ |
| **Apache** | 2.4+ |
| **Browser** | Chrome, Firefox, Edge (modern) |

**Recommended:** [XAMPP](https://www.apachefriends.org/) — bundles Apache + MySQL + PHP.

---

## ⚙️ Installation (XAMPP)

### Step 1 — Place Project Files

```
C:\xampp\htdocs\lms\        ← Windows
/opt/lampp/htdocs/lms/      ← Linux
~/Applications/.../htdocs/lms/   ← macOS
```

### Step 2 — Start XAMPP Services

Open **XAMPP Control Panel** and start:
- ✅ **Apache**
- ✅ **MySQL**

### Step 3 — Create the Database

1. Go to `http://localhost/phpmyadmin`
2. Click **New** → name it `student_management_system` → **Create**
3. Select the new database → click **Import**
4. Choose `lms/database.sql` → click **Go**

### Step 4 — Configure (if needed)

Edit `config.php` — defaults work with standard XAMPP:

```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';           // XAMPP default: empty
$db_name = 'student_management_system';
```

### Step 5 — Fix Upload Permissions

**Windows:** Right-click `lms/uploads/` → Properties → Security → give Write permission.

**Linux/macOS:**
```bash
chmod -R 775 lms/uploads/
```

### Step 6 — Launch

```
http://localhost/lms
```

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@lms.com | admin |

> 💡 More sample accounts are seeded by `database.sql`.

---

## 📁 Project Structure

```
lms/
├── config.php              # DB connection, session, helpers
├── index.php               # Login page (all roles, liquid glass UI)
├── logout.php              # Session destroy
├── database.sql            # Full schema + seed data
├── download.php            # Secure file download handler
├── init_db.php             # Alternative DB initializer
│
├── css/
│   └── style.css           # Global design system (liquid glass)
│
├── admin/
│   ├── dashboard.php       # Stats overview, pending approvals
│   ├── students.php        # Add/edit/delete students
│   ├── faculty.php         # Add/edit/delete faculty
│   ├── courses.php         # Course management
│   ├── enrollments.php     # Approve/reject enrollments
│   ├── timetable.php       # Weekly schedule management
│   ├── announcements.php   # Post/manage announcements ← NEW
│   ├── header.php          # Navbar + sidebar (admin)
│   └── footer.php          # Footer bar
│
├── faculty/
│   ├── dashboard.php       # Course overview, ungraded submissions
│   ├── courses.php         # View enrolled students per course
│   ├── materials.php       # Upload study materials
│   ├── assessments.php     # Create assignments/quizzes
│   ├── submissions.php     # View and grade submissions
│   ├── attendance.php      # Mark daily attendance; shows marked-dates summary + status badge
│   ├── grades.php          # Grade summary view
│   ├── header.php          # Navbar + sidebar (faculty)
│   └── footer.php
│
├── student/
│   ├── dashboard.php       # Stats + upcoming deadlines ← ENHANCED
│   ├── enrollments.php     # View enrollment status
│   ├── register.php        # Apply for courses
│   ├── attendance_view.php # Attendance chart + breakdown
│   ├── assessments_view.php# View/submit assessments
│   ├── materials_view.php  # Download course materials
│   ├── timetable.php       # Weekly class schedule
│   ├── transcript.php      # Academic transcript
│   ├── rollslip.php        # Printable roll slip
│   ├── profile.php         # Update profile/password
│   ├── header.php          # Navbar + sidebar (student)
│   └── footer.php
│
└── uploads/
    ├── materials/          # Faculty-uploaded course files
    └── submissions/        # Student-submitted assignment files
```

---

## 🎨 Design System

Learnix v2.0 uses a custom **Liquid Glass** design system:

### Color Palette
| Variable | Color | Use |
|----------|-------|-----|
| `--bg-base` | `#04070f` | Page background |
| `--accent-cyan` | `#00e5ff` | Primary accent, links |
| `--accent-green` | `#00e676` | Success states |
| `--accent-rose` | `#ff4d8d` | Danger/logout |
| `--accent-amber` | `#ffb347` | Warnings, deadlines |
| `--accent-violet` | `#7b5ea7` | Badges, secondary |

### Typography
- **Display/Headings:** Syne (700–800 weight)
- **Body:** DM Sans (400–600 weight)

### Glass Effect
```css
background: rgba(13,25,50,0.55);
backdrop-filter: blur(24px);
border: 1px solid rgba(0,229,255,0.12);
box-shadow: 0 8px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
```

---

## ⚠️ Security Notes

> This project is designed for **local / academic use**. Before any public deployment:

| Issue | Fix |
|-------|-----|
| MD5 passwords | Replace with `password_hash()` / `password_verify()` |
| SQL injection risk | Migrate to **prepared statements** (PDO) |
| No CSRF protection | Add CSRF tokens to all forms |
| Direct upload access | Add `.htaccess` to restrict `uploads/` directory |
| HTTP only | Enable HTTPS with SSL certificate |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x |
| Database | MySQL 8.0 / MariaDB |
| Frontend | HTML5, CSS3 (custom), Vanilla JS |
| Charts | Chart.js (CDN) |
| Fonts | Google Fonts (Syne, DM Sans) |
| Dev Server | XAMPP (Apache + MySQL) |

---

## 📄 License

MIT — Free to use and modify for academic and personal projects.

---

## 👨‍💻 Built for:

Learnix was built for eduational purpose.
