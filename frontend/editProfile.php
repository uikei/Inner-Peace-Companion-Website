<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$host = 'localhost';
$dbname = 'innerpeacecomp_web';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];
    
    // Fetch current profile picture and username
    $stmt = $pdo->prepare("SELECT profile_picture, user_username FROM signup_web WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<script>alert('User not found. Please log in again.'); window.location.href='Login.html';</script>";
        exit();
    }
    
    $current_profile_pic = $user['profile_picture'] ?? '../src/ui/icon.jpeg';
    $username = $user['user_username'] ?? 'User';
    
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . $e->getMessage() . "'); window.location.href='Login.html';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile Picture</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }

        body {
            background: #EAEEEB;
            margin: 0;
            padding-top: 95px;
        }

        .banner {
            background: #889B7E;
            padding: 20px;
            color: white;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 30;
            height: 95px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .profile-pic-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #889B7E;
            margin: 0 auto 24px;
            display: block;
        }

        .upload-section {
            background: #f9f9f9;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: block;
        }

        .file-input-label {
            display: block;
            padding: 16px;
            background: white;
            border: 2px dashed #889B7E;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #889B7E;
        }

        .file-input-label:hover {
            background: #f0f7eb;
            border-color: #6b7d62;
        }

        input[type="file"] {
            display: none;
        }

        .file-name {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-save {
            background: #889B7E;
            color: white;
        }

        .btn-save:hover {
            background: #6b7d62;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 155, 126, 0.3);
        }

        .btn-save:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 24px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 24px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="banner">Edit Profile Picture</div>

    <div class="container">
        <!-- Success/Error Messages -->
        <div id="successMessage" class="success-message">
            ✅ Profile picture updated successfully!
        </div>
        <div id="errorMessage" class="error-message"></div>

        <div class="title">Change Your Profile Picture</div>

        <!-- Username Display -->
        <div class="flex justify-center mb-6">
            <p class="text-lg font-semibold text-gray-700">👤 <?php echo htmlspecialchars($username); ?></p>
        </div>

        <!-- Profile Picture Preview -->
        <img id="profilePreview" src="<?php echo htmlspecialchars($current_profile_pic); ?>" 
             alt="Profile Picture" class="profile-pic-preview"
             onerror="this.src='../src/ui/icon.jpeg'">

        <!-- Upload Form -->
        <form id="editProfileForm" enctype="multipart/form-data">
            <div class="upload-section">
                <div class="file-input-wrapper">
                    <label for="profilePicInput" class="file-input-label">
                        📷 Click to upload or drag and drop<br>
                        <span style="font-size: 12px; color: #999;">PNG, JPG, GIF or WebP (Max 5MB)</span>
                    </label>
                    <input type="file" id="profilePicInput" name="profile_picture" accept="image/*">
                    <div class="file-name" id="fileName"></div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="button-group">
                <button type="submit" class="btn btn-save" id="saveBtn">Save Picture</button>
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        const fileInput = document.getElementById('profilePicInput');
        const profilePreview = document.getElementById('profilePreview');
        const fileName = document.getElementById('fileName');
        const form = document.getElementById('editProfileForm');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        const saveBtn = document.getElementById('saveBtn');

        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showError('Please select an image file');
                    fileInput.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('File size must be less than 5MB');
                    fileInput.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    profilePreview.src = event.target.result;
                    fileName.textContent = '✓ ' + file.name + ' selected';
                    fileName.style.color = '#4CAF50';
                    hideError();
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle drag and drop
        const fileInputWrapper = document.querySelector('.file-input-wrapper');
        
        fileInputWrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileInputWrapper.style.opacity = '0.7';
        });

        fileInputWrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileInputWrapper.style.opacity = '1';
        });

        fileInputWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            fileInputWrapper.style.opacity = '1';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!fileInput.files || fileInput.files.length === 0) {
                showError('Please select an image to upload');
                return;
            }

            const formData = new FormData();
            formData.append('profile_picture', fileInput.files[0]);

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            try {
                const response = await fetch('updateProfile.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess('Profile picture updated successfully!');
                    fileInput.value = '';
                    fileName.textContent = '';
                    setTimeout(() => {
                        window.location.href = 'home.php';
                    }, 2000);
                } else {
                    showError(result.message || 'Error updating profile picture');
                }
            } catch (error) {
                showError('Error: ' + error.message);
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Picture';
            }
        });

        function showSuccess(message) {
            successMessage.textContent = '✅ ' + message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
        }

        function showError(message) {
            errorMessage.textContent = '❌ ' + message;
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
        }

        function hideError() {
            errorMessage.style.display = 'none';
        }
    </script>
</body>

</html>
