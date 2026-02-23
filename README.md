# Skyline Treats – Full Stack Setup Guide

## Architecture

```
Browser (HTML/CSS/JS)
      │  fetch() calls
      ▼
Python FastAPI  (:8000)
      │  mysql-connector-python
      ▼
MySQL 8.0  (fastfood database)
```

PHP has been completely removed. Python (FastAPI) is now the backend AND analytics engine.

---

## 1. Database Setup

```bash
mysql -u root -p < sql/fastfood_database.sql
```

This creates the `fastfood` database with all tables, seed data, views, and the `place_order` stored procedure.

**Tables created:**
| Table | Purpose |
|---|---|
| `roles` | customer / admin |
| `users` | All user accounts |
| `categories` | Food categories |
| `food_items` | Full menu with prices |
| `orders` | Every order placed |
| `order_items` | Line items per order |
| `payments` | M-Pesa / Card / Crypto records |
| `sessions` | Optional server-side sessions |
| `api_logs` | Every API request logged |
| `rate_limits` | Login throttling |
| `contact_messages` | Contact form submissions |

**Views:** `vw_daily_revenue`, `vw_top_items`, `vw_user_summary`, `vw_payment_summary`

---

## 2. Python Backend Setup

```bash
cd python/

# Create virtual environment
python -m venv venv
source venv/bin/activate        # Linux/Mac
venv\Scripts\activate           # Windows

# Install dependencies
pip install -r requirements.txt

# Configure environment
cp .env.example .env
# Edit .env – set DB_PASS, and change JWT_SECRET to a long random string

# Start the server
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

The API will be live at: **http://localhost:8000**

Interactive docs: **http://localhost:8000/api/docs**

---

## 3. API Endpoints

### Auth (no token required)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/register` | Create account |
| POST | `/api/auth/login` | Sign in, returns JWT |
| POST | `/api/auth/logout` | Sign out |

### Food (public)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/food/` | All active food items |
| GET | `/api/food/categories` | All categories |
| GET | `/api/food/{id}` | Single food item |

### Orders (JWT required)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/orders/` | Place new order |
| GET | `/api/orders/my` | My order history |

### Payments (JWT required)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/payments/` | Record payment |

### Profile (JWT required)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/profile/` | Get my profile |
| PUT | `/api/profile/` | Update profile |

### Admin (admin JWT required)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/stats` | Full analytics dashboard |
| GET | `/api/admin/orders` | All orders |
| GET | `/api/admin/users` | All users |

### Misc
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/ping` | Health check |
| POST | `/api/contact` | Contact form |

---

## 4. Frontend Setup

Copy all files from `frontend/` into your web server root (e.g. Apache `htdocs`, Nginx `html`, or VS Code Live Server).

If your Python API is on a different host/port than `localhost:8000`, update this line in `dashboard.html` and `auth.html`:

```js
window.API_BASE = "http://localhost:8000/api";
```

**Files updated (PHP removed):**
- `auth.js` — uses `/api/auth/login` and `/api/auth/register`
- `cart.js` — uses `/api/orders/` for checkout
- `profile.js` — uses `/api/profile/`
- `dashboard.html` — loads menu from `/api/food/`, has auth guard
- `thankyou.html` — posts payments to `/api/payments/`

---

## 5. JWT Flow

1. User logs in → backend returns `{ token, user }`
2. Frontend stores token in `localStorage.st_token`
3. Every protected request sends `Authorization: Bearer <token>`
4. Token expires after 24 hours (configurable via `JWT_EXPIRE_MINUTES` in `.env`)

---

## 6. Analytics (Python)

The Python analytics layer runs inside the same FastAPI process.
Admin stats endpoint (`GET /api/admin/stats`) returns:

- Total orders & revenue
- Orders by status
- Top 10 food items by units sold + revenue
- Top 10 customers by spend
- Daily revenue for last 30 days (pandas-aggregated, zero-filled)
- New users in last 30 days

All powered by `analytics/orders_analytics.py` and `analytics/user_analytics.py`.

---

## 7. Project Structure

```
python/
├── main.py                    ← FastAPI app entry point
├── requirements.txt
├── .env.example               ← Copy to .env and configure
├── api/
│   ├── config.py              ← Settings (reads .env)
│   ├── db.py                  ← MySQL connection pool
│   ├── auth.py                ← JWT helpers + FastAPI dependencies
│   ├── models.py              ← Pydantic request/response models
│   └── routes/
│       ├── auth_routes.py
│       ├── food_routes.py
│       ├── order_routes.py
│       ├── payment_routes.py
│       ├── profile_routes.py
│       ├── admin_routes.py
│       └── misc_routes.py
└── analytics/
    ├── orders_analytics.py    ← Order metrics + pandas
    └── user_analytics.py      ← User metrics + pandas

frontend/
├── auth.js                    ← Updated: Python API
├── cart.js                    ← Updated: Python API
├── profile.js                 ← Updated: Python API
├── dashboard.html             ← Updated: dynamic menu + auth guard
└── thankyou.html              ← Updated: Python payments API

sql/
└── fastfood_database.sql      ← Full MySQL schema + seed data
```
