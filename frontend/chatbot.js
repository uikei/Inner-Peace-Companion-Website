// Chatbot functionality
const chatToggle = document.getElementById("chatbot-toggle");
const chatContainer = document.getElementById("chatbot-container");
const chatIcon = document.getElementById("chat-icon");
const closeIcon = document.getElementById("close-icon");
const headerCloseBtn = document.getElementById("chatbot-header-close");
const chatForm = document.getElementById("chat-form");
const chatInput = document.getElementById("chat-input");
const chatMessages = document.getElementById("chatbot-messages");
const sendButton = document.getElementById("send-button");

let isOpen = false;

// Toggle chatbot function
function toggleChatbot() {
  isOpen = !isOpen;

  if (isOpen) {
    chatContainer.classList.remove("hidden");
    chatContainer.classList.add("flex");
    chatToggle.classList.add("active");
    chatInput.focus();
    loadChatHistory();
  } else {
    chatContainer.classList.add("hidden");
    chatContainer.classList.remove("flex");
    chatToggle.classList.remove("active");
  }
}

// Event listeners for both toggle button and header close button
chatToggle.addEventListener("click", toggleChatbot);
headerCloseBtn.addEventListener("click", toggleChatbot);

// Send message
chatForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const message = chatInput.value.trim();
  if (!message) return;

  // Add user message to chat
  addMessage(message, "user");
  chatInput.value = "";

  // Disable input while processing
  setInputState(false);

  // Show typing indicator
  const typingId = showTypingIndicator();

  try {
    const response = await fetch("../backend/api_chatbot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "send_message",
        message: message,
      }),
    });

    const data = await response.json();

    // Remove typing indicator
    removeTypingIndicator(typingId);

    if (data.success) {
      addMessage(data.message, "bot");
    } else {
      addMessage(
        "I apologize, but I'm having trouble connecting right now. Please try again in a moment.",
        "bot"
      );
      console.error("API Error:", data.error);
    }
  } catch (error) {
    removeTypingIndicator(typingId);
    addMessage(
      "I'm having trouble connecting. Please check your internet connection and try again.",
      "bot"
    );
    console.error("Network Error:", error);
  } finally {
    setInputState(true);
    chatInput.focus();
  }
});

// Add message to chat
function addMessage(text, type) {
  const messageDiv = document.createElement("div");
  messageDiv.className = "flex gap-3 message-enter";

  if (type === "user") {
    messageDiv.classList.add("justify-end");
    messageDiv.innerHTML = `
            <div class="bg-[#D9D9D9] text-black font-medium rounded-2xl rounded-tr-sm p-4 shadow-md max-w-xs">
                <p class="text-sm">${escapeHtml(text)}</p>
            </div>
        `;
  } else {
    messageDiv.innerHTML = `
            <div class="w-10 h-10 bg-[#A2BA95]/20 rounded-full flex items-center justify-center flex-shrink-0">
                <img src="../src/ui/leaf.png" alt="chatbot logo" class="w-5 h-5">
            </div>
            <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-md max-w-xs">
                <p class="text-sm text-gray-800 font-medium whitespace-pre-line">${escapeHtml(
                  text
                )}</p>
            </div>
        `;
  }

  chatMessages.appendChild(messageDiv);
  scrollToBottom();
}

// Show typing indicator
function showTypingIndicator() {
  const typingDiv = document.createElement("div");
  const id = "typing-" + Date.now();
  typingDiv.id = id;
  typingDiv.className = "flex gap-3 message-enter";
  typingDiv.innerHTML = `
        <div class="w-10 h-10 bg-[#A2BA95]/20 rounded-full flex items-center justify-center flex-shrink-0">
            <img src="../src/ui/leaf.png" alt="chatbot logo" class="w-5 h-5">
        </div>
        <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-md">
            <div class="typing-indicator flex gap-1">
                <span class="w-2 h-2 bg-[#B9C5B4] rounded-full"></span>
                <span class="w-2 h-2 bg-[#B9C5B4] rounded-full"></span>
                <span class="w-2 h-2 bg-[#B9C5B4] rounded-full"></span>
            </div>
        </div>
    `;
  chatMessages.appendChild(typingDiv);
  scrollToBottom();
  return id;
}

// Remove typing indicator
function removeTypingIndicator(id) {
  const element = document.getElementById(id);
  if (element) {
    element.remove();
  }
}

// Load chat history
async function loadChatHistory() {
  try {
    const response = await fetch("../backend/api_chatbot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "get_history",
      }),
    });

    const data = await response.json();

    if (data.success && data.history.length > 0) {
      // Clear current messages except welcome message
      chatMessages.innerHTML = "";

      // Add history messages
      data.history.forEach((msg) => {
        addMessage(msg.message_content, msg.message_type);
      });
    }
  } catch (error) {
    console.error("Failed to load history:", error);
  }
}

// Set input state
function setInputState(enabled) {
  chatInput.disabled = !enabled;
  sendButton.disabled = !enabled;
}

// Scroll to bottom
function scrollToBottom() {
  setTimeout(() => {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }, 100);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Allow Enter to send, Shift+Enter for new line
chatInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    chatForm.dispatchEvent(new Event("submit"));
  }
});
