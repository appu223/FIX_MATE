# 🔧 FixMate — Home Services Booking & Operations Platform

> **A complete digital platform for discovering, booking, dispatching, managing, and fulfilling home-service requests.**

FixMate is a modern **home-services booking and operations management platform** designed to connect **customers, technicians, and administrators** through a single workflow.

The platform simplifies the complete service journey — from **service discovery and cost estimation to booking, technician dispatch, proof-of-work verification, wallet earnings, invoicing, and administrative operations**.

---

## ✨ Project Overview

Finding a reliable technician, understanding service costs, scheduling a visit, monitoring service completion, and managing technicians can become complicated when handled through disconnected systems.

**FixMate** brings these processes together into one centralized platform.

### 🔄 Complete Workflow

```text
Customer
   │
   ▼
Browse Services
   │
   ▼
Cost Estimation
   │
   ▼
Checkout & Booking
   │
   ▼
Admin Dispatch
   │
   ▼
Technician Assignment
   │
   ▼
Service Fulfillment
   │
   ▼
Proof of Work
   │
   ▼
Admin Verification
   │
   ▼
Technician Wallet
   │
   ▼
Payout Request
```

---

# 🎯 Objectives

FixMate is designed to:

* Simplify home-service booking.
* Provide transparent service estimates.
* Connect customers with verified technicians.
* Reduce manual administrative work.
* Support structured technician dispatch.
* Maintain proof of completed work.
* Provide centralized KYC verification.
* Track service and operational activity.
* Manage technician earnings through a wallet system.
* Support tax invoice generation.
* Provide an administrative dispute/helpdesk workflow.

---

# 🚀 Key Features

## 👤 Customer Module

Customers can:

* Create an account.
* Select their city and service address.
* Browse available home services.
* Select service quantity.
* Calculate an estimated service cost.
* Apply available coupons.
* Choose optional express dispatch.
* Add services to the cart.
* Select booking date and arrival slot.
* Select a saved service address.
* Choose a payment method.
* Confirm and place a booking.
* View booking-related information.
* Track the status of their service workflow.

### Customer Registration

The registration workflow includes:

* Full Name
* Email
* 10-Digit Mobile Number
* City
* Address
* Password

Supported city options in the current interface include:

* Bengaluru
* Mumbai
* Delhi NCR
* Hyderabad

---

# 🧮 Smart Service Estimator

FixMate includes a dynamic front-end service estimator.

Customers can select:

```text
Service
   +
Quantity
   +
Coupon
   +
Express Dispatch
   ↓
Dynamic Estimate
```

### Example Services

| Service                           | Example Price |
| --------------------------------- | ------------: |
| Ceiling Fan Installation / Repair |          ₹399 |
| Split AC Deep Foam Jet Servicing  |          ₹899 |
| AC Gas Leak Check & Refill        |        ₹2,499 |
| Full 2BHK Deep Cleaning           |        ₹3,499 |

> Prices shown above are examples from the current FixMate interface/data and should not be interpreted as permanent or universal service pricing.

### Coupon Example

`FIX20`

Current behavior:

* Discount: **20%**
* Maximum discount: **₹250**

### Additional Calculation

The estimator currently supports:

* **25% urgency surge** for express dispatch
* **18% GST**

> The estimator is a front-end estimate. The final amount should be confirmed during checkout.

---

# 🛒 Customer Checkout

The checkout workflow allows customers to review their booking before confirmation.

### Checkout Includes

* Cart items
* Booking date
* Arrival slot
* Service address
* Coupon
* Subtotal
* Discount
* Tax
* Final displayed total
* Payment method

### Payment Options

```text
Online Payment
├── UPI
├── Cards
└── NetBanking

OR

Cash on Service Completion
```

Customers can then use:

**Confirm & Place Booking**

to submit the booking.

---

# 🧾 Invoice & Financial Workflow

FixMate includes an administrative invoice workflow.

The current invoice interface provides:

* Booking reference
* Customer information
* Service details
* Line items
* Tax summary
* GST information
* Print Invoice
* Download PDF

### Invoice Route

```text
/admin/financials/invoice
```

### Related View

```text
views/admin/invoice_print.php
```

> The current implementation provides PDF download/printing through the Admin Financials invoice workflow. It should not be represented as an automatic customer-side PDF delivery feature.

---

# 👨‍🔧 Technician Module

Technicians receive a dedicated operational dashboard.

### Technician Dashboard

The dashboard provides access to:

* KYC status
* Notifications
* Assigned jobs
* Active jobs
* Service schedule
* Completed jobs
* Client rating
* Net earnings
* Wallet balance
* Administrative communication

### Technician Workflow

```text
Assigned Job
     ↓
View Job Details
     ↓
Perform Service
     ↓
Upload Proof
     ↓
Admin Review
     ↓
Approval
     ↓
Eligible Wallet Credit
```

