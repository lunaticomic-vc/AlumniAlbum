document.addEventListener("DOMContentLoaded", function () {
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
      .then((d) => { responseDiv.textContent = d.message; });
  });
});
