<?php require_once '../backend/config_usertrend.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wellness Trends</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Poppins:ital,wght@0,500;0,600;0,700;1,500&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }

        .main-content {
            margin-left: 200px; 
            margin-top: 40px; 
            padding: 40px;
            min-height: calc(100vh - 95px);
        }

    </style>

</head>
<body class="bg-[#EAEEEB] min-h-screen">
    <?php require 'header.php'; ?>
    <?php require 'sidebar.php'; ?>
    <?php require 'chatbot.php'; ?>
    <div class="main-content">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-[#40350A] mb-2">My Wellness Report</h1>
                <p class="text-[#706F4E]">Track your progress and see how you're doing</p>
            </div>

            <!-- Week Selector -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <form method="GET" action="" class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#40350A] mb-2">Select Week</label>
                        <select name="week" id="weekSelector" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="this.form.submit()">
                            <?php foreach ($weeks as $index => $week): ?>
                                <option value="<?php echo $index === 0 ? 'current' : 'week' . $index; ?>" <?php echo $week_index === $index ? 'selected' : ''; ?>>
                                    <?php echo $index === 0 ? 'Current Week' : 'Week ' . ($index + 1) . ' ago'; ?> (<?php echo $week['label']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="analyze" value="1" class="px-6 py-2 bg-[#778970] text-white font-semibold rounded-lg hover:bg-[#5D6A58] transition">
                        Refresh Analysis
                    </button>
                </form>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- PHQ-9 Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#40350A]">PHQ-9 Score</h3>
                        <span class="text-2xl"><?php echo $phq_info['emoji']; ?></span>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-4xl font-bold text-blue-600">
                                <?php echo $current_phq ? $current_phq['score'] : 'N/A'; ?>
                            </p>
                            <p class="text-sm text-[#706F4E]"><?php echo $phq_info['category']; ?></p>
                        </div>
                        <?php if ($phq_change != 0): ?>
                        <div class="flex items-center <?php echo getTrendColor($phq_change); ?>">
                            <?php echo getTrendIcon($phq_change); ?>
                            <span class="text-sm font-medium text-[#40350A]">
                                <?php echo abs($phq_change); ?> from last week
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- GAD-7 Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#40350A]">GAD-7 Score</h3>
                        <span class="text-2xl"><?php echo $gad_info['emoji']; ?></span>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-4xl font-bold text-purple-600">
                                <?php echo $current_gad ? $current_gad['score'] : 'N/A'; ?>
                            </p>
                            <p class="text-sm text-[#706F4E]"><?php echo $gad_info['category']; ?></p>
                        </div>
                        <?php if ($gad_change != 0): ?>
                        <div class="flex items-center <?php echo getTrendColor($gad_change); ?>">
                            <?php echo getTrendIcon($gad_change); ?>
                            <span class="text-sm font-medium text-[#40350A]">
                                <?php echo abs($gad_change); ?> from last week
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Journal Activity -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#40350A]">Journal Entries</h3>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-4xl font-bold text-indigo-600"><?php echo count($current_journals); ?></p>
                            <p class="text-sm text-[#706F4E]">This week</p>
                        </div>
                        <?php if ($journal_change != 0): ?>
                        <div class="flex items-center <?php echo $journal_change > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $journal_change > 0 ? getTrendIcon(1) : getTrendIcon(-1); ?>
                            <span class="text-sm font-medium text-[#40350A]">
                                <?php echo abs($journal_change); ?> entries
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Score Trends Chart -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-[#40350A] mb-4">Score Trends (Last 4 Weeks)</h3>
                    <canvas id="scoreChart"></canvas>
                </div>

                <!-- Activity Chart -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-[#40350A] mb-4">Weekly Activity</h3>
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- AI Analysis Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex items-center mb-4">
                    <h3 class="text-xl font-semibold text-[#40350A]">AI-Powered Insights</h3>
                </div>
                <div id="aiAnalysis" class="space-y-4">
                    <!-- Loading state -->
                    <div id="aiLoading" class="flex flex-col items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#778970] mb-4"></div>
                        <span class="text-[#706F4E] text-lg">Generating AI insights...</span>
                        <span class="text-[#706F4E] text-sm mt-2">This may take a few moments</span>
                    </div>
                    
                    <!-- Analysis content (hidden initially) -->
                    <div id="aiContent" class="hidden"></div>
                </div>
            </div>

            <!-- Weekly Highlights -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-[#40350A] mb-4">This Week's Highlights</h3>
                <div class="space-y-4">
                    <?php if ($current_phq): ?>
                    <div class="border-l-4 border-blue-500 pl-4">
                        <p class="text-sm text-[#706F4E]"><?php echo date('l, M d', strtotime($current_phq['created_at'])); ?></p>
                        <p class="text-[#40350A]">Completed PHQ-9 assessment - Score: <?php echo $current_phq['score']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($current_gad): ?>
                    <div class="border-l-4 border-purple-500 pl-4">
                        <p class="text-sm text-[#706F4E]"><?php echo date('l, M d', strtotime($current_gad['created_at'])); ?></p>
                        <p class="text-[#40350A]">Completed GAD-7 assessment - Score: <?php echo $current_gad['score']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php foreach (array_slice($current_journals, 0, 3) as $journal): ?>
                    <div class="border-l-4 border-green-500 pl-4">
                        <p class="text-sm text-[#706F4E]"><?php echo date('l, M d', strtotime($journal['created_at'])); ?></p>
                        <p class="text-[#40350A]">
                            Journal entry: "<?php echo htmlspecialchars(substr($journal['content'], 0, 100)); ?>..."
                        </p>
                    </div>
                    <?php endforeach; ?>

                    <?php if (count($current_chats) > 0): ?>
                    <div class="border-l-4 border-indigo-500 pl-4">
                        <p class="text-sm text-[#706F4E]"><?php echo date('l, M d', strtotime($current_chats[0]['created_at'])); ?></p>
                        <p class="text-[#40350A]">Chatbot interaction: <?php echo count($current_chats); ?> messages exchanged</p>
                    </div>
                    <?php endif; ?>

                    <?php if (!$current_phq && !$current_gad && count($current_journals) === 0 && count($current_chats) === 0): ?>
                    <div class="text-center text-[#706F4E] py-4">
                        No activity recorded for this week yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Score Trends Chart
        const scoreCtx = document.getElementById('scoreChart').getContext('2d');
        new Chart(scoreCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($historical_scores['labels']); ?>,
                datasets: [{
                    label: 'PHQ-9',
                    data: <?php echo json_encode($historical_scores['phq']); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    spanGaps: true
                }, {
                    label: 'GAD-7',
                    data: <?php echo json_encode($historical_scores['gad']); ?>,
                    borderColor: 'rgb(168, 85, 247)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    tension: 0.4,
                    spanGaps: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 27
                    }
                }
            }
        });

        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($historical_scores['labels']); ?>,
                datasets: [{
                    label: 'Journal Entries',
                    data: <?php echo json_encode($activity_data['journals']); ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                }, {
                    label: 'Chat Sessions',
                    data: <?php echo json_encode($activity_data['chats']); ?>,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                }, {
                    label: 'Assessments',
                    data: <?php echo json_encode($activity_data['assessments']); ?>,
                    backgroundColor: 'rgba(236, 72, 153, 0.8)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Load AI Analysis asynchronously
        async function loadAIAnalysis() {
            const weekIndex = <?php echo $week_index; ?>;
            const loadingDiv = document.getElementById('aiLoading');
            const contentDiv = document.getElementById('aiContent');
            
            try {
                const response = await fetch(`../backend/api_usertrend_analysis.php?week_index=${weekIndex}`);
                const data = await response.json();
                
                if (data.success && data.analysis) {
                    const analysis = data.analysis;
                    let html = '';
                    
                    if (analysis.positive && analysis.positive.trim()) {
                        html += `
                            <div class="bg-green-50 border-l-4 border-green-500 p-4">
                                <h4 class="font-semibold text-green-800 mb-2">User Progress</h4>
                                <p class="text-green-700">${escapeHtml(analysis.positive).replace(/\n/g, '<br>')}</p>
                            </div>
                        `;
                    }
                    
                    if (analysis.observations && analysis.observations.length > 0) {
                        html += `
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                                <h4 class="font-semibold text-blue-800 mb-2">Key Observations</h4>
                                <ul class="text-blue-700 space-y-2">
                                    ${analysis.observations.map(obs => obs.trim() ? `<li>${escapeHtml(obs)}</li>` : '').join('')}
                                </ul>
                            </div>
                        `;
                    }
                    
                    if (analysis.suggestions && analysis.suggestions.length > 0) {
                        html += `
                            <div class="bg-purple-50 border-l-4 border-purple-500 p-4">
                                <h4 class="font-semibold text-purple-800 mb-2">Personalized Suggestions</h4>
                                <ul class="text-purple-700 space-y-2">
                                    ${analysis.suggestions.map(sug => sug.trim() ? `<li>${escapeHtml(sug)}</li>` : '').join('')}
                                </ul>
                            </div>
                        `;
                    }
                    
                    if (analysis.reminders && analysis.reminders.trim()) {
                        html += `
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                                <h4 class="font-semibold text-yellow-800 mb-2">Gentle Reminders</h4>
                                <p class="text-yellow-700">${escapeHtml(analysis.reminders).replace(/\n/g, '<br>')}</p>
                            </div>
                        `;
                    }
                    
                    contentDiv.innerHTML = html || '<p class="text-center text-[#706F4E]">No insights available at this time.</p>';
                } else {
                    contentDiv.innerHTML = '<p class="text-center text-red-600">Unable to load AI insights. Please try refreshing the page.</p>';
                }
            } catch (error) {
                console.error('Error loading AI analysis:', error);
                contentDiv.innerHTML = '<p class="text-center text-red-600">Error loading AI insights. Please try again later.</p>';
            } finally {
                loadingDiv.classList.add('hidden');
                contentDiv.classList.remove('hidden');
            }
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Load AI analysis after page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a brief moment to let charts render first
            setTimeout(loadAIAnalysis, 500);
        });
    </script>
</body>
</html>