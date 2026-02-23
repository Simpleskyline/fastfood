/**
 * cart.js – Skyline Treats
 * Local cart stored in localStorage; checkout posts to Python API
 */

const API = window.API_BASE || "http://localhost:8000/api";
const CART_KEY = "ronz_cart";

// ── Cart state ────────────────────────────────────────────────────────────────
function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY) || "[]"); } catch { return []; }
}

function saveCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items));
}

function addToCart(item) {
  const cart = getCart();
  const existing = cart.find(i => i.id === item.id);
  if (existing) {
    existing.quantity = (existing.quantity || 1) + 1;
  } else {
    cart.push({ ...item, quantity: 1 });
  }
  saveCart(cart);
  return cart;
}

function removeFromCart(foodId) {
  saveCart(getCart().filter(i => i.id !== foodId));
}

function clearCart() {
  localStorage.removeItem(CART_KEY);
}

// ── Checkout ──────────────────────────────────────────────────────────────────
async function checkout(cartItems) {
  const token = localStorage.getItem("st_token");
  if (!token) {
    window.location.href = "auth.html";
    return null;
  }

  const payload = {
    items: cartItems.map(i => ({ food_id: i.id, quantity: i.quantity || 1 })),
  };

  const res = await fetch(`${API}/orders/`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${token}`,
    },
    body: JSON.stringify(payload),
  });

  const data = await res.json();

  if (data.success) {
    localStorage.setItem("last_order_id",    String(data.order_id));
    localStorage.setItem("last_order_total", String(data.total));
    clearCart();
  }

  return data;
}

// Make helpers globally accessible
window.addToCart   = addToCart;
window.removeFromCart = removeFromCart;
window.getCart     = getCart;
window.saveCart    = saveCart;
window.clearCart   = clearCart;
window.checkout    = checkout;
