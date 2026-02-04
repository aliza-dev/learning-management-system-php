<div align="center">

  <h1>🏛️ University Resource Management Ecosystem</h1>
  
  <p>
    <strong>A Logic-Driven LMS & Smart Booking System</strong>
    <br />
    <em>Developed under the mentorship of Sir Ali Bashir</em>
  </p>

  <p>
    <a href="#key-features">Key Features</a> •
    <a href="#smart-booking-logic">The Booking Engine</a> •
    <a href="#tech-stack">Tech Stack</a> •
    <a href="#installation">How to Run</a>
  </p>

  <p>
    <img src="https://img.shields.io/badge/PHP-Core-777BB4?style=for-the-badge&logo=php&logoColor=white" />
    <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
    <img src="https://img.shields.io/badge/Tailwind_CSS-Design-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" />
    <img src="https://img.shields.io/badge/FPDF-Library-FF0000?style=for-the-badge&logo=adobe-acrobat-reader&logoColor=white" />
  </p>
  
  <br />

</div>

> **🚀 Why this project is different?**
> This isn't just a simple website. It is a connected **Campus Ecosystem** where **Students**, **Faculty**, and **Admins** operate with real permissions, workflows, and strict logic constraints. It moves beyond basic CRUD to handle **Concurrency** and **Role-Based Access Control (RBAC)**.

---

## 🔥 The "Wow" Factor: Smart Room Booking Engine

This project features a complex **Resource Allocation System** that solves real-world concurrency problems. It handles requests for venues like the *Auditorium, Senate Hall, and Conference Rooms*.

### 1. 📅 Visual Availability Calendar (The Logic)
Instead of boring lists, Focal Persons see a **Color-Coded Dashboard** to check room status instantly:

| Visual Indicator | Status | Logic |
| :--- | :--- | :--- |
| 🟡 **Yellow** | **Available** | Slot is open (e.g., 8:00 AM - 9:00 AM) |
| 🟢 **Green** | **Booked** | Request Approved by Admin (Locked for others) |
| 🔴 **Red** | **Denied** | Request Rejected (Reason visible in history) |

### 2. 🧠 Conflict Detection & Time Slots
* **Time Constraints:** Slots are strictly divided from **8:00 AM to 6:00 PM**.
* **Zero-Conflict Rule:** If a room is pending or booked for "9-10 AM", no other Focal Person can select it. The system automatically disables the selection to prevent double-booking.

### 3. 📄 Automated PDF Voucher Generation
* **Instant Documentation:** Upon request submission, the system uses the **FPDF Library** to auto-generate a downloadable PDF.
* **Proof of Request:** This PDF serves as a digital voucher containing the *Title, No. of Persons, Time Slot,* and *Department details*.

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

🛠️ Key Modules & Features
🔹 True Role-Based Access Control (RBAC)
Each user lives in their own secure environment:

👑 Admin: The Controller. Can grant/deny room requests, manage departments, and oversee the entire system.

🎓 Focal Person: The Manager. Can book rooms, post News/Events specific to their department, and generate PDFs.

🎒 Students: The End-User. Accesses academic data, notices, and department updates.

🔹 Advanced Admin Panel
Request Handling: Admins don't just "delete" requests. They Approve or Reject them.

State Retention: Rejected requests remain in the database history with a "Denied" status for transparency.

Single Focal Person Logic: The system uses Database Triggers to ensure only one Focal Person exists per department. Assigning a new one automatically demotes the previous one.

💻 Tech Stack
Backend: Core PHP (Functional Programming)

Database: MySQL (Relational Schema with Complex Queries)

Frontend: HTML5, JavaScript, Tailwind CSS

Libraries: FPDF (For PDF Generation)

Server: XAMPP (Apache)


⚙️ Installation (Localhost)
Clone the Repo:

Bash
git clone [https://github.com/aliza-dev/learning-management-system-php.git](https://github.com/aliza-dev/learning-management-system-php.git)
Setup Database:

Open phpMyAdmin.

Create a database named university_db.

Import the .sql file located in the database/ folder.

Configure:

Open db_connect.php and check credentials.

Run:

Place the folder in C:/xampp/htdocs/.

Visit: http://localhost/university


<div align="center"> <h3>📬 Contact & Credits</h3> <p>Developed with ❤️ by <strong>Aliza Tariq</strong></p> <p>Special thanks to <strong>Sir Ali Bashir</strong> for pushing us beyond "Submit & Forget" to "Logic & Architecture".</p>

<p> <a href="https://www.google.com/search?q=https://linkedin.com/in/aliza-tariq-dev"> <img src="https://www.google.com/search?q=https://img.shields.io/badge/LinkedIn-Connect-blue%3Fstyle%3Dfor-the-badge%26logo%3Dlinkedin" /> </a> <a href="https://github.com/aliza-dev"> <img src="https://www.google.com/search?q=https://img.shields.io/badge/GitHub-Follow-black%3Fstyle%3Dfor-the-badge%26logo%3Dgithub" /> </a> </p> </div>

