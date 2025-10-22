<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }
        #chatbot-container {
            transition: all 0.3s ease-in-out;
        }
        #chatbot-messages {
            scroll-behavior: smooth;
        }
        .message-enter {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .typing-indicator span {
            animation: blink 1.4s infinite;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes blink {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        #chatbot-toggle {
            position: fixed;
            top: 50%;
            right: 15px;
            width: 80px;
            height: 80px;
            background-image: url('../src/ui/buttonCB.svg');
            background-size: 65px;
            background-position: center;
            background-repeat: no-repeat;
            border: none;
            cursor: pointer;
            transition: transform 0.3s ease;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translateY(-50%);
        }

        #chatbot-toggle:hover {
            transform: translateY(-50%) scale(1.05);
        }

        /* Chat bubble icon centered inside the button */
        #chat-icon {
            width: 32px;
            height: 32px;
            background-image: url('../src/ui/chatBubble.png');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            transition: opacity 0.3s ease;
        }

        

       
    </style>
</head>
<body>
    
    <!-- Chatbot Toggle Button -->
    <button id="chatbot-toggle">
        <div id="chat-icon"></div>
    </button>

    <!-- Chatbot Container -->
    <div id="chatbot-container" class="fixed bottom-24 right-6 w-96 bg-[#EAEEEB] rounded-2xl shadow-2xl hidden flex-col z-50" style="height: 550px;">
        <!-- Header -->
        <div class="bg-[#A2BA95] text-[#40350A] px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <img src="../src/ui/leaf.png" alt="chatbot logo" class="w-7 h-7">
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Therapy Assistant</h3>
                    <p class="text-xs text-[#40350A] flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>
                        Here to listen
                    </p>
                </div>
            </div>
            <div class="w-[30px] h-[30px] bg-black/15 rounded-full flex items-center justify-center backdrop-blur-sm cursor-pointer hover:bg-black/25 transition-all" id="chatbot-header-close">
                <img src="../src/ui/closeBtn.png" alt="close" class="w-[23px] h-[23px]">
            </div>
        </div>

        <!-- Messages -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#EAEEEB]">
            <div class="flex gap-3 message-enter">
                <div class="w-10 h-10 bg-[#A2BA95]/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <img src="../src/ui/leaf.png" alt="chatbot logo" class="w-5 h-5">
                </div>
                <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-md max-w-xs">
                    <p class="text-sm text-gray-800 font-medium">Hello! I'm here to provide a safe, supportive space for you. How are you feeling today?</p>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="p-4 rounded-b-2xl">
            <form id="chat-form" class="flex gap-2">
                <input 
                    type="text" 
                    id="chat-input" 
                    placeholder="Share your thoughts..." 
                    class="bg-[#B7B7B7] placeholder-white/80 text-white flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-transparent"
                    autocomplete="off"
                >
                <button 
                    type="submit" 
                    class="bg-[#B7B7B7] text-white px-5 py-3 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg"
                    id="send-button"
                >
                    <img src="../src/ui/sendBtn.png" alt="send button" class="w-5 h-5">
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 text-center">Your conversation is confidential</p>
        </div>
    </div>

    <script src="chatbot.js"></script>
</body>
</html>