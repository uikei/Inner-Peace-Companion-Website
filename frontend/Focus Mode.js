// Your JavaScript code here
document.addEventListener('DOMContentLoaded', function() {
    const timerDisplay = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-btn');
    const resetBtn = document.getElementById('reset-btn');
    const setTimeBtn = document.getElementById('set-time-btn');
    const hoursInput = document.getElementById('hours');
    const minutesInput = document.getElementById('minutes');
    const secondsInput = document.getElementById('seconds');
    const progressCircle = document.querySelector('.progress-ring-progress');
    const timerSound = document.getElementById('timer-sound');
    const modal = document.getElementById('breakModal');
    const closeModal = document.getElementById('closeModal');
    
    // Timer variables
    let timer;
    let reminderTimer;
    let isRunning = false;
    let totalSeconds = 0;  //Default minutes
    let remainingSeconds = 0;
    let reminderInterval = 1800; // every 30 mins = 1800s
    
    // Calculate circumference for progress ring
    const radius = 45;
    const circumference = 2 * Math.PI * radius;
    progressCircle.style.strokeDasharray = `${circumference} ${circumference}`;
    progressCircle.style.strokeDashoffset = circumference;

    
    // Format time as MM:SS
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        if (hrs > 0) {
            return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Update timer display
    function updateDisplay() {
        timerDisplay.textContent = formatTime(remainingSeconds);

        if (totalSeconds > 0){
            const progress = circumference - (remainingSeconds / totalSeconds) * circumference;
            progressCircle.style.strokeDashoffset = progress;
        } else {
            progressCircle.style.strokeDashoffset = circumference;
        }
    }


    function showBreakNotification() {
        // Stop the countdown before showing modal
        clearInterval(timer);
        clearInterval(reminderTimer);
        isRunning = false;
        startBtn.disabled = false; // Allow user to start again manually

        // Play bell sound
        timerSound.currentTime = 0;
        timerSound.play().catch(err => console.log('Sound play blocked:', err));

        // Show the modal
        modal.style.display = 'flex';

        // When user closes modal
        closeModal.onclick = () => {
            modal.style.display = 'none';
            timerSound.pause();
            timerSound.currentTime = 0;

            // Do NOT auto-resume — wait for user to click "Start" again
            // (Just leave the remainingSeconds as is)
        };
    }

    // Start the timer
   function startTimer() {
    if (isRunning) return;
    if (totalSeconds <= 0) {
        alert("Please set a timer duration first!");
        return;
    }

        // Unlock audio for autoplay
        timerSound.muted = true;
        timerSound.play().then(() => {
            timerSound.pause();
            timerSound.currentTime = 0;
            timerSound.muted = false;
        }).catch(err => console.log('Audio init blocked:', err));

        isRunning = true;
        startBtn.disabled = true;

        remainingSeconds = totalSeconds;
        updateDisplay();

        // Schedule reminders every 30 minutes (if timer > 30 mins)
        if (totalSeconds > reminderInterval) {
            reminderTimer = setInterval(() => {
                if (remainingSeconds > 0) {
                    showBreakNotification(); // will pause the timer internally
                }
            }, reminderInterval * 1000);
        }

        timer = setInterval(() => {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                updateDisplay();
            } else {
                clearInterval(timer);
                clearInterval(reminderTimer);
                isRunning = false;
                startBtn.disabled = false;
                showBreakNotification();
            }
        }, 1000);
    }
    
    // Reset the timer
    function resetTimer() {
        clearInterval(timer);
        clearInterval(reminderTimer);
        isRunning = false;
        remainingSeconds = totalSeconds;
        updateDisplay();
        startBtn.disabled = false;
    }
    
    // Set custom time
    function setCustomTime() {
        const hours = parseInt(hoursInput.value) || 0;
        const minutes = parseInt(minutesInput.value) || 0;
        const seconds = parseInt(secondsInput.value) || 0;

        totalSeconds = hours * 3600 + minutes * 60 + seconds;
        remainingSeconds = totalSeconds;
        console.log("Custom time set:", totalSeconds, "seconds");

        if (totalSeconds <= 0) {
            alert('Please set a valid time (at least 1 second)');
            return;
        }

        updateDisplay();
    }

    // Validate input fields
    function validateInput(input, max) {
        let value = parseInt(input.value);
        if (isNaN(value) || value < 0) {
            input.value = 0;
        } else if (value > max) {
            input.value = max;
        }
    }
    
    // Event listeners
    startBtn.addEventListener('click', startTimer);
    resetBtn.addEventListener('click', resetTimer);
    setTimeBtn.addEventListener('click', setCustomTime);
    hoursInput.addEventListener('change', () => validateInput(hoursInput, 23));
    minutesInput.addEventListener('change', () => validateInput(minutesInput, 59));
    secondsInput.addEventListener('change', () => validateInput(secondsInput, 59));

    updateDisplay();
});