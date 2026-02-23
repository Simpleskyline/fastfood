"""
routes/food_routes.py – Food items & categories
"""
from fastapi import APIRouter, Depends
from ..db import query_all, query_one
from ..auth import get_current_user

router = APIRouter(prefix="/food", tags=["Food"])


@router.get("/")
def get_all_food():
    """Public – no auth required. Returns all active food items."""
    items = query_all(
        """SELECT f.id, f.name, f.description, f.price, f.image_url,
                  c.name AS category, c.slug AS category_slug
           FROM food_items f
           JOIN categories c ON f.category_id = c.id
           WHERE f.is_active = 1
           ORDER BY c.sort_order, f.name"""
    )
    return {"success": True, "data": items}


@router.get("/categories")
def get_categories():
    cats = query_all(
        "SELECT id, name, slug FROM categories ORDER BY sort_order"
    )
    return {"success": True, "data": cats}


@router.get("/{food_id}")
def get_food_item(food_id: int):
    item = query_one(
        """SELECT f.id, f.name, f.description, f.price, f.image_url,
                  c.name AS category
           FROM food_items f
           JOIN categories c ON f.category_id = c.id
           WHERE f.id = %s AND f.is_active = 1""",
        (food_id,),
    )
    if not item:
        from fastapi import HTTPException
        raise HTTPException(404, "Food item not found")
    return {"success": True, "data": item}
