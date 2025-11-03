<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user data from database
// Assuming you have user_id stored in session
$user_id = $_SESSION['user_id'] ?? null;
$user_name = '';
$user_image = ''; // Default avatar

if ($user_id) {
    // Include your database connection
    // require_once 'config/db_connect.php';

    // Example query (adjust based on your database structure)
    // $query = "SELECT name, profile_image FROM users WHERE id = ?";
    // $stmt = $conn->prepare($query);
    // $stmt->bind_param("i", $user_id);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $user = $result->fetch_assoc();
    // $user_name = $user['name'];
    // $user_image = $user['profile_image'];
}

$profile_pic = '../src/ui/icon.jpeg'; // Fallback

if ($user_id && !empty($user_image)) {
    // Check if custom image exists
    if (file_exists($user_image)) {
        $profile_pic = $user_image;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }

        /* Ring animation for phone button */
        @keyframes ring {
            0% {
                transform: rotate(0deg) scale(1);
            }

            10% {
                transform: rotate(15deg) scale(1.05);
            }

            20% {
                transform: rotate(-15deg) scale(1.1);
            }

            30% {
                transform: rotate(15deg) scale(1.05);
            }

            40% {
                transform: rotate(-15deg) scale(1.1);
            }

            50% {
                transform: rotate(0deg) scale(1);
            }

            100% {
                transform: rotate(0deg) scale(1);
            }
        }

        .ring-animation {
            animation: ring 2s ease-in-out infinite;
        }

        /* Phone button with blur effect */
        .phone-btn-container {
            position: relative;
            width: 50px;
            height: 50px;
        }

        .phone-btn-blur {
            position: absolute;
            top: 50%;
            /* Center it */
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 100%;
            background-image: url('../src/ui/emergencyCall.svg');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            transform: translate(-50%, -50%) scale(1.15);
            transform-origin: center;
            z-index: 1;
        }

        .phone-btn-icon {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: transform 0.2s;
        }

        .phone-btn-icon:hover {
            transform: scale(1.1);
        }

        .phone-btn-icon img {
            width: 30px;
            height: 30px;
            filter: none;
            /* Ensure icon stays sharp */
        }

        /* Profile button with blur effect */
        .profile-btn-container {
            position: relative;
            width: 50px;
            height: 50px;
        }

        .profile-btn-blur {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 60%;
            background: rgba(37, 30, 30, 0.27);
            border: 0.5px solid #000;
            filter: blur(2px);
            z-index: 1;
        }

        .profile-btn-icon {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: transform 0.2s;
            overflow: hidden;
        }

        .profile-btn-icon:hover {
            transform: scale(1.05);
        }

        .profile-btn-icon img,
        .profile-btn-icon svg {
            filter: none;
            /* Ensure image/icon stays sharp */
        }

        /* Modal styling */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
        }

        .modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 16px;
        }

        .modal-message {
            font-size: 16px;
            color: #374151;
            margin-bottom: 24px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-continue {
            background: #dc2626;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-continue:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #4b5563;
            transform: scale(1.05);
        }

        /* Dropdown menu styling */
        .profile-dropdown {
            display: none;
            position: absolute;
            top: 96px;
            /* Position below the header */
            right: 2px;
            /* Move more to the right */
            width: 160px;
            height: 180px;
            border: 1px solid #40350A;
            background: #8D9E82;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            flex-shrink: 0;
        }

        .profile-dropdown.show {
            display: block;
            animation: fadeIn 0.2s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            color: #F5F2E9;
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.2s;
            /*border-bottom: 1px solid rgba(0, 0, 0, 0.1);*/
        }

        .dropdown-item.logout-item {
            border-top: 1px solid #000;
            /* Black line before logout */
        }

        .dropdown-item:hover {
            background: rgba(0, 0, 0, 0.05);
            /*width: 179px;*/
            /*height: 43px;*/
            /*border-radius: 34px;*/
            /*background: #688F4E;*/
            /*box-shadow: 0 4px 7.1px 0 rgba(0, 0, 0, 0.25);*/

        }

        .dropdown-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <header class="w-full bg-[#889B7E] fixed top-0 left-0 z-40" style="height: 95px;">
        <div class="max-w-[1727px] h-full mx-auto px-8 flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center">
                <a href="home.php">
                    <img src="../src/image/logo/logo.png" alt="Logo" class="h-[140px] w-auto object-contain">
                </a>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-8">
                <!-- Phone Button with Ring Animation and Blur Effect -->
                <div class="phone-btn-container ring-animation" onclick="handlePhoneClick()">
                    <div class="phone-btn-blur"></div>
                    <button id="phoneBtn" class="phone-btn-icon">
                        <img src="../src/ui/phone.png" alt="Phone">
                    </button>
                </div>
                <!-- Profile Button with Blur Effect -->
                <div class="profile-btn-container" onclick="toggleProfileDropdown()">
                    <div class="profile-btn-blur"></div>
                    <button id="profileBtn" class="profile-btn-icon">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile"
                            class="w-[50px] h-[50px] object-cover rounded-full"
                            onerror="this.src='../src/ui/default-avatar.png'"> <!-- Fallback if image fails to load -->
                    </button>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown Menu -->
        <div id="profileDropdown" class="profile-dropdown">
            <div class="flex flex-col h-full justify-center">

                <a href="Setting.php" class="dropdown-item flex items-center justify-center gap-3">
                    <span class="font-bold text-lg">Settings</span>
                </a>

                <a href="Edit_Profile.php" class="dropdown-item flex items-center justify-center gap-3">
                    <span class="font-bold text-lg">Edit Profile</span>
                </a>

                <a href="Logout.php" class="dropdown-item logout-item flex items-center justify-center gap-3">
                    <span class="font-bold text-lg">Logout</span>
                </a>
            </div>
        </div>
        <!-- Warning Modal -->
        <div id="warningModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-title">⚠️ Emergency Call</div>
                <div class="modal-message">
                    You are about to call emergency services.<br>
                    <strong>03-76272929 (befrienderskl)</strong> <br>
                    <strong>More Info:</strong><br> <a
                        href="https://www.befrienders.org.my/contact">https://www.befrienders.org.my/contact</a>
                </div>
                <div class="modal-buttons">
                    <button class="btn-continue" onclick="continueCall()">Continue</button>
                    <button class="btn-cancel" onclick="cancelCall()">Cancel</button>
                </div>
            </div>
        </div>
    </header>

    <script>
        const warningModal = document.getElementById('warningModal');

        // Phone button click handler - show modal
        function handlePhoneClick() {
            warningModal.classList.add('show');
        }

        function continueCall() {
            warningModal.classList.remove('show');
            window.location.href = 'tel:03-76272929';
        }

        function cancelCall() {
            warningModal.classList.remove('show');
        }

        // Close modal when clicking outside
        warningModal.addEventListener('click', function (e) {
            if (e.target === warningModal) {
                cancelCall();
            }
        });

        // Toggle profile dropdown
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileBtn = document.getElementById('profileBtn');

            if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Trigger ring animation every 5 seconds
        const phoneBtn = document.getElementById('phoneBtn');
        setInterval(() => {
            phoneBtn.classList.add('ring-animation');
        }, 5000);
    </script>
</body>

</html>