---

# 📸 Proof of Work

Technicians can submit service evidence through the proof-of-work workflow.

Supported proof categories include:

### Before Repair

Evidence of the original condition.

### After Completion

Evidence of the completed service.

### Material Bill Receipt

Supporting material/expense documentation.

The submitted evidence can then be reviewed by administrators.

---

# 💰 Technician Wallet

After eligible work is reviewed and approved, the corresponding earning can be reflected in the technician's **FixMate wallet**.

```text
Service Completed
       ↓
Proof Submitted
       ↓
Admin Approval
       ↓
Eligible Earning
       ↓
FixMate Wallet
       ↓
Separate Bank Payout Request
```

> Wallet credit and bank payout are separate processes. The platform should not be interpreted as performing an instant bank transfer after approval.

---

# 🛠️ Admin Operations

The Admin Panel is the central management system for FixMate.

Administrators can manage multiple operational areas.

## 📊 Admin Dashboard

The dashboard provides operational and financial overview information such as:

* Gross Volume
* Net Commission
* Active Pipeline
* KYC Approvals
* Verified Professionals

Visual analytics include:

```text
Revenue Analytics
Service Category Distribution
Operational KPIs
```

Relevant chart components include:

```text
#revenueSplineChart
#serviceCategoryDonut
```

---

# 🚚 Automated Dispatch

FixMate includes an administrative dispatch board for technician assignment.

The matching workflow considers:

### 1. Verification

Only appropriate verified professionals are considered.

### 2. Active Status

The technician should be active/eligible for assignment.

### 3. Service Zone

The booking zone is used to identify suitable technicians.

### 4. Active Job Load

Technicians with lower active workloads receive priority.

### 5. Rating

Rating can be used as a tie-breaker.

### Dispatch Logic

```text
Booking
   ↓
Service Zone
   ↓
Verified Professionals
   ↓
Active Professionals
   ↓
Active Job Load
   ↓
Rating Tie-Breaker
   ↓
Suggested Assignment
```

Administrators can also perform **manual assignment**.

> The dispatch workflow should not be described as precise GPS-distance matching unless such functionality is explicitly implemented.

---

# 📍 Current Example Service Zones

The current local data/interface contains examples such as:

### Bengaluru Central

* Indiranagar
* MG Road

### Bengaluru East

* Whitefield
* Bellandur

### Bengaluru South

* Koramangala
* BTM

These are examples from the current project data and are not intended as a guarantee of permanent production coverage.

---

# 🪪 KYC Verification

FixMate provides an administrator-controlled KYC workflow.

The Admin KYC Workbench allows authorized administrators to review technician verification information.

### KYC Workflow

```text
Technician Registration
        ↓
KYC Submission
        ↓
Admin Review
        ↓
Verification Decision
        ↓
Eligible Technician
```

Sensitive identity-document contents should remain protected and should not be exposed in public screenshots or demonstrations.

---

# 💬 Notifications & Support

The platform includes administrative communication and support-oriented components.

Technicians can access:

* Notifications
* Admin communication
* Job-related information

The Admin interface also includes a disputes/helpdesk workflow.

---

# ⚠️ Disputes & Helpdesk

Administrators can manage service-related issues through the dispute/helpdesk workflow.

The system can represent:

```text
Issue
 ↓
Review
 ↓
Status
 ↓
Resolution
```

Relevant view:

```text
views/admin/disputes_helpdesk.php
```

---

# 🗺️ Technician Navigation

The technician navigation interface provides navigation-related UI and a Google Maps link/sample destination content.

> The current implementation should not be represented as verified real-time GPS tracking.

---

# 🧩 System Modules

| Module            | Purpose                                              |
| ----------------- | ---------------------------------------------------- |
| Customer          | Registration, browsing, booking and service workflow |
| Service Estimator | Dynamic service cost estimation                      |
| Cart              | Manage selected services                             |
| Checkout          | Confirm booking information                          |
| Technician        | Job fulfillment and proof submission                 |
| Proof of Work     | Before/after/material evidence                       |
| Wallet            | Technician eligible earnings                         |
| Dispatch          | Technician assignment                                |
| KYC               | Technician verification                              |
| Admin Dashboard   | Operational analytics                                |
| Financials        | Financial and invoice workflow                       |
| Invoice           | Tax invoice generation/printing                      |
| Disputes          | Issue and resolution management                      |
| Notifications     | Operational updates                                  |

---

# 🏗️ Project Architecture

A simplified FixMate architecture can be represented as:

