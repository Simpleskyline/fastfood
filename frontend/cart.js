/**
 * cart.js – Skyline Treats v3
 * Handles cart storage, checkout with delivery, and order posting to Python API
 */

const CART_KEY = "ronz_cart";

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY) || "[]"); } catch { return []; }
}

function saveCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items));
}

function addToCart(item) {
  const cart = getCart();
  const key  = item._key || (String(item.id) + (item.variant||'') + (item.sugar||''));
  item._key  = key;
  const ex   = cart.find(i => i._key === key);
  if (ex) ex.quantity = (ex.quantity || 1) + 1;
  else cart.push({ ...item, quantity: 1, _key: key });
  saveCart(cart);
  return cart;
}

function removeFromCart(key) {
  saveCart(getCart().filter(i => i._key !== key));
}

function clearCart() {
  localStorage.removeItem(CART_KEY);
}

// ── Checkout ──────────────────────────────────────────────────────────────────
async function checkout(cartItems, deliveryInfo = {}) {
  const token = localStorage.getItem("st_token");
  if (!token) { window.location.href = "auth.html"; return null; }

  const API = window.API_BASE || "http://localhost:8000/api";

  const payload = {
    items: cartItems.map(i => ({
      food_id:  i.id,
      quantity: i.quantity || 1,
      variant:  i.variant  || null,
      sugar:    i.sugar    || null,
    })),
    delivery_type:         deliveryInfo.type     || "pickup",
    delivery_address:      deliveryInfo.address  || null,
    delivery_lat:          deliveryInfo.lat      || null,
    delivery_lng:          deliveryInfo.lng      || null,
    delivery_distance_km:  deliveryInfo.distKm   || null,
    delivery_fee:          deliveryInfo.fee      || 0,
  };

  try {
    const res  = await fetch(`${API}/orders/`, {
      method: "POST",
      headers: {
        "Content-Type":  "application/json",
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
  } catch(e) {
    return { success: false, error: "Network error. Please try again." };
  }
}

// Expose globally
window.addToCart    = addToCart;
window.removeFromCart = removeFromCart;
window.getCart      = getCart;
window.saveCart     = saveCart;
window.clearCart    = clearCart;
window.checkout     = checkout;
