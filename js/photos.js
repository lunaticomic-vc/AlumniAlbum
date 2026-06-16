document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("alumni_id")) {
    document.getElementById("f-alumni-id").value = urlParams.get("alumni_id");
  }
  document.getElementById("f-apply").addEventListener("click", loadPhotos);
  loadPhotos();
});

function loadPhotos() {
  const alumniId  = document.getElementById("f-alumni-id").value.trim();
  const sessionId = document.getElementById("f-session-id").value.trim();
  const params = new URLSearchParams();
  if (alumniId)  params.set("alumni_id", alumniId);
  if (sessionId) params.set("session_id", sessionId);

  fetch("./models/get_photos.php?" + params.toString())
    .then((r) => r.json())
    .then((data) => {
      const container = document.getElementById("photos-container");
      container.innerHTML = "";
      if (data.status !== "SUCCESS" || data.photos.length === 0) {
        container.innerHTML = "<p>Няма снимки.</p>";
        return;
      }
      data.photos.forEach((p) => {
        const div = document.createElement("div");
        div.className = "photo-card";
        div.innerHTML = `
          <img src="./${p.image_dir}" alt="${escape(p.name)}" onerror="this.style.display='none'" />
          <p>${escape(p.caption || p.name)}</p>
          <small>Източник: ${escape(p.source)} &middot; Качи: ${escape(p.uploader_name || "-")}</small>
          <button class="delete-btn" onclick="deletePhoto(${p.id})">Изтрий</button>
        `;
        container.appendChild(div);
      });
    });
}

function deletePhoto(photoId) {
  if (!confirm("Сигурен ли си, че искаш да изтриеш снимката?")) return;
  fetch("./models/delete_photo.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ photo_id: photoId })
  })
    .then((r) => r.json())
    .then((d) => {
      alert(d.message);
      if (d.status === "SUCCESS") loadPhotos();
    });
}

function escape(s) {
  if (s === null || s === undefined) return "";
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
