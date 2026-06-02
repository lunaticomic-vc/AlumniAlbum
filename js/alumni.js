document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("f-apply").addEventListener("click", loadAlumni);
  loadAlumni();
});

function loadAlumni() {
  const params = new URLSearchParams();
  const q         = document.getElementById("f-q").value.trim();
  const specialty = document.getElementById("f-specialty").value.trim();
  const stream    = document.getElementById("f-stream").value.trim();
  const year      = document.getElementById("f-year").value.trim();
  const degree    = document.getElementById("f-degree").value;

  if (q)         params.set("q", q);
  if (specialty) params.set("specialty", specialty);
  if (stream)    params.set("stream", stream);
  if (year)      params.set("graduation_year", year);
  if (degree)    params.set("degree", degree);

  fetch("./models/get_alumni.php?" + params.toString())
    .then((r) => r.json())
    .then((data) => {
      if (data.status === "SUCCESS") {
        render(data.alumni);
      } else {
        console.error(data.message);
      }
    });
}

function render(alumni) {
  const container = document.getElementById("alumni-container");
  container.innerHTML = "";

  if (alumni.length === 0) {
    container.innerHTML = "<p>Няма намерени алумни.</p>";
    return;
  }

  alumni.forEach((a) => {
    const card = document.createElement("div");
    card.className = "alumni-card";

    const validatedBadge = a.validated == 1
      ? '<span class="badge validated">Валидиран</span>'
      : '<span class="badge pending">Невалидиран</span>';

    card.innerHTML = `
      <h3>${escapeHtml(a.fullname)} ${validatedBadge}</h3>
      <p><b>Факултетен №:</b> ${escapeHtml(a.fn)}</p>
      <p><b>Специалност:</b> ${escapeHtml(a.specialty || "-")}</p>
      <p><b>Поток:</b> ${escapeHtml(a.stream || "-")}</p>
      <p><b>Випуск:</b> ${a.graduation_year || "-"} (${a.degree})</p>
      <p><b>Позиция:</b> ${escapeHtml(a.position || "-")}, ${escapeHtml(a.location || "-")}</p>
      <button onclick="validateAlumni(${a.id})">Валидирай</button>
      <button onclick="viewPhotos(${a.id})">Снимки</button>
    `;
    container.appendChild(card);
  });
}

function validateAlumni(alumniId) {
  const note = prompt("Кратък текст / откъде познаваш този алумни:");
  if (note === null) return;

  fetch("./models/validate_alumni.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ alumni_id: alumniId, evidence_text: note, status: "approved" })
  })
    .then((r) => r.json())
    .then((d) => alert(d.message))
    .then(loadAlumni);
}

function viewPhotos(alumniId) {
  window.location.href = "./photos.html?alumni_id=" + alumniId;
}

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
