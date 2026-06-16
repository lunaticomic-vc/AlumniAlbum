document.addEventListener("DOMContentLoaded", function () {
  loadOrders();
  document.getElementById("print-form").addEventListener("submit", function (e) {
    e.preventDefault();
    const responseDiv = document.getElementById("response-message");
    responseDiv.textContent = "";

    const raw = document.getElementById("photo_ids").value.trim();
    const photoIds = raw
      ? raw.split(",").map((s) => parseInt(s.trim(), 10)).filter((n) => !isNaN(n))
      : [];

    const body = {
      product_type: document.getElementById("product_type").value,
      photo_ids:    photoIds,
      quantity:     parseInt(document.getElementById("quantity").value, 10) || 1,
      note:         document.getElementById("note").value.trim()
    };

    fetch("./models/add_print_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    })
      .then((r) => r.json())
      .then((d) => {
        responseDiv.textContent = d.message;
        if (d.status === "SUCCESS") {
          document.getElementById("print-form").reset();
          loadOrders();
        }
      });
  });
});

function loadOrders() {
  fetch("./models/get_print_orders.php")
    .then((r) => r.json())
    .then((data) => {
      const container = document.getElementById("orders-container");
      container.innerHTML = "";
      if (data.status !== "SUCCESS" || data.orders.length === 0) {
        container.innerHTML = "<p>Нямаш поръчки.</p>";
        return;
      }
      data.orders.forEach((o) => {
        const div = document.createElement("div");
        div.className = "order-card";
        div.innerHTML = `
          <p><b>${escapeHtml(o.product_type)}</b> &times; ${o.quantity}
             — статус: ${escapeHtml(o.status)}</p>
          <p><small>Снимки: ${escapeHtml(o.photo_ids || "[]")}</small></p>
          <p><small>${escapeHtml(o.note || "")}</small></p>
          <button class="delete-btn" onclick="deleteOrder(${o.id})">Изтрий</button>
        `;
        container.appendChild(div);
      });
    });
}

function deleteOrder(orderId) {
  if (!confirm("Сигурен ли си, че искаш да изтриеш поръчката?")) return;
  fetch("./models/delete_print_order.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ order_id: orderId })
  })
    .then((r) => r.json())
    .then((d) => {
      alert(d.message);
      if (d.status === "SUCCESS") loadOrders();
    });
}

function escapeHtml(s) {
  if (s === null || s === undefined) return "";
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
