(function () {
  const inputs = document.querySelectorAll("input");
  const form = document.getElementById("login-form");
  const responseDiv = document.getElementById("response-message");
  const toRegistrationBtn = document.getElementById("to-registration-btn");

  toRegistrationBtn.addEventListener("click", () => {
    window.location.href = "./registration.html";
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    responseDiv.classList.add("no-show");
    responseDiv.innerHTML = null;

    let data = {};
    inputs.forEach((input) => { data[input.name] = input.value; });

    checkLoginData(data)
      .then((responseMessage) => {
        if (responseMessage["status"] === "ERROR") {
          throw new Error(responseMessage["message"]);
        } else {
          window.location.replace("./homepage.html");
        }
      })
      .catch((errorMessage) => {
        showError(responseDiv, errorMessage);
      });
  });
})();

function checkLoginData(data) {
  return fetch("./models/login_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  }).then((r) => r.json());
}

function showError(div, response) {
  const message = document.createElement("p");
  message.textContent = response;
  div.appendChild(message);
  div.classList.remove("no-show");
}