```text
                    ┌───────────────────┐
                    │     CUSTOMER      │
                    │                   │
                    │ Browse / Estimate │
                    │ Cart / Checkout   │
                    │ Booking           │
                    └─────────┬─────────┘
                              │
                              ▼
                    ┌───────────────────┐
                    │      FIXMATE      │
                    │ APPLICATION LAYER │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼────────────────┐
              │               │                │
              ▼               ▼                ▼
       ┌────────────┐  ┌────────────┐  ┌────────────┐
       │ TECHNICIAN │  │   ADMIN    │  │  FINANCIAL │
       │ WORKFLOW   │  │ OPERATIONS │  │  WORKFLOW  │
       └─────┬──────┘  └─────┬──────┘  └────────────┘
             │               │
             ▼               ▼
       Proof of Work      Dispatch / KYC
             │            / Disputes
             │               │
             └───────┬───────┘
                     ▼
              ┌───────────────┐
              │    DATABASE   │
              │    MySQL      │
              └───────────────┘
```

---

# 🗂️ Important Project Views

The project contains dedicated views for different workflows.

### Customer

```text
views/landing/index.php
views/customer/checkout.php
```

### Technician

```text
views/professional/dashboard.php
views/professional/fulfillment.php
views/professional/proof_work.php
views/professional/nav_chat.php
```

### Admin

```text
views/admin/dashboard.php
views/admin/dispatch.php
views/admin/kyc_workbench.php
views/admin/disputes_helpdesk.php
views/admin/invoice_print.php
```

---

# 🧰 Technology Stack

## Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap
* Responsive UI
* Dynamic DOM interactions
* Chart-based analytics
* Animation/UI transitions

## Backend

* PHP
* Server-side application logic
* Session-based authentication/authorization
* Role-based workflows

## Database

* MySQL
* Relational data model
* Booking management
* User management
* Technician records
* Service records
* Financial records

## Development Environment

* XAMPP
* Apache
* MySQL
* PHP
* Browser Developer Tools
* Git
* GitHub
* Visual Studio Code

---

# 🔐 Security & Privacy Considerations

FixMate is designed with role-based workflows and controlled administrative access.

Important areas include:

* Authentication
* Authorization
* Admin-only KYC review
* Protected financial workflows
* Controlled technician operations
* Sensitive document protection
* Input validation
* Session management
* Secure database access

### Never expose in public demos

```text
Passwords
Private credentials
Real customer information
Identity-document contents
Bank account information
Private contact information
```

---

# 📁 Suggested Project Structure

```text
FixMate/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── icons/
│
├── database/
│   ├── schema.sql
│   └── seeds.sql
│
├── views/
│   ├── landing/
│   │   └── index.php
│   │
│   ├── customer/
│   │   └── checkout.php
│   │
│   ├── professional/
│   │   ├── dashboard.php
│   │   ├── fulfillment.php
│   │   ├── proof_work.php
│   │   └── nav_chat.php
│   │
│   └── admin/
│       ├── dashboard.php
│       ├── dispatch.php
│       ├── kyc_workbench.php
│       ├── disputes_helpdesk.php
│       └── invoice_print.php
│
├── config/
├── controllers/
├── models/
├── routes/
├── uploads/
├── index.php
└── README.md
```

> Adjust the structure above to match the final repository if additional folders/files exist.

---

# ⚙️ Installation & Setup

## 1. Clone the Repository

```bash
git clone YOUR_REPOSITORY_URL
```

```bash
cd FixMate
```

---

## 2. Install XAMPP

Install XAMPP with:

* Apache
* MySQL
* PHP

Start:

```text
Apache
MySQL
```

from the XAMPP Control Panel.

---

## 3. Move the Project

Copy the project into:

```text
C:\xampp\htdocs\FixMate
```

---

## 4. Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database, for example:

```sql
CREATE DATABASE fixmate;
```

---

## 5. Import Database

Import:

```text
database/schema.sql
```

and, if applicable:

```text
database/seeds.sql
```

through phpMyAdmin.

---

## 6. Configure Database Connection

Update the project's database configuration with your local credentials.

Example:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "fixmate";
```

Use the actual configuration structure present in your project.

---

## 7. Start the Application

Open:

```text
http://localhost/FixMate/
```

The exact URL depends on the project folder name and Apache configuration.

---

# 🧪 Testing Workflow

A complete demo can be tested using the following sequence:

### Customer

```text
Register
 ↓
Browse Service
 ↓
Estimate
 ↓
Add to Cart
 ↓
Checkout
 ↓
Place Booking
```

### Admin

```text
Open Dashboard
 ↓
Review Booking
 ↓
Open Dispatch
 ↓
Assign Technician
 ↓
Review KYC / Work Proof
 ↓
Manage Invoice / Dispute
```

### Technician

```text
Open Dashboard
 ↓
View Assigned Job
 ↓
Perform Service
 ↓
Upload Before/After Proof
 ↓
