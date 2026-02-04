<div align="center">

  <h1>🎓 University Department & Faculty Manager</h1>
  
  <p>
    <strong>A robust, role-based academic administration ecosystem built for scale.</strong>
  </p>

  <p>
    <a href="#key-features">Key Features</a> •
    <a href="#technical-architecture">Architecture</a> •
    <a href="#installation">Installation</a> •
    <a href="#database-logic">Database Logic</a>
  </p>

  <p>
    <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
    <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
    <img src="https://img.shields.io/badge/Security-RBAC-green?style=for-the-badge&logo=security&logoColor=white" alt="Security" />
  </p>
</div>

<br />

> **Project Overview:** > This system is not just a CRUD application; it is a **dynamic administrative solution** designed to handle complex university hierarchies. It features an intelligent **"Single-Focal-Person"** logic engine, automated database triggers for role management, and a secure, permission-based dashboard for faculty members.

---

## 🚀 Key Features

### 👑 The "Focal Person" Intelligence
Unlike standard systems, this application enforces strict academic rules via Database Triggers:
* **Conflict-Free Assignment:** The system guarantees **only one Focal Person per department**. 
* **Auto-Demotion Logic:** Assigning a new focal person automatically revokes rights from the previous holder instantly.
* **Privileged Dashboard:** Focal Persons gain exclusive access to manage **News**, **Notices**, **Events**, and **Notifications**, while normal faculty see a restricted view.

### 🛡️ Admin Command Center
* **Department Orchestration:** Create, edit, and restructure university departments dynamically.
* **Faculty Onboarding:** Add faculty members with rich profiles (Bios, Hire Dates) and assign departmental roles.
* **Role-Based Access Control (RBAC):** Granular permission management via the `user_rights` attribute.

### 🌐 Dynamic Frontend Experience
* **Auto-Populated Navigation:** The Navbar fetches active departments from the database in real-time.
* **Context-Aware Pages:** `dpt.php` uses GET requests (`dpt.php?id=CS`) to render department-specific data, faculty lists, and announcements dynamically.
* **Responsive UI:** Built on **Bootstrap 5.3** for a seamless experience across Mobile, Tablet, and Desktop.

---

## 🏗️ Technical Architecture

### 🛠 Tech Stack
| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | Core PHP (7.4+) | Server-side logic, Session Management, Input Sanitization |
| **Database** | MySQL (5.7+) | Relational Schema, Triggers, Stored Procedures |
| **Frontend** | HTML5, CSS3, JS | Bootstrap 5 for grid system and responsive components |
| **Security** | `password_hash()` | Bcrypt encryption for secure authentication |

### 🗄️ Database Schema & Triggers
The system relies on a relational schema connecting `admin`, `departments`, `faculty`, and `students`.

**The "Smart Trigger" Logic:**
The system uses a conceptual trigger logic to ensure integrity:
```sql
-- Logic implemented to ensure single focal person
IF (NEW.is_focal_person = 1) THEN
    UPDATE faculty SET user_rights = 'normal', is_focal_person = 0 
    WHERE department_id = NEW.department_id;
    SET NEW.user_rights = 'focal_person';
END IF;
📂 File Structure
Plaintext
university/
├── 📂 database/
│   └── database_schema.sql      # Contains Table Structures & Triggers
├── 📂 includes/
│   └── db_connect.php           # Singleton Database Connection
├── 📂 admin/
│   ├── admin_dashboard.php      # Analytics & Quick Actions
│   ├── admin_departments.php    # Department CRUD
│   └── admin_faculty.php        # Faculty Logic & Focal Person Assignment
├── 📂 dashboards/
│   └── focal_dashboard.php      # Exclusive Control Panel for Focal Persons
├── index.php                    # Dynamic Homepage
├── dpt.php                      # Department-Specific Dynamic Page (GET Method)
├── login.php                    # Secure RBAC Login
└── README.md                    # Documentation
⚙️ Installation & Setup
Clone the Repository

Bash
git clone [https://github.com/aliza-dev/department-faculty-system.git](https://github.com/aliza-dev/department-faculty-system.git)
Database Configuration

Open phpMyAdmin and create a database named university_db.

Import database_schema.sql into the database.

Note: Ensure triggers are imported correctly.

Connect Application

Open db_connect.php and update credentials:

PHP
$conn = new mysqli('localhost', 'root', '', 'university_db');

Admin Access
URL: http://localhost/university/login.php

🔒 Security Measures
SQL Injection Protection: All database queries use Prepared Statements.

XSS Prevention: Output is sanitized using htmlspecialchars().

Session Hijacking: Secure session handling ensures unauthorized users cannot access dashboards.

Password Security: Industry-standard hashing algorithms (Bcrypt).

<div align="center"> <p>Built with ❤️ for Modern Academia</p> <p> <a href="https://github.com/aliza-dev">GitHub</a> • <a href="https://www.linkedin.com/in/aliza-tariq-dev/">LinkedIn</a> </p> </div> 
