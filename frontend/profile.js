/**
 * profile.js – Skyline Treats
 * Fetches and updates user profile via Python API
 */

const API = window.API_BASE || "http://localhost:8000/api";

function authHeaders() {
  return {
    "Content-Type": "application/json",
    "Authorization": `Bearer ${localStorage.getItem("st_token")}`,
  };
}

// ── Load profile ──────────────────────────────────────────────────────────────
async function loadProfile() {
  const token = localStorage.getItem("st_token");
  if (!token) { window.location.href = "auth.html"; return; }

  try {
    const res  = await fetch(`${API}/profile/`, { headers: authHeaders() });
    const data = await res.json();

    if (!data.success) {
      if (res.status === 401) { window.location.href = "auth.html"; return; }
      document.getElementById("profileDetails").innerHTML = "<p>Could not load profile.</p>";
      return;
    }

    const u = data.user;

    // Populate display
    const details = document.getElementById("profileDetails");
    if (details) {
      details.innerHTML = `
        <p><strong>Name:</strong> ${u.first_name} ${u.last_name}</p>
        <p><strong>Username:</strong> @${u.username}</p>
        <p><strong>Email:</strong> ${u.email}</p>
        <p><strong>Phone:</strong> ${u.phone || "—"}</p>
        <p><strong>Location:</strong> ${u.location || "—"}</p>
        <p><strong>Role:</strong> ${u.role}</p>
        <p><strong>Member Since:</strong> ${new Date(u.created_at).toLocaleDateString("en-KE", { month: "long", year: "numeric" })}</p>
      `;
    }

    // Populate form fields
    ["first_name", "last_name", "phone", "location"].forEach(field => {
      const el = document.getElementById(field);
      if (el) el.value = u[field] || "";
    });

  } catch {
    document.getElementById("profileDetails").innerHTML = "<p>Server error.</p>";
  }
}

// ── Load order history ────────────────────────────────────────────────────────
async function loadOrderHistory() {
  const container = document.getElementById("orderHistory");
  if (!container) return;

  try {
    const res  = await fetch(`${API}/orders/my`, { headers: authHeaders() });
    const data = await res.json();

    if (!data.success || !data.orders.length) {
      container.innerHTML = "<p>No orders yet.</p>";
      return;
    }

    container.innerHTML = data.orders.map(order => `
      <div class="order-card">
        <div class="order-header">
          <span class="order-id">#${order.id}</span>
          <span class="order-status ${order.status}">${order.status}</span>
          <span class="order-date">${new Date(order.created_at).toLocaleDateString()}</span>
          <strong class="order-total">KSH ${Number(order.total_amount).toFixed(2)}</strong>
        </div>
        <ul class="order-items">
          ${order.items.map(i => `<li>${i.name} × ${i.quantity} — KSH ${Number(i.line_total).toFixed(2)}</li>`).join("")}
        </ul>
      </div>
    `).join("");

  } catch {
    container.innerHTML = "<p>Could not load orders.</p>";
  }
}

// ── Update profile form ───────────────────────────────────────────────────────
const profileForm = document.getElementById("profileForm");
if (profileForm) {
  profileForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = profileForm.querySelector("button[type=submit]");
    btn.disabled = true;
    btn.textContent = "Saving…";

    try {
      const res  = await fetch(`${API}/profile/`, {
        method: "PUT",
        headers: authHeaders(),
        body: JSON.stringify({
          first_name: document.getElementById("first_name")?.value.trim() || null,
          last_name:  document.getElementById("last_name")?.value.trim()  || null,
          phone:      document.getElementById("phone")?.value.trim()      || null,
          location:   document.getElementById("location")?.value.trim()   || null,
        }),
      });
      const data = await res.json();
      if (data.success) {
        if (typeof UI !== "undefined") UI.showToast("Profile updated!", "success");
        else alert("Profile updated!");
        loadProfile();
      } else {
        alert(data.detail || data.error || "Update failed");
      }
    } catch {
      alert("Server error");
    } finally {
      btn.disabled = false;
      btn.textContent = "Save Changes";
    }
  });
}

// ── Modal helpers ─────────────────────────────────────────────────────────────
function openModal()  { document.getElementById("profileModal")?.style && (document.getElementById("profileModal").style.display = "flex"); }
function closeModal() { document.getElementById("profileModal")?.style && (document.getElementById("profileModal").style.display = "none"); }

// ── Initialise ────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  loadProfile();
  loadOrderHistory();
});
