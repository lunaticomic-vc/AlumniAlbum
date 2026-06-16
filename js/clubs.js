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
            <button class="delete-btn" onclick="deleteClub(${c.id})">Изтрий</button>
          `;
          container.appendChild(div);
        });
      }
    });
}

function deleteClub(clubId) {
  if (!confirm("Сигурен ли си, че искаш да изтриеш клуба?")) return;
  fetch("./models/delete_club.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ club_id: clubId })
  })
    .then((r) => r.json())
    .then((d) => {
      alert(d.message);
      if (d.status === "SUCCESS") loadClubs();
    });
}

function escape(s) {
  if (s === null || s === undefined) return "";
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
