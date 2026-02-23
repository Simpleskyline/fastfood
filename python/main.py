"""
main.py – Skyline Treats FastAPI application
Run with: uvicorn main:app --reload --host 0.0.0.0 --port 8000
"""
from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
import time

from api.config import get_settings
from api.routes.auth_routes    import router as auth_router
from api.routes.food_routes    import router as food_router
from api.routes.order_routes   import router as order_router
from api.routes.payment_routes import router as payment_router
from api.routes.profile_routes import router as profile_router
from api.routes.admin_routes   import router as admin_router
from api.routes.misc_routes    import router as misc_router

settings = get_settings()

app = FastAPI(
    title="Skyline Treats API",
    description="Python/FastAPI backend for Skyline Treats fast food platform",
    version="2.0.0",
    docs_url="/api/docs",
    redoc_url="/api/redoc",
)

# ── CORS ──────────────────────────────────────────────────────────────────────
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins_list,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Request timing middleware ─────────────────────────────────────────────────
@app.middleware("http")
async def add_timing_header(request: Request, call_next):
    start = time.time()
    response = await call_next(request)
    response.headers["X-Process-Time"] = str(round((time.time() - start) * 1000, 1)) + "ms"
    return response

# ── Global error handler ──────────────────────────────────────────────────────
@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    return JSONResponse(
        status_code=500,
        content={"success": False, "error": "Internal server error"},
    )

# ── Routers ───────────────────────────────────────────────────────────────────
app.include_router(misc_router,     prefix="/api")
app.include_router(auth_router,     prefix="/api")
app.include_router(food_router,     prefix="/api")
app.include_router(order_router,    prefix="/api")
app.include_router(payment_router,  prefix="/api")
app.include_router(profile_router,  prefix="/api")
app.include_router(admin_router,    prefix="/api")
