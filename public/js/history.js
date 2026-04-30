const statusMap = {
  pending: { text: "🟡 Pending", class: "bg-warning text-dark" },
  assigned: { text: "👨‍🍳 Assigned", class: "bg-info text-dark" },
  out_for_delivery: { text: "🚚 On the way", class: "bg-primary" },
  delivered: { text: "✅ Delivered", class: "bg-success" },
};

export async function initHistory() {
  const res = await fetch("/history-data");
  const data = await res.json();

  const container = document.getElementById("history-list");

  if (!data.length) {
    container.innerHTML = "<p>No past orders</p>";
    return;
  }

  container.innerHTML = data
    .map((o) => {
      const status = statusMap[o.delivery_status] || statusMap["pending"];

      const itemsHTML = o.items
        .map(
          (item) => `
    <div class="mb-2">
      🍕 <strong>${item.pizza_name}</strong> (x${item.quantity})<br>
      <small>Crust: ${item.crust_name}</small>
    </div>
  `,
        )
        .join("");

      return `
    <div class="col-md-4">
      <div class="cart-card">

        <h5>Order #${o.order_id}</h5>

        <p>
          <span class="badge rounded-pill ${status.class}">
            ${status.text}
          </span>
        </p>

        <div class="mt-2">${itemsHTML}</div>

        <hr>

        <p>Total: ৳ ${o.total_amount}</p>
        <p>Date: ${new Date(o.created_at).toLocaleString()}</p>

      </div>
    </div>
  `;
    })
    .join("");
}
