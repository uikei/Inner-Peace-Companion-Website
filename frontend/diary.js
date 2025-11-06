let selectedEmotion = "";

function openJournalForm() {
  document.getElementById("journalModal").classList.remove("hidden");
}

function closeJournalForm() {
  document.getElementById("journalModal").classList.add("hidden");
  document.getElementById("journalTitle").value = "";
  document.getElementById("journalText").value = "";
  selectedEmotion = "";
  document
    .querySelectorAll(".emotion-btn")
    .forEach((btn) => btn.classList.remove("selected"));
}

function selectEmotion(emotion) {
  selectedEmotion = emotion;
  document.querySelectorAll(".emotion-btn").forEach((btn) => {
    btn.classList.remove("selected");
  });
  document
    .querySelector(`[data-emotion="${emotion}"]`)
    .classList.add("selected");
}

function submitJournal() {
  const title = document.getElementById("journalTitle").value;
  const text = document.getElementById("journalText").value;

  if (!title || !text || !selectedEmotion) {
    alert("Please fill in all fields and select an emotion!");
    return;
  }

  const formData = new FormData();
  formData.append("action", "create");
  formData.append("title", title);
  formData.append("emotion", selectedEmotion);
  formData.append("text", text);

  fetch("../backend/diary_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error saving journal: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error saving journal");
    });
}

function viewJournal(journalId) {
  fetch(`../backend/diary_handler.php?action=get&id=${journalId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const emotionMap = {
          happy: "../src/journalAsset/emotions/happy.png",
          sad: "../src/journalAsset/emotions/cry.png",
          angry: "../src/journalAsset/emotions/angry.png",
          anxious: "../src/journalAsset/emotions/worried.png",
        };

        // Set title
        document.getElementById("viewTitle").textContent =
          data.journal.journal_title;

        const emotionImg = document.createElement("img");
        emotionImg.src =
          emotionMap[data.journal.emotion] ||
          "../src/journalAsset/emotions/happy.png";
        emotionImg.alt = data.journal.emotion;
        emotionImg.className = "w-12 h-12 object-contain";

        const emotionContainer = document.getElementById("viewEmotion");
        emotionContainer.innerHTML = ""; // Clear previous content
        emotionContainer.appendChild(emotionImg); // Add image

        // Set date
        document.getElementById("viewDate").textContent = new Date(
          data.journal.created_date
        ).toLocaleDateString("en-GB");

        // Set content
        document.getElementById("viewContent").textContent =
          data.journal.diary_text;

        // Show modal
        document.getElementById("viewModal").classList.remove("hidden");
      } else {
        alert("Error loading journal");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error loading journal");
    });
}

function closeViewModal() {
  document.getElementById("viewModal").classList.add("hidden");
}
