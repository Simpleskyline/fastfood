# Skyline Treats v3.0 – Full Stack Update


## Setup

### 1. Database
```bash
mysql -u root -p < sql/fastfood_database.sql
```

### 2. Python Backend
```bash
cd python
pip install -r requirements.txt
cp .env.example .env
# Edit .env with your DB password, JWT secret, Google OAuth keys, SMTP settings
uvicorn main:app --reload --port 8000
```

### 3. Frontend
Open with a live server (e.g. VS Code Live Server on port 5500).

### 4. Google OAuth Setup
1. Go to https://console.cloud.google.com
2. Create OAuth 2.0 credentials
3. Add redirect URI: `http://localhost:8000/api/auth/google/callback`
4. Add your Client ID and Secret to `.env`

### 5. Google Maps Setup
Replace `YOUR_GOOGLE_MAPS_API_KEY` in:
- `frontend/thankyou.html`
- `frontend/location.html`

Get a key from: https://console.cloud.google.com → Maps JavaScript API

### 6. Email (Password Reset)
Configure SMTP in `.env`. Gmail users: create an App Password in Google Account settings.

## Project Structure
```
skyline_treats_v3/
├── sql/
│   └── fastfood_database.sql       ← Updated schema
├── frontend/
│   ├── auth.html                   ← Google + email auth + forgot password
│   ├── dashboard.html              ← Full menu with combos, pizza, coffee, tea
│   ├── thankyou.html               ← Delivery + Google Maps + payment
│   ├── admin_dashboard.html        ← Full admin panel
│   ├── reset-password.html         ← Password reset page
│   ├── privacy.html                ← Privacy Policy
│   ├── terms.html                  ← Terms & Conditions
│   ├── location.html               ← Find Us + Google Maps
│   ├── cart.js                     ← Cart with delivery support
│   ├── main.css                    ← Updated with dark mode theme
│   └── ...other pages
└── python/
    ├── main.py
    ├── requirements.txt
    ├── .env.example
    ├── analytics_helper.py         ← Pandas analytics
    └── api/
        ├── config.py               ← Updated with Google/SMTP settings
        ├── models.py               ← Updated models
        └── routes/
            ├── auth_routes.py      ← Google OAuth + password reset
            ├── admin_routes.py     ← Full admin endpoints
            └── ...other routes
```
