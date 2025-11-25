<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$host = 'localhost';
$dbname = 'innerpeacecomp_web';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user has completed screening in the last week
    $stmt = $pdo->prepare("
        SELECT MAX(created_at) as last_screening 
        FROM (
            SELECT created_at FROM phq9_responses WHERE user_id = ?
            UNION ALL
            SELECT created_at FROM gad7_responses WHERE user_id = ?
        ) as screenings
    ");
    $stmt->execute([$user_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $last_screening = $result['last_screening'];
    $can_take_screening = true;
    $days_until_next = 0;
    $screening_message = '';
    
    if ($last_screening) {
        $last_date = new DateTime($last_screening);
        $today = new DateTime();
        $interval = $today->diff($last_date);
        $days_passed = $interval->days;
        
        // User must wait 7 days (1 week) between screenings
        if ($days_passed < 7) {
            $can_take_screening = false;
            $days_until_next = 7 - $days_passed;
            $screening_message = "You last completed the screening " . $days_passed . " day(s) ago. Please come back in " . $days_until_next . " day(s) to take the screening again.";
        }
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $can_take_screening = false;
    $screening_message = "Database error. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Health Questionnaire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Poppins:ital,wght@0,500;0,600;0,700;1,500&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Manrope', sans-serif;
       
        }
        .option input[type="radio"]:checked + label {
            background-color: #9CAC92;
            color: #F1F3F0;
        }
    </style>
</head>
<body class="bg-[#EAEEEB] min-h-screen flex items-center justify-center p-5">
    <div class="bg-[#B9C5B4] rounded-xl shadow-lg max-w-4xl w-full p-10">
        <!-- Screening Locked Message -->
        <?php if (!$can_take_screening): ?>
        <div id="lockedSection" class="form-section">
            <div class="text-center">
                <div class="mb-6">
                    <svg class="w-20 h-20 mx-auto text-yellow-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zm-2-5a.75.75 0 00-1.5 0v1.263a6 6 0 014.471 5.629.75.75 0 001.498-.058 7.501 7.501 0 00-4.469-7.134V3zm0 9.5a.75.75 0 00-1.5 0v.263a6 6 0 01-4.471-5.629.75.75 0 00-1.498.058 7.501 7.501 0 004.469 7.134v1.263z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-[#40350A] mb-4">Screening Not Available Yet</h2>
                <p class="text-[#706F4E] text-lg mb-6"><?php echo htmlspecialchars($screening_message); ?></p>
                <div class="bg-[#EAEEEB] p-6 rounded-lg mb-6">
                    <p class="text-[#40350A] font-semibold mb-2">📋 Screening Schedule</p>
                    <p class="text-[#706F4E] text-sm">Mental health screenings must be completed every week to help track your mental wellness journey.</p>
                </div>
                <a href="home.php" class="inline-block bg-[#778970] text-[#F5F2E9] px-10 py-4 rounded-lg text-base font-semibold hover:bg-[#5D6A58] transition transform hover:-translate-y-0.5 hover:shadow-lg">
                    Return to Home
                </a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Start Screen -->
        <div id="startSection" class="form-section">
            <h1 class="flex justify-center items-center text-3xl font-semibold text-[#40350A] mb-3">Mental Health Assessment</h1>
            <p class="flex justify-center items-center text-[#706F4E] mb-3">*You are required to perform the assessment every week*</p>
            <div class="flex justify-center items-center">
               <button onclick="startQuestionnaire()" class="bg-[#778970] text-[#F5F2E9] px-10 py-4 rounded-lg text-base font-semibold hover:  transition transform hover:-translate-y-0.5 hover:shadow-lg">
                Start Assessment
                </button> 
            </div>
        </div>
        <!-- PHQ-9 Form -->
        <div id="phq9Section" class="form-section hidden">
            <div class="mb-8">
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-[#778970] transition-all duration-300" id="phq9Progress" style="width: 0%"></div>
                </div>
                <p class="mt-3 text-[#706F4E] text-sm">Part 1 of 2: Depression Screening (PHQ-9)</p>
            </div>

            <h1 class="text-3xl font-bold text-[#40350A] mb-3">Over the last 2 weeks, how often have you been bothered by the following problems?</h1>
            <p class="text-[#706F4E] mb-8 text-sm">Please answer all questions</p>

            <form id="phq9Form">
                <div class="space-y-6">
                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">1. Little interest or pleasure in doing things</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q1" value="0" id="q1_0" class="hidden">
                                <label for="q1_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="1" id="q1_1" class="hidden">
                                <label for="q1_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="2" id="q1_2" class="hidden">
                                <label for="q1_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="3" id="q1_3" class="hidden">
                                <label for="q1_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">2. Feeling down, depressed, or hopeless</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q2" value="0" id="q2_0" class="hidden">
                                <label for="q2_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="1" id="q2_1" class="hidden">
                                <label for="q2_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="2" id="q2_2" class="hidden">
                                <label for="q2_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="3" id="q2_3" class="hidden">
                                <label for="q2_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">3. Trouble falling or staying asleep, or sleeping too much</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q3" value="0" id="q3_0" class="hidden">
                                <label for="q3_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="1" id="q3_1" class="hidden">
                                <label for="q3_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="2" id="q3_2" class="hidden">
                                <label for="q3_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="3" id="q3_3" class="hidden">
                                <label for="q3_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">4. Feeling tired or having little energy</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q4" value="0" id="q4_0" class="hidden">
                                <label for="q4_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="1" id="q4_1" class="hidden">
                                <label for="q4_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="2" id="q4_2" class="hidden">
                                <label for="q4_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="3" id="q4_3" class="hidden">
                                <label for="q4_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">5. Poor appetite or overeating</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q5" value="0" id="q5_0" class="hidden">
                                <label for="q5_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="1" id="q5_1" class="hidden">
                                <label for="q5_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="2" id="q5_2" class="hidden">
                                <label for="q5_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="3" id="q5_3" class="hidden">
                                <label for="q5_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">6. Feeling bad about yourself — or that you are a failure or have let yourself or your family down</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q6" value="0" id="q6_0" class="hidden">
                                <label for="q6_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="1" id="q6_1" class="hidden">
                                <label for="q6_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="2" id="q6_2" class="hidden">
                                <label for="q6_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="3" id="q6_3" class="hidden">
                                <label for="q6_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">7. Trouble concentrating on things, such as reading the newspaper or watching television</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q7" value="0" id="q7_0" class="hidden">
                                <label for="q7_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="1" id="q7_1" class="hidden">
                                <label for="q7_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="2" id="q7_2" class="hidden">
                                <label for="q7_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="3" id="q7_3" class="hidden">
                                <label for="q7_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">8. Moving or speaking so slowly that other people could have noticed. Or the opposite — being so fidgety or restless that you have been moving around a lot more than usual</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q8" value="0" id="q8_0" class="hidden">
                                <label for="q8_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q8" value="1" id="q8_1" class="hidden">
                                <label for="q8_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q8" value="2" id="q8_2" class="hidden">
                                <label for="q8_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q8" value="3" id="q8_3" class="hidden">
                                <label for="q8_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">9. Thoughts that you would be better off dead, or of hurting yourself in some way</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q9" value="0" id="q9_0" class="hidden">
                                <label for="q9_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q9" value="1" id="q9_1" class="hidden">
                                <label for="q9_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q9" value="2" id="q9_2" class="hidden">
                                <label for="q9_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q9" value="3" id="q9_3" class="hidden">
                                <label for="q9_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="mt-6 bg-[#778970] text-[#F5F2E9] px-10 py-4 rounded-lg text-base font-semibold hover:bg-[#5D6A58] transition transform hover:-translate-y-0.5 hover:shadow-lg">
                    Continue to Anxiety Assessment
                </button>
                <p class="text-red-500 mt-3 text-sm" id="phq9Error"></p>
            </form>
        </div>

        <!-- GAD-7 Form -->
        <div id="gad7Section" class="form-section hidden">
            <div class="mb-8">
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-[#778970] transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="mt-3 text-[#706F4E] text-sm">Part 2 of 2: Anxiety Screening (GAD-7)</p>
            </div>

            <h1 class="text-3xl font-bold text-[#40350A] mb-3">Over the last 2 weeks, how often have you been bothered by the following problems?</h1>
            <p class="text-[#706F4E] mb-8 text-sm">Please answer all questions</p>

            <form id="gad7Form">
                <div class="space-y-6">
                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">1. Feeling nervous, anxious, or on edge</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q1" value="0" id="g1_0" class="hidden">
                                <label for="g1_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="1" id="g1_1" class="hidden">
                                <label for="g1_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="2" id="g1_2" class="hidden">
                                <label for="g1_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q1" value="3" id="g1_3" class="hidden">
                                <label for="g1_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">2. Not being able to stop or control worrying</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q2" value="0" id="g2_0" class="hidden">
                                <label for="g2_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="1" id="g2_1" class="hidden">
                                <label for="g2_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="2" id="g2_2" class="hidden">
                                <label for="g2_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q2" value="3" id="g2_3" class="hidden">
                                <label for="g2_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">3. Worrying too much about different things</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q3" value="0" id="g3_0" class="hidden">
                                <label for="g3_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="1" id="g3_1" class="hidden">
                                <label for="g3_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="2" id="g3_2" class="hidden">
                                <label for="g3_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q3" value="3" id="g3_3" class="hidden">
                                <label for="g3_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">4. Trouble relaxing</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q4" value="0" id="g4_0" class="hidden">
                                <label for="g4_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="1" id="g4_1" class="hidden">
                                <label for="g4_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="2" id="g4_2" class="hidden">
                                <label for="g4_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q4" value="3" id="g4_3" class="hidden">
                                <label for="g4_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">5. Being so restless that it is hard to sit still</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q5" value="0" id="g5_0" class="hidden">
                                <label for="g5_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="1" id="g5_1" class="hidden">
                                <label for="g5_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="2" id="g5_2" class="hidden">
                                <label for="g5_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q5" value="3" id="g5_3" class="hidden">
                                <label for="g5_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">6. Becoming easily annoyed or irritable</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q6" value="0" id="g6_0" class="hidden">
                                <label for="g6_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="1" id="g6_1" class="hidden">
                                <label for="g6_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="2" id="g6_2" class="hidden">
                                <label for="g6_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q6" value="3" id="g6_3" class="hidden">
                                <label for="g6_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EAEEEB] p-6 rounded-xl">
                        <div class="font-semibold text-[#40350A] mb-4">7. Feeling afraid, as if something awful might happen</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="option">
                                <input type="radio" name="q7" value="0" id="g7_0" class="hidden">
                                <label for="g7_0" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Not at all</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="1" id="g7_1" class="hidden">
                                <label for="g7_1" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Several days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="2" id="g7_2" class="hidden">
                                <label for="g7_2" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">More than half the days</label>
                            </div>
                            <div class="option">
                                <input type="radio" name="q7" value="3" id="g7_3" class="hidden">
                                <label for="g7_3" class="flex justify-center items-center px-4 py-3 bg-white rounded-lg cursor-pointer text-center text-sm  font-medium text-gray-700 transition w-full h-full">Nearly every day</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="mt-6 bg-[#778970] text-[#F5F2E9] px-10 py-4 rounded-lg text-base font-semibold hover:bg-[#5D6A58] transition transform hover:-translate-y-0.5 hover:shadow-lg">
                    Submit Assessment
                </button>
                <p class="text-red-500 mt-3 text-sm" id="gad7Error"></p>
            </form>
        </div>

        <!-- Success Message -->
        <div id="successSection" class="form-section hidden">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-green-600 mb-3">✓ Assessment Complete!</h2>
                <p class="text-gray-600">Thank you for completing the questionnaire. Your responses have been recorded.</p>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
    <script src="MHScreening.js"></script>
</body>
</html>
