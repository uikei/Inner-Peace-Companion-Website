<!-- Screening Reminder Modal -->
<div id="screeningReminderModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#B9C5B4] rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
        <!-- Close Button -->
        <button onclick="closeScreeningReminder()" 
                class="absolute top-4 right-4 text-[#40350A] hover:text-gray-600 text-2xl">
            ✕
        </button>

        <div class="text-center">
            <!-- Icon -->
            <div class="mb-4 flex justify-center">
                <div class="bg-white/40 rounded-full p-4">
                    <img src="../src/ui/calendar.png" alt="calender" class="flex justify-center w-[40px] h-[40px]"> 
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-bold text-[#40350A] mb-2">Time for Your Screening!</h2>
            
            <!-- Message -->
            <p class="text-[#40350A] mb-6">
                It's been a while since your last mental health screening. Regular check-ins help you track your wellness journey.
            </p>

            <!-- Info Box -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-green-800">
                    <strong>📋 Why screen regularly?</strong><br>
                    Screenings help you monitor your mental health and identify patterns over time.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button onclick="closeScreeningReminder()" 
                        class="flex-1 px-4 py-2 bg-[#A6B3A0] text-[#D0D8CD] font-semibold rounded-lg hover:bg-[#7F8C79] transition">
                    Later
                </button>
                <button onclick="openScreeningModal()" 
                        class="flex-1 px-4 py-2 bg-[#778970] text-[#F5F2E9] font-semibold rounded-lg hover:bg-[#5D6A58] transition">
                    Start Now
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// Check if user needs screening on page load
document.addEventListener('DOMContentLoaded', function() {
    checkScreeningStatus();
});

function checkScreeningStatus() {
    fetch('screening_reminder.php')
        .then(response => response.json())
        .then(data => {
            if (data.needs_screening) {
                showScreeningReminder();
            }
        })
        .catch(error => console.error('Error checking screening status:', error));
}

function showScreeningReminder() {
    const modal = document.getElementById('screeningReminderModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeScreeningReminder() {
    const modal = document.getElementById('screeningReminderModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function openScreeningModal() {
    window.location.href = 'MHScreening.php';
}

function closeScreeningModal() {
    const modal = document.getElementById('screeningModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Close modal when clicking outside
document.getElementById('screeningModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeScreeningModal();
    }
});
</script>