Submit Completion
 ↓
Wait for Admin Approval
```

### Financial Workflow

```text
Approved Job
 ↓
Eligible Wallet Credit
 ↓
Payout Request
```

---

# 🎥 Project Demonstration

A recommended project demonstration sequence is:

```text
01 — Landing Page
02 — Service Estimator
03 — Customer Checkout
04 — Booking Creation
05 — Admin Dispatch
06 — Technician Dashboard
07 — Proof of Work
08 — Admin Approval
09 — Wallet
10 — Invoice
11 — Dispute/Helpdesk
12 — Final Customer Workflow
```

---

# 🎨 UI / Design Philosophy

FixMate follows a warm, clean and modern visual direction.

### Primary Palette

| Purpose              | Color     |
| -------------------- | --------- |
| Main Background      | `#fdfbf7` |
| Primary Accent       | `#eab308` |
| Card Background      | `#fdf9ee` |
| Secondary Background | `#f7f3e8` |
| Primary Text         | `#1e1b18` |

The interface uses:

* Rounded cards
* Soft shadows
* High contrast
* Clean spacing
* Responsive layouts
* Dashboard cards
* Interactive components
* Smooth transitions
* Service-oriented visual hierarchy

---

# 📱 Responsive Design

The platform is intended to provide a responsive experience across:

* Desktop
* Laptop
* Tablet
* Mobile

Important interface areas include:

* Navigation
* Service cards
* Estimator
* Checkout
* Technician dashboard
* Admin dashboard
* Dispatch board
* Forms
* Modals
* Tables
* Charts

---

# 🔮 Future Enhancements

Potential future improvements include:

* Real-time technician location tracking
* Real-time chat
* Push notifications
* Online payment gateway integration
* Automatic customer invoice delivery
* Advanced technician recommendation
* Real-time service availability
* Mobile application
* Customer loyalty program
* Technician performance analytics
* Automated dispute resolution
* Advanced fraud detection
* Service-area expansion
* AI-powered service recommendations
* Predictive maintenance suggestions

These are **future enhancements**, not necessarily current platform capabilities.

---

# ⚠️ Current Implementation Notes

The following distinctions should be maintained when presenting the project:

### Cost Estimator

The current calculator is an estimate and not automatically a final payment quote.

### Invoice

PDF download/printing is currently part of the Admin Financials workflow.

### Navigation

The current navigation interface should not be presented as live GPS tracking.

### Payments

A booking confirmation should not automatically be presented as proof that an online payment transaction was completed.

### Technician Wallet

Wallet credit after approval is different from bank payout.

### Service Availability

Current service prices, technicians and zones should be treated as current/example project data rather than universal guarantees.

---

# 🏆 Project Highlights

FixMate demonstrates the integration of multiple real-world software workflows:

```text
Customer Management
        +
Service Management
        +
Dynamic Pricing
        +
Booking Management
        +
Technician Management
        +
Dispatch
        +
KYC Verification
        +
Proof of Work
        +
Financial Management
        +
Invoice Generation
        +
Dispute Management
```

This makes FixMate more than a simple booking website — it represents a complete **service operations management workflow**.

---

# 📚 Academic Project Value

FixMate demonstrates practical implementation of:

* Web application development
* Database management
* CRUD operations
* Authentication
* Role-based access
* Form validation
* Dynamic pricing
* Booking systems
* Workflow automation
* Admin dashboards
* Financial workflows
* Document generation
* Responsive UI/UX
* Real-world business process modeling

---

# 👥 Core User Roles

| Role             | Main Responsibility                |
| ---------------- | ---------------------------------- |
| 👤 Customer      | Discover and book services         |
| 👨‍🔧 Technician | Fulfill assigned services          |
| 🛡️ Admin        | Manage operations and verification |

---

# 🌟 Project Vision

> **“Making home-service booking simpler, more structured, and easier to manage for customers, technicians, and administrators.”**

FixMate aims to bring the entire home-service lifecycle into one connected digital platform.

---

# 📌 Project Status

**Status:** 🚧 Active Development / Academic Project

The platform is continuously being refined with improvements to:

* UI/UX
* Booking workflows
* Admin operations
* Technician management
* Financial workflows
* Validation
* Security
* Performance

---

# 📄 License

This project is developed as an academic/software project.

Add your preferred license here if the repository is intended for public open-source distribution.

---

# 👨‍💻 Author

**Alphonse Niccori A**

**Project:** FixMate — Home Services Booking & Operations Platform

---

## ⭐ Support

If you find the project useful or interesting, consider giving the repository a ⭐ on GitHub.

---

### 🔧 FIXMATE

**Customer → Booking → Dispatch → Service → Verification → Wallet → Operations**

> **One platform for the complete home-service journey.**
