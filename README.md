# Talent HUB - Job Board Platform

Talent HUB is a specialized platform designed to connect Candidates, Recruiters, and Administrators. This project focuses on building a robust, secure, and scalable MVC architecture from scratch using PHP 8, emphasizing clean code through the Repository Pattern.

## The Team
* **Fatima-ezzahrae Boukhali**
* **Adnane El Yazidi**
* **Ali Kara**

---

## Project Context
The goal of this project is to develop a reusable authentication foundation (MVC without frameworks) and extend it into a complete job board experience.

### Key Objectives
* **MVC Architecture:** Separation of concerns for high maintainability.
* **Repository Pattern:** Isolation of data access logic.
* **PDO Security:** Use of prepared statements to prevent SQL injection.
* **RBAC:** Multi-role authentication (Admin, Recruiter, Candidate).
* **Soft Delete:** Archiving system for data preservation.
* **AJAX Integration:** Dynamic search and filtering for a fluid UX.
* **Secure Uploads:** Controlled management of CVs and images.

---

## Technical Stack
* **Backend:** PHP 8.2+ (OOP)
* **Architecture:** Custom MVC
* **Database:** MySQL (MariaDB)
* **Patterns:** Repository Pattern, Singleton, Inheritance Mapping
* **Frontend:** Tailwind CSS, Vanilla JS / AJAX

---

## Database Structure (Class Inheritance)
The database uses a **Class Table Inheritance** strategy. All users reside in a central `users` table, while role-specific data is stored in specialized child tables.



* **Roles:** Dynamic role management via `roles` table.
* **Users:** Common credentials (First Name, Last Name, Email, Password).
* **Candidates:** Inherits from User (CV path, Expected Salary).
* **Recruiters:** Inherits from User (Company Name, Description, Website).
* **Offers:** Linked to Categories and multiple Tags (Skills).

---

## Features

### Back Office (Admin & Recruiters)
- **Dashboard:** Statistics on categories, tags, and active users.
- **CRUD Operations:** Manage Categories and Tags.
- **Job Management:** Create, update, and archive (soft delete) job offers.
- **Application Tracking:** Consult candidate profiles and manage application statuses.

### Front Office (Candidates & Visitors)
- **Auth System:** Secure Register/Login/Logout with role-based redirection.
- **Job Search:** Dynamic filtering by keywords, categories, and tags.
- **Applications:** Secure CV upload and application form.
- **Recommendations:** Smart matching based on candidate skills and salary requirements.

---

## Security Implementation
- **Passwords:** Hashed using `password_hash()`.
- **Routes:** Middleware-guarded routes with 403 Access Denied handling.
- **Data:** 100% Prepared Statements for all database interactions.
- **Files:** Unique naming and MIME-type validation for all uploads.

---

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ikara-py/Talent_HUB



## Structure
   ```bash
Talent_HUB/
├─ App/
│  ├─ Controllers/
│  │  ├─ AdminController.php
│  │  ├─ AuthController.php
│  │  ├─ CandidateController.php
│  │  ├─ Controller.php
│  │  ├─ Home.php
│  │  └─ RecruiterController.php
│  ├─ Core/
│  │  └─ App.php
│  ├─ Models/
│  │  ├─ Candidate.php
│  │  ├─ Category.php
│  │  ├─ Recruiter.php
│  │  ├─ Role.php
│  │  ├─ Tag.php
│  │  └─ User.php
│  ├─ repositories/
│  │  ├─ CandidateRepository.php
│  │  ├─ CategoryRepository.php
│  │  ├─ RecruiterRepository.php
│  │  ├─ RoleRepository.php
│  │  ├─ TagRepository.php
│  │  └─ UserRepository.php
│  ├─ Services/
│  │  ├─ AuthService.php
│  │  └─ RegistrationService.php
│  └─ views/
│     ├─ admin/
│     │  ├─ categories.twig
│     │  ├─ category_form.twig
│     │  ├─ dashboard.twig
│     │  ├─ tag_form.twig
│     │  └─ tags.twig
│     ├─ auth/
│     │  ├─ login.twig
│     │  └─ register.twig
│     ├─ candidate/
│     │  └─ dashboard.twig
│     ├─ errors/
│     │  ├─ 403.php
│     │  └─ 404.php
│     ├─ home/
│     │  └─ index.twig
│     └─ recruiter/
│        └─ dashboard.twig
├─ Assets/
│  └─ useCase.PNG
├─ config/
│  ├─ config.php
│  └─ connection.php
├─ Database/
│  ├─ Database.sql
│  ├─ UML.mwb
│  ├─ UML.pdf
│  └─ UML.png
├─ public/
│  ├─ css/
│  │  └─ style.css
│  ├─ .htaccess
│  └─ index.php
├─ .env
├─ .env.example
├─ .gitignore
├─ .htaccess
├─ composer.json
├─ composer.lock
└─ README.md
