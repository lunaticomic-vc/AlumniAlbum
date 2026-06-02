document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("upload-form");
  const responseDiv = document.getElementById("response-message");

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    responseDiv.textContent = "";
    const formData = new FormData(form);

    fetch("./models/upload_photo.php", { method: "POST", body: formData })
      .then((r) => r.json())
      .then((d) => {
        responseDiv.textContent = d.message;
        if (d.status === "SUCCESS") {
          setTimeout(() => { window.location.href = "./photos.html"; }, 1500);
        }
      })
      .catch((err) => { responseDiv.textContent = "Грешка: " + err.message; });
  });
});
