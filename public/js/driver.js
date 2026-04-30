export async function initDriver() {
  const res = await fetch("/driver/orders");
  const data = await res.json();

  const container = document.getElementById("driver-orders");

  if (!data.length) {
    container.innerHTML = "<p>No assigned orders yet</p>";
    return;
  }

  container.innerHTML = data
    .map((o) => {
      const total = Number(o.total_amount) || 0;

      return `
      <div class="col-sm-6 col-md-4 col-lg-3">

        <div class="cart-card h-100">

          <strong>Order #${o.order_id}</strong><br>
          Total: ৳ ${total}<br>
          Status: ${o.delivery_status}<br><br>

          <div class="status-btn-group">

            <button class="btn w-100 status-btn 
              ${o.delivery_status === "assigned" ? "btn-success" : "btn-outline-secondary"}"
              data-order="${o.order_id}" data-status="assigned">
              Assigned
            </button>

            <button class="btn w-100 status-btn 
              ${o.delivery_status === "out_for_delivery" ? "btn-success" : "btn-outline-secondary"}"
              data-order="${o.order_id}" data-status="out_for_delivery">
              Out for Delivery
            </button>

            <button class="btn w-100 status-btn 
              ${o.delivery_status === "delivered" ? "btn-success" : "btn-outline-secondary"}"
              data-order="${o.order_id}" data-status="delivered">
              Delivered
            </button>

          </div>

        </div>

      </div>
    `;
    })
    .join("");

  document.querySelectorAll(".status-btn").forEach((btn) => {
    btn.onclick = async () => {
      const order_id = btn.dataset.order;
      const status = btn.dataset.status;

      await fetch("/driver/update-status", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id, status }),
      });

      location.reload();
    };
  });
}
