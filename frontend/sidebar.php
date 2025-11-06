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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,500;0,600;0,700;1,500&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }


        .sidebar {
            width: 200px;
            height: 100vh;
            background: #ABB9A4;
            position: fixed;
            left: 0;
            top: 95px;
            padding: 10px 0;
            overflow-y: auto;
            font-family: 'Poppins', sans-serif;
        }

        .nav-item {
            padding: 12px 24px;
            color: #40350A;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 700;
            font-size: 18px;
            display: flex;
            align-items: center;
            /* justify-content: space-between; */
            justify-content: flex-start;
            gap: 10px;
        }

        .nav-item:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .nav-item.active {
            background: rgba(0, 0, 0, 0.15);
            border-left: 4px solid #F5F2E9;
        }

        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .dropdown-content.show {
            max-height: 500px;
        }

        .dropdown-link {
            padding: 12px 24px 12px 56px;
            color: #40350A;
            display: block;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown-link:hover {
            background: rgba(0, 0, 0, 0.1);
            padding-left: 60px;
        }

        .arrow {
            transition: transform 0.3s;
            font-size: 12px;
            /*opacity: 0;*/
        }

        .arrow.rotate {
            transform: rotate(90deg);
        }

        /*
        .nav-item:hover .arrow {
            opacity: 1;
        }
        */
    </style>
</head>

<body>
    <!-- Side Navigation Bar -->
    <nav class="sidebar">
        <!-- Task Section -->
        <div class="nav-section">
            <div class="nav-item" onclick="toggleDropdown('taskDropdown')">
                <span class="arrow" id="taskArrow">▶︎</span>
                <span>Task</span>
            </div>
            <div class="dropdown-content" id="taskDropdown">
                <a href="diary.php" class="dropdown-link font-medium">Diary Log</a>
            </div>
        </div>

        <!-- Zen Section -->
        <div class="nav-section">
            <div class="nav-item" onclick="toggleDropdown('zenDropdown')">
                <span class="arrow" id="zenArrow">▶︎</span>
                <span>Zen</span>
            </div>
            <div class="dropdown-content" id="zenDropdown">
                <a href="../frontend/RelaxMode.php" class="dropdown-link font-medium">Relax Mode</a>
                <a href="../frontend/Focus Mode.php" class="dropdown-link font-medium">Focus Mode</a>
            </div>
        </div>

        <!-- Assistant Section -->
        <div class="nav-section">
            <div class="nav-item" onclick="toggleDropdown('assistantDropdown')">
                <span class="arrow" id="assistantArrow">▶︎</span>
                <span>Assistant</span>
            </div>
            <div class="dropdown-content" id="assistantDropdown">
                <a href="user_trend.php" class="dropdown-link font-medium">Wellness Trends</a>
            </div>
        </div>
    </nav>
    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const arrow = document.getElementById(dropdownId.replace('Dropdown', 'Arrow'));

            // Toggle the dropdown
            dropdown.classList.toggle('show');
            arrow.classList.toggle('rotate');

            // Close other dropdowns (optional)
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            const allArrows = document.querySelectorAll('.arrow');

            allDropdowns.forEach(d => {
                if (d.id !== dropdownId && d.classList.contains('show')) {
                    d.classList.remove('show');
                }
            });

            allArrows.forEach(a => {
                if (a.id !== dropdownId.replace('Dropdown', 'Arrow') && a.classList.contains('rotate')) {
                    a.classList.remove('rotate');
                }
            });
        }

        // Highlight active page
        document.addEventListener('DOMContentLoaded', function () {
            const currentPage = window.location.pathname.split('/').pop();
            const links = document.querySelectorAll('.dropdown-link');

            links.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    //link.style.background = 'rgba(0, 0, 0, 0.15)';
                    // Removed the bold styling - link.style.fontWeight = '700';

                    // Auto-open parent dropdown
                    const parentDropdown = link.closest('.dropdown-content');
                    if (parentDropdown) {
                        parentDropdown.classList.add('show');
                        const arrowId = parentDropdown.id.replace('Dropdown', 'Arrow');
                        document.getElementById(arrowId).classList.add('rotate');
                    }
                }
            });
        });
    </script>
</body>

</html>