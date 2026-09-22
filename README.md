# Product Manager

A web-based inventory management application built with native PHP 8.2 and MySQL 8.0 running inside Docker containers. Implements a **Front Controller (Single Entry Point)** pattern with a **Controller-View** architecture, a responsive **Bootstrap 5.3** dark theme enhanced by a minimal surgical CSS layer, and multi-layered web security (CSRF, XSS, SQL Injection).

> **Environment Consistency:** Containerized with Docker to guarantee cross-platform environment parity (Linux, macOS, Windows) and eliminate local environment discrepancies, ensuring reproducible runtime behavior across any machine without relying on OS-specific local server stacks.

---

## Tech Stack

| Component | Specification / Library | Purpose |
|---|---|---|
| **Backend Language** | PHP 8.2 (Strict Types) | Application logic, request handling, and validation |
| **Database Abstraction** | PHP Data Objects (PDO) | Secure database operations via prepared statements |
| **Database** | MySQL 8.0 Community Server | Relational persistence and constraint enforcement |
| **Web Server** | Apache 2.4 (mpm_prefork) | HTTP request handling and URL rewrites |
| **CSS Framework** | Bootstrap 5.3.3 (CDN) | Responsive layout grid, form controls, and dark theme |
| **Iconography** | Bootstrap Icons 1.11.3 (CDN) | Consistent visual icons without inline SVGs |
| **Typography** | Inter (Google Fonts) | Clean, modern typographic hierarchy |
| **Containerization** | Docker | Isolated, reproducible container runtime |

---

## Features and Business Validation

| Operation | Feature | Business Validation Rules |
|---|---|---|
| **Create** | Add new product | - Name: required, 3–23 characters, unique across all records.<br>- Category: required, maximum 100 characters.<br>- Price: numeric, strictly greater than 0.<br>- Stock: non-negative integer between 0 and 20 units. |
| **Read** | Product catalog & metrics | - Responsive card grid layout.<br>- Dynamic visual stock meter (red $\le 3$, yellow $\le 10$, green $> 10$).<br>- Real-time aggregation of total products, total stock, and total inventory value. |
| **Update** | Edit existing product | - Pre-populates form data by product ID.<br>- Enforces identical validation constraints as Create.<br>- Name uniqueness check excludes the product currently being edited. |
| **Delete** | Remove product | - Accepts only HTTP POST requests.<br>- Mandatory CSRF token verification prior to database deletion.<br>- Client-side user confirmation before request dispatch. |

---

## System Architecture

The application adopts the **Front Controller (Single Entry Point)** pattern paired with a **Controller-View** separation of concerns:

```text
HTTP Request
     │
     ▼
[ src/index.php ] ── (Session Init, DB Singleton, Helpers, Route Resolver)
     │
     ├── action=read   ──▶ [ controllers/read.php ]   ──▶ [ views/read.view.php ]   ──▶ HTML Response
     ├── action=create ──▶ [ controllers/create.php ] ──▶ [ views/create.view.php ] ──▶ HTML Response / PRG Redirect
     ├── action=edit   ──▶ [ controllers/edit.php ]   ──▶ [ views/edit.view.php ]   ──▶ HTML Response / PRG Redirect
     └── action=delete ──▶ [ controllers/delete.php ] ──▶ PRG Redirect (Session Flash)
```

---

## Security Mechanisms

| Attack Vector | Defense Mechanism | Implementation Details |
|---|---|---|
| **SQL Injection** | PDO Prepared Statements | All queries use parameterized binding (`:name`, `:price`, `:id`). Emulated prepared statements are explicitly disabled (`ATTR_EMULATE_PREPARES => false`). |
| **Cross-Site Scripting (XSS)** | Context-Aware Output Escaping | All dynamic user and database outputs pass through the helper function `h()`, applying `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. |
| **Cross-Site Request Forgery (CSRF)** | Synchronizer Token Pattern | A cryptographically secure 32-byte session token (`random_bytes`) is validated on every state-changing POST request using `hash_equals()`. |
| **Double Form Submission** | Post-Redirect-Get (PRG) | Successful POST operations (Create, Update, Delete) issue an HTTP 302 redirect back to `index.php` along with one-time session flash notifications. |
| **Arbitrary File Inclusion** | Routing Whitelist | Route parameters (`action`) are matched strictly against a predefined whitelist in the Front Controller; unmatched values trigger an HTTP 404 response. |

---

## Project Structure

```text
Product-Manager/
├── Dockerfile                      
├── docker-compose.yml              
├── README.md                       
└── src/
    ├── .htaccess                   
    ├── index.php                   
    ├── db.php                      
    ├── helpers.php                 
    ├── schema.sql                  
    ├── controllers/                
    │   ├── read.php                
    │   ├── create.php              
    │   ├── edit.php                
    │   └── delete.php              
    ├── views/                      
    │   ├── _layout_head.php        
    │   ├── _layout_foot.php        
    │   ├── read.view.php           
    │   ├── create.view.php         
    │   └── edit.view.php           
    └── assets/
        └── style.css               
```

---

## Getting Started

### Prerequisites

- Docker Engine (v20.10 or later) and Docker Compose.

### 1. Start Docker Containers

**Standard start (Using existing image):**
```bash
docker compose up -d
```

**Rebuild and start (Use when Dockerfile is modified):**
```bash
docker compose up -d --build
```

### 2. Initialize Database Schema (First-Time Setup)

```bash
docker exec -i product-manager_db_1 mysql -u app_user -papp_pass product_manager < src/schema.sql
```

### 3. Access the Application

```text
http://localhost:8080/
```

### 4. Stop Docker Containers

**Stop without deleting data (Preserve Database):**
```bash
docker compose down
```

**Stop and wipe all data (Reset Database & Volumes):**
```bash
docker compose down -v
```

---

## Routing Reference

| Action | URL Endpoint | HTTP Method | Handler | Description |
|---|---|---|---|---|
| **List Products** | `/` or `/index.php?action=read` | `GET` | `controllers/read.php` | Displays dashboard metrics and product catalog |
| **Create Form** | `/index.php?action=create` | `GET` | `controllers/create.php` | Renders the product creation form |
| **Store Product** | `/index.php?action=create` | `POST` | `controllers/create.php` | Validates input and persists a new product |
| **Edit Form** | `/index.php?action=edit&id={id}` | `GET` | `controllers/edit.php` | Fetches record by ID and renders pre-filled edit form |
| **Update Product** | `/index.php?action=edit` | `POST` | `controllers/edit.php` | Validates input and updates product record |
| **Delete Product** | `/index.php?action=delete` | `POST` | `controllers/delete.php` | Validates CSRF token and deletes product record |

---

## Environment Configuration

Database connection settings configured in [`docker-compose.yml`]:

| Variable Name | Default Value | Description |
|---|---|---|
| `DB_HOST` | `db` | Database service hostname |
| `DB_NAME` / `MYSQL_DATABASE` | `product_manager` | Application database name |
| `DB_USER` / `MYSQL_USER` | `app_user` | Database user account |
| `DB_PASS` / `MYSQL_PASSWORD` | `app_pass` | Database user password |
| `MYSQL_ROOT_PASSWORD` | `root_pass` | MySQL root administrative password |

---

## Author & License

- **Author**: Muhammad Taufiq
- **License**: Distributed under the MIT License.
