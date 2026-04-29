export async function initCart() {
  const res = await fetch("/get-cart");
  const data = await res.json();

  const list = document.getElementById("cart-list");
  let total = 0;

  data.forEach((item, i) => {
    total += item.price * item.quantity;

    const li = document.createElement("li");
    li.innerHTML = `
      Pizza ${item.pizza_id} x${item.quantity} - ৳ ${item.price}
      <button onclick="removeItem(${i})">X</button>
    `;
    list.appendChild(li);
  });

  document.getElementById("total").innerText = "Total: ৳ " + total;
}

window.removeItem = async (i) => {
  await fetch("/remove-from-cart", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ index: i }),
  });
  location.reload();
};
