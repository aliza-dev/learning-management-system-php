<div align="center">

  <h1>🏛️ University Resource Management Ecosystem</h1>
  
  <p>
    <strong>A Logic-Driven LMS, Smart Booking & Faculty Management System</strong>
    <br />
    <em>Developed under the mentorship of Sir Ali Bashir</em>
  </p>

  <p>
    <a href="#key-features">Key Features</a> •
    <a href="#smart-booking-logic">Booking Engine</a> •
    <a href="#technical-architecture">Architecture</a> •
    <a href="#installation">How to Run</a>
  </p>

  <p>
    <img src="[https://img.shields.io/badge/PHP-Core-777BB4?style=for-the-badge&logo=php&logoColor=white](https://img.shields.io/badge/PHP-Core-777BB4?style=for-the-badge&logo=php&logoColor=white)" />
    <img src="[https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)" />
    <img src="[https://img.shields.io/badge/FrontEnd-Tailwind%20%26%20JS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white](https://img.shields.io/badge/FrontEnd-Tailwind%20%26%20JS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)" />
    <img src="[https://img.shields.io/badge/FPDF-Library-FF0000?style=for-the-badge&logo=adobe-acrobat-reader&logoColor=white](https://img.shields.io/badge/FPDF-Library-FF0000?style=for-the-badge&logo=adobe-acrobat-reader&logoColor=white)" />
  </p>
  
  <br />

</div>

> **🚀 Why this project is different?**
> This isn't just a CRUD app. It is a connected **Campus Ecosystem** where **Students**, **Faculty**, and **Admins** operate with real permissions. It moves beyond basic features to handle **Concurrency**, **Database Triggers**, and **Smart Resource Allocation**.

---

## 🔥 The "Wow" Factor: Smart Booking & Logic

### 1. 📅 Visual Availability Calendar (Booking Engine)
Solves real-world concurrency for venues like *Auditoriums & Conference Rooms*. Focal Persons use a **Color-Coded Dashboard**:

| Visual Indicator | Status | Logic |
| :--- | :--- | :--- |
| 🟡 **Yellow** | **Available** | Slot is open (e.g., 8:00 AM - 9:00 AM) |
| 🟢 **Green** | **Booked** | Request Approved by Admin (Locked for others) |
| 🔴 **Red** | **Denied** | Request Rejected (Reason visible in history) |

**🧠 Zero-Conflict Rule:** If a room is pending or booked for "9-10 AM", the system automatically disables the selection for others to prevent double-booking.

### 2. 📄 Automated PDF Voucher Generation
* **Instant Documentation:** Uses **FPDF Library** to auto-generate downloadable Booking Vouchers.
* **Proof of Request:** Contains Title, No. of Persons, Time Slot, and Department details.

### 3. 👑 The "Focal Person" Trigger Logic
Unlike standard systems, this application enforces strict academic rules via **Database Triggers**:
* **Conflict-Free Assignment:** The system guarantees **only one Focal Person per department**.
* **Auto-Demotion:** Assigning a new focal person automatically revokes rights from the previous holder instantly.

---

## ⚡ Workflow Architecture

Here is how the system handles a Booking Request cycle:

```mermaid
graph LR
A[Focal Person] -- Selects Room & Time --> B(System Checks Availability)
B -- Slot Free --> C{Generate PDF Request}
C --> D[Admin Dashboard]
D -- Grant Access --> E((🟢 Booking Confirmed))
D -- Deny Access --> F((🔴 Request Rejected))

🛠️ Key Modules & Features (RBAC)
🛡️ Admin Command Center
Request Handling: Admins don't just "delete" requests. They Approve or Reject them.

State Retention: Rejected requests remain in the database history with a "Denied" status for transparency.

Faculty Onboarding: Add faculty members with specific privileges (HOD, Lecturer).

🎓 Focal Person Dashboard
Exclusive Access: Can manage News, Notices, Events, and Notifications.

Resource Booking: Access to the Booking Engine and PDF generation.

🎒 Student Portal
Context-Aware: View department-specific news, faculty lists, and announcements dynamically.

💻 Tech Stack:
Component,Technology,Description
Backend,Core PHP (Functional),"Server-side logic, Session Management"
Database,MySQL (Relational),"Triggers, Complex Queries, Stored Procedures"
Frontend,"HTML5, Tailwind CSS, JS",Responsive UI and visual components
Libraries,FPDF,For dynamic PDF generation
Server,XAMPP,Apache Server

⚙️ Installation (Localhost)
Clone the Repo:
git clone https://github.com/aliza-dev/learning-management-system-php.git

Setup Database:

Open phpMyAdmin.

Create a database named university_db.

Import the .sql file located in the database/ folder.

Note: Ensure triggers are imported correctly.

Configure:

Open db_connect.php and check credentials.

Run:

Place the folder in C:/xampp/htdocs/.

Visit: http://localhost/university

<div align="center"> <h3>📬 Contact & Credits</h3> <p>Developed with ❤️ by <strong>Aliza Tariq</strong></p> <p>Special thanks to <strong>Sir Ali Bashir</strong> for pushing us beyond "Submit & Forget" to "Logic & Architecture".</p>

<p> <a href="https://linkedin.com/in/aliza-tariq-dev"> <img src="https://img.shields.io/badge/LinkedIn-Connect-blue?style=for-the-badge&logo=linkedin" /> </a> <a href="https://github.com/aliza-dev"> <img src="https://img.shields.io/badge/GitHub-Follow-black?style=for-the-badge&logo=github" /> </a> </p> </div>
