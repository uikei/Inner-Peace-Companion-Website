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

        // Store current journal ID on the modal for delete/edit
        document.getElementById("viewModal").dataset.journalId = journalId;

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
  document.getElementById("kebabMenu").classList.remove("open");
}

/* ── Kebab (three-dot) menu ─────────────────────────────── */
function toggleKebabMenu(event) {
  event.stopPropagation();
  document.getElementById("kebabMenu").classList.toggle("open");
}

// Close kebab when clicking anywhere outside
document.addEventListener("click", function () {
  const menu = document.getElementById("kebabMenu");
  if (menu) menu.classList.remove("open");
});

/* ── Delete ─────────────────────────────────────────────── */
function deleteJournal() {
  const journalId = document.getElementById("viewModal").dataset.journalId;
  if (!journalId) return;

  if (!confirm("Are you sure you want to delete this journal entry? This cannot be undone.")) return;

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", journalId);

  fetch("../backend/diary_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        closeViewModal();
        location.reload();
      } else {
        alert("Error deleting journal: " + data.message);
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      alert("Error deleting journal");
    });
}

/* ── Edit ───────────────────────────────────────────────── */
let selectedEditEmotion = "";

function openEditFromView() {
  // Close kebab
  document.getElementById("kebabMenu").classList.remove("open");

  // Pre-populate edit modal from current view modal data
  document.getElementById("editTitle").value =
    document.getElementById("viewTitle").textContent;
  document.getElementById("editText").value =
    document.getElementById("viewContent").textContent;

  // Try to determine current emotion from the img alt
  const emotionImgEl = document.querySelector("#viewEmotion img");
  const currentEmotion = emotionImgEl ? emotionImgEl.alt : "";
  selectEditEmotion(currentEmotion);

  // Show edit modal
  document.getElementById("editModal").classList.remove("hidden");
}

function selectEditEmotion(emotion) {
  selectedEditEmotion = emotion;
  document.querySelectorAll("[data-edit-emotion]").forEach((btn) => {
    btn.classList.remove("selected");
  });
  const target = document.querySelector(`[data-edit-emotion="${emotion}"]`);
  if (target) target.classList.add("selected");
}

function closeEditModal() {
  document.getElementById("editModal").classList.add("hidden");
  selectedEditEmotion = "";
  document.querySelectorAll("[data-edit-emotion]").forEach((btn) =>
    btn.classList.remove("selected")
  );
}

function submitEdit() {
  const journalId = document.getElementById("viewModal").dataset.journalId;
  const title = document.getElementById("editTitle").value.trim();
  const text  = document.getElementById("editText").value.trim();

  if (!title || !text || !selectedEditEmotion) {
    alert("Please fill in all fields and select an emotion!");
    return;
  }

  const formData = new FormData();
  formData.append("action",  "update");
  formData.append("id",      journalId);
  formData.append("title",   title);
  formData.append("emotion", selectedEditEmotion);
  formData.append("text",    text);

  fetch("../backend/diary_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        closeEditModal();
        closeViewModal();
        location.reload();
      } else {
        alert("Error updating journal: " + data.message);
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      alert("Error updating journal");
    });
}
