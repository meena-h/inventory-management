# Inventory Management System

A Laravel 12 REST API for managing inventory including products, suppliers, stock tracking, purchase orders and reports.

## Requirements
- PHP 8.2+
- Composer
- MySQL
- XAMPP or any local server

## Setup Instructions

### 1. Clone the repository
```bash
git clone https://github.com/meena-h/inventory-management.git
cd inventory-management
```

### 2. Install dependencies
```bash
composer install
```

### 3. Create environment file
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database
Open `.env` and update:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management
DB_USERNAME=root
DB_PASSWORD=

### 5. Run migrations
```bash
php artisan migrate
```

### 6. Start the server
```bash
php artisan serve
```

API is now running at `http://127.0.0.1:8000`

## Roles
| Role | Access |
|------|--------|
| Admin | Full access — manage products, categories, suppliers, purchase orders |
| Staff | Stock in/out and view reports |

## API Endpoints

### Auth
| Method | Endpoint | Access |
|--------|----------|--------|
| POST | /api/register | Public |
| POST | /api/login | Public |
| GET | /api/profile | All |
| POST | /api/logout | All |

### Categories
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | /api/categories | All |
| GET | /api/categories/{id} | All |
| POST | /api/categories | Admin |
| PUT | /api/categories/{id} | Admin |
| DELETE | /api/categories/{id} | Admin |

### Suppliers
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | /api/suppliers | All |
| GET | /api/suppliers/{id} | All |
| POST | /api/suppliers | Admin |
| PUT | /api/suppliers/{id} | Admin |
| DELETE | /api/suppliers/{id} | Admin |

### Products
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | /api/products | All |
| GET | /api/products/{id} | All |
| POST | /api/products | Admin |
| PUT | /api/products/{id} | Admin |
| DELETE | /api/products/{id} | Admin |

### Stock
| Method | Endpoint | Access |
|--------|----------|--------|
| POST | /api/stocks/in | All |
| POST | /api/stocks/out | All |
| GET | /api/stocks/current | All |
| GET | /api/stocks/low | All |
| GET | /api/stocks/{product_id}/history | All |

### Purchase Orders
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | /api/purchase-orders | All |
| GET | /api/purchase-orders/{id} | All |
| POST | /api/purchase-orders | Admin |
| PUT | /api/purchase-orders/{id}/receive | Admin |
| PUT | /api/purchase-orders/{id}/cancel | Admin |

### Reports
| Method | Endpoint | Access |
|--------|----------|--------|
| GET | /api/reports/master | All |
| GET | /api/reports/stock-movements | All |
| GET | /api/reports/stock-at-date | All |

## Process Flow

Supplier
↓
Purchase Order Created (status: pending)
↓
Purchase Order Marked as Received (status: received)
↓
Stock Automatically Added (stock_transactions + current_stocks updated)
↓
Manual Stock Out when items are used
↓
Reports show full movement history

## Stock Rules
- Stock can never go below 0
- Every movement is logged in stock_transactions
- current_stocks table maintains latest stock for fast reads
- Receiving a purchase order auto triggers stock in

## Auto Generated Codes
| Field | Format | Example |
|-------|--------|---------|
| category_code | CAT-XXXX | CAT-0001 |
| supplier_code | SUP-XXXX | SUP-0001 |
| product_code | PROD-XXXX | PROD-0001 |
| sku | SKU-XXXX | SKU-0001 |
| order_code | PO-XXXX | PO-0001 |

## Database Schema
| Table | Description |
|-------|-------------|
| users | Admin and staff accounts |
| categories | Product categories with category_code |
| suppliers | Product suppliers with supplier_code |
| products | Inventory items with product_code and sku |
| product_supplier | Many-to-many pivot between products and suppliers |
| stock_transactions | Full log of all stock in/out movements |
| current_stocks | Latest stock quantity per product |
| purchase_orders | Orders raised from suppliers |
| purchase_order_products | Line items in each purchase order |
