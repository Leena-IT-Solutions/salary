# SalaryManager 💼

SalaryManager is an enterprise-grade HR & Payroll Management System built on Laravel and Vue 3. It is designed to work seamlessly across multiple platforms, offering a **Web App**, a **Desktop Client** (via NativePHP), and a **Mobile Application** (via Capacitor).

The system automates the lifecycle of HR operations, including employee profile onboarding, shift planning, raw biometric attendance parsing, Loss of Pay (LOP) calculations, variable pay adjustments, loans/advances tracking, and fully detailed monthly payroll generation with PDF outputs.

---

## 🚀 Key Features

*   **Multi-Platform Access:**
    *   **Web App:** Responsive, modern design using Bootstrap 5 and custom SCSS patterns.
    *   **Desktop App:** Electron-wrapped native desktop client utilizing NativePHP.
    *   **Mobile App:** WebView integration using Capacitor (iOS & Android).
*   **Biometric & Face Punch Integrations:**
    *   `GET` endpoint for RFID card punch scanners.
    *   Bearer Token-secured `POST` API for facial recognition terminals.
*   **Automated LOP & Penalty Calculations:**
    *   Detects half-days, early departures, and late arrivals.
    *   Implements the **Sandwich Rule** (e.g. weekoff/holiday marked as LOP if sandwiched between LOP days).
    *   Calculates customizable pro-rata deductions or fixed penalty days.
*   **Comprehensive Financial Workflow:**
    *   **Earnings:** Standardized base earnings (Basic, HRA, etc.) computed as flat amounts or percentages of Basic/CTC.
    *   **Variable Pay & Approvals:** Handles Variable Pay, Reimbursements, Fines, and Loans with automated EMI deductions.
    *   **Statutory Compliance:** Dynamic deduction mapping for PF, ESIC, Professional Tax, etc.
*   **Roles & Portals:**
    *   **Admin Hub:** Setup settings, run monthly payroll, approve overrides, manage employees.
    *   **Time Office Hub:** Oversee shifts, review attendance calendar, and approve time updates.
    *   **Employee Portal:** Self-service dashboard for attendance tracking, holiday schedule, payslip downloads, and request approvals.

---

## 🛠️ Tech Stack

*   **Backend:** PHP 8.1+ & [Laravel 10.x](https://laravel.com)
*   **Frontend:** [Vue 3.x](https://vuejs.org) (integrated as SPA component views), Sass/SCSS, and Bootstrap 5
*   **Bundler:** [Vite](https://vitejs.dev)
*   **Desktop:** [NativePHP Electron](https://nativephp.com)
*   **Mobile:** [Capacitor CLI 8.x](https://capacitorjs.com)
*   **PDF Generation:** `barryvdh/laravel-dompdf`
*   **Excel Imports/Exports:** `maatwebsite/excel`

---

## 📁 Repository Structure

*   [`app/Models/`](file:///Users/sandeep/Projects/SalaryManager/salary/app/Models) - Contains 50+ Eloquent models mapping HR and Payroll entities (Employee, Leave, Shift, Earning, Loan, Payroll, Statutory, etc.).
*   [`app/Http/Controllers/`](file:///Users/sandeep/Projects/SalaryManager/salary/app/Http/Controllers) - Houses business controllers:
    *   [`RunPayrollController.php`](file:///Users/sandeep/Projects/SalaryManager/salary/app/Http/Controllers/RunPayrollController.php): Processes complex payroll formulas and monthly runs.
    *   [`AttendanceMachineController.php`](file:///Users/sandeep/Projects/SalaryManager/salary/app/Http/Controllers/AttendanceMachineController.php): Punch logging and daily LOP evaluation.
    *   [`PDFController.php`](file:///Users/sandeep/Projects/SalaryManager/salary/app/Http/Controllers/PDFController.php): Formats PDFs for payslips, CA reports, and bank letters.
*   [`resources/js/components/`](file:///Users/sandeep/Projects/SalaryManager/salary/resources/js/components) - Interactive Vue 3 components grouped by domain (Overview, Employee Manager, Approvals, Forms).
*   [`database/migrations/`](file:///Users/sandeep/Projects/SalaryManager/salary/database/migrations) - Database schema defining tables for tracking logs, loans, statutory compliances, and shifts.

---

## 💻 Installation & Setup

### Prerequisites

*   PHP 8.1 or higher
*   Node.js (v16+ recommended)
*   Composer
*   MySQL/MariaDB

### Steps

1.  **Clone and Navigate to the Repository:**
    ```bash
    git clone <repository-url> salary
    cd salary
    ```

2.  **Install Composer Dependencies:**
    ```bash
    composer install
    ```

3.  **Install NPM Packages:**
    ```bash
    npm install
    ```

4.  **Environment Setup:**
    ```bash
    cp .env.example .env
    ```
    *Open the `.env` file and configure your database settings (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

5.  **Generate Application Key & Run Migrations:**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```

6.  **Compile Assets & Start Development Server:**
    *   **Vite Assets Compiler (watches for Vue changes):**
        ```bash
        npm run dev
        ```
    *   **Laravel Local Host Server:**
        ```bash
        php artisan serve
        ```

---

## 📲 API Punch Machine Integration

The application has endpoints designed to integrate directly with RFID card readers and Facial Recognition terminals:

### 1. RFID Card Punches (`GET`)
Used by hardware card scanners to record card tap logs:
- **Endpoint:** `/attendance/save`
- **Method:** `GET`
- **Query Parameters:**
  - `tagid`: The unique RFID Card ID.
  - `tagms`: The registered Employee Code.
  - `dt`: The punch date (`YYYY-MM-DD`).
  - `tim`: The punch time (`HH:MM`).
- **Example:**
  ```http
  GET /attendance/save?tagid=1234&tagms=LITS0001&dt=2026-06-23&tim=09:00
  ```

### 2. Face Recognition Terminal (`POST`)
Used by facial scanners. Requests are secured with an API token:
- **Endpoint:** `/attendance/face_save`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <Attendance_Machine_API_Token>`
- **Request Parameters:**
  - `employee_code`: The registered Employee Code.
  - `p_date`: The punch date (`YYYY-MM-DD`).
  - `p_time`: The punch time (`HH:MM`).

---

## 🖥️ Platform Builds

### Desktop App (NativePHP)
Start the NativePHP Electron shell during development:
```bash
composer native:dev
```
This runs `concurrently` to serve Vite assets and launch Electron (`php artisan native:serve`).

### Mobile App (Capacitor)
Sync web assets and build native wrappers:
```bash
# Compile Vue/Laravel assets
npm run build

# Copy resources to native platforms
npx cap sync

# Open the IDE to run/build packages
npx cap open android
npx cap open ios
```
*Note: Make sure your target server URL is configured in `capacitor.config.json`.*

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
