export async function initMenu() {
  const [pizzas, crusts, toppings] = await Promise.all([
    fetch("/get-pizzas").then((r) => r.json()),
    fetch("/get-crusts").then((r) => r.json()),
    fetch("/get-toppings").then((r) => r.json()),
  ]);

  const container = document.getElementById("pizza-list");

  pizzas.forEach((p) => {
    const div = document.createElement("div");

    div.innerHTML = `
      <h3>${p.name}</h3>
      <p>${p.description}</p>
      <p>৳ ${p.base_price}</p>

      <select class="crust">
        ${crusts
          .map(
            (c) => `<option value="${c.crust_id}" data-price="${c.extra_price}">
          ${c.crust_name} (+${c.extra_price})
        </option>`,
          )
          .join("")}
      </select>

      ${toppings
        .map(
          (t) => `
        <label>
          <input type="checkbox" value="${t.topping_id}" data-price="${t.extra_price}">
          ${t.topping_name}
        </label>
      `,
        )
        .join("")}

      <input type="number" value="1" min="1" class="qty">

      <button class="add">Add</button>
    `;

    div.querySelector(".add").onclick = async () => {
      const crust = div.querySelector(".crust");
      const crust_price = Number(crust.selectedOptions[0].dataset.price);

      const checked = [...div.querySelectorAll("input[type=checkbox]:checked")];
      const topping_ids = checked.map((c) => c.value);
      const topping_price = checked.reduce(
        (s, c) => s + Number(c.dataset.price),
        0,
      );

      const qty = Number(div.querySelector(".qty").value);

      const price = p.base_price + crust_price + topping_price;

      await fetch("/add-to-cart", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          pizza_id: p.pizza_id,
          crust_id: crust.value,
          toppings: topping_ids,
          quantity: qty,
          price,
        }),
      });

      alert("Added 🍕");
    };

    container.appendChild(div);
  });
}
