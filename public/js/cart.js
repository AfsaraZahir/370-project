export async function initCart() {
  const res = await fetch("/get-cart");
  const data = await res.json();

  const list = document.getElementById("cart-list");
  const totalEl = document.getElementById("total");

  list.innerHTML = "";

  let total = 0;

  data.forEach((item, i) => {
    const itemTotal = Number(item.price) * Number(item.quantity);
    total += itemTotal;

    const toppings = item.toppings.map((t) => t.name).join(", ");

    const col = document.createElement("div");
    col.className = "col-md-6 col-lg-4"; // 👈 FIXED GRID

    col.innerHTML = `
      <div class="cart-card">

        <h4 class="pizza-title mb-2">${item.pizza_name}</h4>

        <p class="cart-meta"><strong>Crust:</strong> ${item.crust_name}</p>
        <p class="cart-meta"><strong>Toppings:</strong> ${toppings || "None"}</p>

        <p class="cart-meta"><strong>Qty:</strong> ${item.quantity}</p>

        <p class="price mt-2">৳ ${itemTotal}</p>

        <button class="btn btn-danger w-100 mt-3" onclick="removeItem(${i})">
          Remove
        </button>

      </div>
    `;

    list.appendChild(col);
  });

  totalEl.innerText = "Total: ৳ " + total;
}

window.removeItem = async (i) => {
  await fetch("/remove-from-cart", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ index: i }),
  });

  location.reload();
};
