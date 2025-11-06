//let currentUserId = '';

function startQuestionnaire() {
  /*
            const userId = document.getElementById('userId').value.trim();
            if (!userId) {
                document.getElementById('startError').textContent = 'Please enter a User ID';
                return;
            }
            currentUserId = userId;
            */
  showSection("phq9Section");
}

function showSection(sectionId) {
  document.querySelectorAll(".form-section").forEach((section) => {
    section.classList.add("hidden");
  });
  document.getElementById(sectionId).classList.remove("hidden");
}

// PHQ-9 Form submission
document
  .getElementById("phq9Form")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const answers = {};
    let allAnswered = true;

    for (let i = 1; i <= 9; i++) {
      const value = formData.get(`q${i}`);
      if (value === null) {
        allAnswered = false;
        break;
      }
      answers[`q${i}`] = parseInt(value);
    }

    if (!allAnswered) {
      document.getElementById("phq9Error").textContent =
        "Please answer all questions";
      return;
    }

    //answers.user_id = currentUserId;

    try {
      const response = await fetch("../backend/submitPHQ.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(answers),
      });

      const result = await response.json();

      if (result.success) {
        showSection("gad7Section");
      } else {
        document.getElementById("phq9Error").textContent =
          result.message || "Error submitting form";
      }
    } catch (error) {
      document.getElementById("phq9Error").textContent =
        "Error: " + error.message;
    }
  });

// GAD-7 Form submission
document
  .getElementById("gad7Form")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const answers = {};
    let allAnswered = true;

    for (let i = 1; i <= 7; i++) {
      const value = formData.get(`q${i}`);
      if (value === null) {
        allAnswered = false;
        break;
      }
      answers[`q${i}`] = parseInt(value);
    }

    if (!allAnswered) {
      document.getElementById("gad7Error").textContent =
        "Please answer all questions";
      return;
    }

    // answers.user_id = currentUserId;

    try {
      const response = await fetch("../backend/submitGAD.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(answers),
      });

      const result = await response.json();

      if (result.success) {
        showSection("successSection");
        setTimeout(() => {
          // redirect user to home
          window.location.href = "home.php";
        }, 2000);
      } else {
        document.getElementById("gad7Error").textContent =
          result.message || "Error submitting form";
      }
    } catch (error) {
      document.getElementById("gad7Error").textContent =
        "Error: " + error.message;
    }
  });

// Update progress as user answers PHQ-9
document.getElementById("phq9Form").addEventListener("change", function () {
  const total = 9;
  let answered = 0;
  for (let i = 1; i <= total; i++) {
    if (document.querySelector(`input[name="q${i}"]:checked`)) {
      answered++;
    }
  }
  const percent = (answered / total) * 100;
  document.getElementById("phq9Progress").style.width = percent + "%";
});
