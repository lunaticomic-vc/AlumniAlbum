(function () {
  const form = document.getElementById("registration-form");
  const inputs = document.querySelectorAll("input, select");
  const responseDiv = document.getElementById("response-message");

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    responseDiv.classList.remove("error");
    responseDiv.innerHTML = null;

    let data = {};
    inputs.forEach((input) => { data[input.name] = input.value; });

    sendFormData(data)
      .then((responseMessage) => {
        if (responseMessage["status"] === "ERROR") {
          throw new Error(responseMessage["message"]);
        } else {
          window.location.replace("./homepage.html");
        }
      })
      .catch((errorMsg) => {
        showError(responseDiv, errorMsg);
      });
  });
})();

function sendFormData(data) {
  return fetch("./models/register_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  }).then((r) => r.json());
}

function showError(div, message) {
  const p = document.createElement("p");
  p.textContent = message;
  div.classList.add("error");
  div.classList.remove("no-display");
  div.appendChild(p);
}
