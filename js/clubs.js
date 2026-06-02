document.addEventListener("DOMContentLoaded", function () {
  loadClubs();
  document.getElementById("club-form").addEventListener("submit", function (e) {
    e.preventDefault();
    const body = {
      name:        document.getElementById("c-name").value.trim(),
      type:        document.getElementById("c-type").value,
      description: document.getElementById("c-description").value.trim()
    };
    fetch("./models/add_club.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    })
      .then((r) => r.json())
      .then((d) => {
        document.getElementById("club-response").textContent = d.message;
        if (d.status === "SUCCESS") {
          document.getElementById("club-form").reset();
          loadClubs();
        }
      });
  });
});

function loadClubs() {
  fetch("./models/get_clubs.php")
    .then((r) => r.json())
    .then((data) => {
      if (data.status === "SUCCESS") {
        const container = document.getElementById("clubs-container");
        container.innerHTML = "";
        data.clubs.forEach((c) => {
          const div = document.createElement("div");
          div.className = "club-card";
          div.innerHTML = `
            <h4>${escape(c.name)} <small>(${escape(c.type)})</small></h4>
            <p>${escape(c.description || "")}</p>
            <p>Членове: ${c.member_count} &middot; Създал: ${escape(c.owner_name || "-")}</p>
            <button onclick="window.location.href='./alumni.html'">Виж алумни</button>
          `;
          container.appendChild(div);
        });
      }
    });
}

function escape(s) {
  if (s === null || s === undefined) return "";
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
