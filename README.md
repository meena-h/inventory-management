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
| Admin | Full access |
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
