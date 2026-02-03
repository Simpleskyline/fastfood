function checkout() {
  const payload = {
    items: cart.map(item => ({
      food_id: item.id,
      quantity: item.qty
    }))
  };

  fetch("/fastfood/php/api/orders/create_order.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Order placed successfully!");
      cart = [];
      renderCart();
    } else {
      alert(data.error || "Order failed");
    }
  });
}
fetch("/fastfood/php/api/cart/get_cart.php")
  .then(res => res.json())
  .then(data => {
    cart = data;
    renderCart();
  });