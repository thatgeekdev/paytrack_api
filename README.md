#  PayTrack API

A production-ready backend API for managing users, wallets, and financial transactions (deposit, withdrawal, and transfer).

This project simulates a real-world fintech system with a strong focus on clean architecture, data integrity, security, and scalability.

---

##  Overview

PayTrack API is designed to demonstrate:

* Clean architecture (Controller → Service → Repository)
* Financial transaction handling with atomic operations
* Secure authentication using token-based access
* Real-world backend engineering practices

---

##  Tech Stack

* **Laravel (PHP)**
* **Laravel Sanctum** (Authentication)
* **MySQL / PostgreSQL**
* **REST API**

---

##  Architecture

The project follows a layered architecture:

```
app/
 ├── Http/Controllers/     # Handles HTTP requests/responses
 ├── Services/             # Business logic
 ├── Repositories/         # Data access layer
 ├── Models/               # Entities
 ├── Http/Resources/       # API response formatting
```

---

##  Authentication

Token-based authentication using Laravel Sanctum.

### Endpoints

* `POST /api/register`
* `POST /api/login`
* `GET /api/profile` (protected)

---

##  Users & Wallets

* Each user automatically gets a wallet upon registration
* Wallet stores the user’s balance

### Endpoint

* `GET /api/wallet`

---

##  Transactions

Supports three types of financial operations:

* **Deposit**
* **Withdraw**
* **Transfer (user-to-user)**

---

### Endpoints

* `POST /api/deposit`
* `POST /api/withdraw`
* `POST /api/transfer`

---

##  Core Business Rules

* Users cannot withdraw more than their available balance
* Transfers must be atomic (all operations succeed or fail together)
* Users cannot transfer to themselves
* All operations are recorded in transaction history
* Users can only access their own data

---

##  Data Integrity & Concurrency

* Database transactions (`DB::transaction`) ensure atomic operations
* Row-level locking (`lockForUpdate`) prevents race conditions
* Prevents double spending and inconsistent balances

---

##  Logging

Structured logs are implemented for all financial operations:

* Deposit
* Withdraw
* Transfer

Logs include:

* User IDs
* Amounts
* Operation status (success/failure)

 Stored in:

```
storage/logs/laravel.log
```

---

##  Transaction History

Users can view their transaction history with filters and pagination.

### Endpoint

```
GET /api/transactions
```

### Supported Filters

* `type` → deposit | withdraw | transfer
* `min_amount`
* `max_amount`
* `date_from`
* `date_to`

### Example

```
/api/transactions?type=transfer&min_amount=100
```

---

##  Testing

Feature tests are implemented to validate core flows:

* Deposit success
* Withdraw success & failure
* Transfer success & validation rules

Run tests:

```bash
php artisan test
```

---

##  Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/your-username/paytrack-api.git
cd paytrack-api
```

---

### 2. Install dependencies

```bash
composer install
```

---

### 3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`

---

### 4. Run migrations

```bash
php artisan migrate
```

---

### 5. Start server

```bash
php artisan serve
```

---

## 📌 Example Flow

1. Register a user
2. Login and get token
3. Deposit funds
4. Transfer to another user
5. Check transaction history

---

##  Roadmap (Next Steps)

###  Short Term

* [ ] Improve validation (Form Requests)
* [ ] Add API rate limiting
* [ ] Enhance error handling (custom exceptions)

---

###  Medium Term

* [ ] Event-driven architecture (Events & Listeners)
* [ ] Background jobs (Queues)
* [ ] Notifications (email / SMS simulation)

---

##  Key Concepts Implemented

* Clean architecture (Separation of concerns)
* Atomic database transactions
* Concurrency control (row locking)
* Structured logging
* RESTful API design
* Pagination and filtering

---

##  Author

José Matsimbe
Software Engineer

---

## 📄 License

This project is for educational and portfolio purposes.
