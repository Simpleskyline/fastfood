fetch("/fastfood/php/api/food/get_food.php")
  .then(res => res.json())
  .then(data => {
    const container = document.getElementById("menu");
    container.innerHTML = "";

    data.forEach(item => {
      const card = document.createElement("div");
      card.className = "food-card";
      card.innerHTML = `
        <h3>${item.name}</h3>
        <p>Category: ${item.category}</p>
        <p>$${item.price.toFixed(2)}</p>
        <button onclick="addToCart(${item.id}, '${item.name}', ${item.price})">
          Add to Cart
        </button>
      `;
      container.appendChild(card);
    });
  });
