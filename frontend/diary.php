<?php
require_once '../backend/config_diary.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user has written today's journal
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM journals WHERE user_id = ? AND DATE(created_date) = ?");
$stmt->execute([$user_id, $today]);
$todayJournal = $stmt->fetch();

// Get all journals for history (only for current user)
$stmt = $pdo->prepare("SELECT * FROM journals WHERE user_id = ? ORDER BY created_date DESC");
$stmt->execute([$user_id]);
$journals = $stmt->fetchAll();

// Emotion mapping - now with image paths
$emotionMap = [
    'happy' => '../src/journalAsset/emotions/happy.png',
    'sad' => '../src/journalAsset/emotions/cry.png',
    'angry' => '../src/journalAsset/emotions/angry.png',
    'anxious' => '../src/journalAsset/emotions/worried.png'
];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Diary Log</title>
    <style>
      body {
        margin: 0;
        padding: 0;
        background: #eaeeeb;
        font-family: "Manrope", sans-serif;
      }

      .main-content {
        margin-left: 200px; 
        margin-top: 40px; 
        padding: 40px;
        min-height: calc(100vh - 95px);
      }

      .emotion-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.3s;
      }
      .emotion-btn:hover {
        transform: scale(1.1);
      }
      .emotion-btn.selected {
        /*border-color: #86a789;*/
        background: rgba(255, 255, 255, 0.5);
      }
    </style>
  </head>
  <body>
    <?php require 'header.php'; ?>
    <?php require 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>

    <div class="main-content">
      <div class="max-w-5xl mx-auto p-8">
        <!-- Header Section -->
        <div class="rounded-t-lg p-8 text-center relative overflow-hidden">
          <div class="absolute inset-0">
            <img
              src="../src/banner/journal.gif"
              alt="banner"
              class="w-[1251px] h-[233px] [clip-path:inset(0_0_40%_0)]"
            />
          </div>
          <div class="relative z-10">
            <div class="flex justify-center pt-20">
              <img
                src="../src/emoji/heart.png"
                alt="heart"
                class="w-[70px] h-[70px]"
              />
            </div>
            <h1 class="text-4xl font-bold text-[#40350A] mb-2">
              Mindful Journal
            </h1>
            <p class="text-gray-600/50">
              This is where you release all your thoughts hihi, no we won't read
              what you write :>
            </p>
          </div>
        </div>

        <div class="rounded-b-3xl p-5">
          <!-- Today Section -->
          <div class="mb-8">
            <h2
              class="text-3xl font-semibold text-[#40350A] mb-4 border-b-2 border-gray-400 pb-2"
            >
              Today
            </h2>

            <?php if ($todayJournal): ?>
            <div class="rounded-lg p-6 text-center">
              <p class="text-gray-600 text-lg">
                You have done today journal! :D
              </p>
            </div>
            <?php else: ?>
            <div
              class="bg-[#b5c9a3] rounded-lg p-8 flex items-center justify-center cursor-pointer hover:bg-[#a8bc96] transition"
              onclick="openJournalForm()"
            >
              <div class="text-center">
                <!--tukar jadi image-->
                <div class="mb-2 flex justify-center">
                  <img
                    src="../src/ui/addNew.png"
                    alt="addNew"
                    class="w-[65px] h-[65px] opacity-15"
                  />
                </div>
                <!--meow3-->
                <p class="text-white/70">
                  You haven't start today journal, start by clicking +
                </p>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- History Section -->
          <div>
            <h2
              class="text-3xl font-semibold text-[#40350A] mb-4 border-b-2 border-gray-400 pb-2"
            >
              History
            </h2>

            <?php if (count($journals) >
            0): ?>
            <?php foreach ($journals as $journal): ?>
            <div
              class="rounded-lg p-4 mb-3 flex items-center justify-between transition cursor-pointer"
              onclick="viewJournal(<?php echo $journal['journal_id']; ?>)"
            >
              <div class="flex items-center gap-4">
                <span class="text-gray-800 font-medium text-xl"><?php echo $journal['journal_title']; ?></span>
                <span class="text-3xl">
                  <img src="<?php echo $emotionMap[$journal['emotion']] ?? '../src/journalAsset/emotions/happy.png'; ?>" alt="emotion" class="w-12 h-12 object-contain">
                </span>
              </div>
              <span class="text-gray-600"
                ><?php echo date('d/m/Y', strtotime($journal['created_date'])); ?></span
              >
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="rounded-lg p-6 text-center">
              <p class="text-gray-600">
                No journal entries yet. Start writing!
              </p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Journal Form Modal -->
      <div
        id="journalModal"
        class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-[#B9C5B4] rounded-lg p-8 w-full max-w-3xl mx-4">
          <h2 class="text-2xl font-semibold text-[#40350A] mb-4">
            Journal Title
          </h2>
          <input
            type="text"
            id="journalTitle"
            class="w-full p-3 rounded-lg border-2 border-gray-300 mb-4 focus:outline-none focus:border-[#86a789]"
            placeholder="Enter your journal title..."
          />

          <div class="mb-4">
            <label class="text-[#40350A] font-medium mb-2 block"
              >How are you feeling:</label
            >
            <div class="flex gap-2">
              <button
                type="button"
                class="emotion-btn"
                data-emotion="happy"
                onclick="selectEmotion('happy')"
              >
                <img
                  src="../src/journalAsset/emotions/happy.png"
                  alt="happy"
                  title="happy"
                />
              </button>
              <button
                type="button"
                class="emotion-btn"
                data-emotion="sad"
                onclick="selectEmotion('sad')"
              >
                <img
                  src="../src/journalAsset/emotions/cry.png"
                  alt="sad"
                  title="sad"
                />
              </button>
              <button
                type="button"
                class="emotion-btn"
                data-emotion="angry"
                onclick="selectEmotion('angry')"
              >
                <img
                  src="../src/journalAsset/emotions/angry.png"
                  alt="angry"
                  title="angry"
                />
              </button>
              <button
                type="button"
                class="emotion-btn"
                data-emotion="anxious"
                onclick="selectEmotion('anxious')"
              >
                <img
                  src="../src/journalAsset/emotions/worried.png"
                  alt="anxious"
                  title="anxious"
                />
              </button>
            </div>
          </div>

          <textarea
            id="journalText"
            rows="12"
            class="w-full p-4 rounded-lg border-2 border-gray-300 mb-4 focus:outline-none focus:border-[#86a789] resize-none"
            placeholder="Write your thoughts here..."
          ></textarea>

          <div class="flex gap-4 justify-end">
            <button
              onclick="closeJournalForm()"
              class="px-6 py-2 bg-[#A6B3A0] text-[#F5F2E9]/60 font-semibold rounded-lg hover:bg-[#7F8C79] transition"
            >
              Cancel
            </button>
            <button
              onclick="submitJournal()"
              class="px-6 py-2 bg-[#778970] text-[#F5F2E9] font-semibold rounded-lg hover:bg-[#5D6A58] transition"
            >
              Submit
            </button>
          </div>
        </div>
      </div>

      <!-- View Journal Modal -->
      <div
        id="viewModal"
        class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 relative"
        >
          <button
            onclick="closeViewModal()"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-3xl"
          >
            &times;
          </button>

          <div class="p-8">
            <div
              class="flex items-center justify-between mb-4 border-b-2 border-gray-200 pb-4"
            >
              <h2 id="viewTitle" class="text-2xl font-bold text-gray-800"></h2>
              <div class="flex items-center gap-4">
                <span id="viewEmotion" class="text-3xl"></span>
                <span id="viewDate" class="text-gray-600"></span>
              </div>
            </div>

            <div
              id="viewContent"
              class="whitespace-pre-wrap text-gray-700 leading-relaxed max-h-96 overflow-y-auto"
            ></div>
          </div>
        </div>
      </div>
    </div>
    <script src="diary.js"></script>
  </body>
</html>
