document.addEventListener('DOMContentLoaded', function() {
    const timerDisplay = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-btn');
    const continueBtn = document.getElementById('continue-btn'); // 🔧 new button
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
    let totalSeconds = 0;
    let remainingSeconds = 0;
    const reminderInterval = 1800; // every 30 mins

    // Circle progress setup
    const radius = 45;
    const circumference = 2 * Math.PI * radius;
    progressCircle.style.strokeDasharray = `${circumference} ${circumference}`;
    progressCircle.style.strokeDashoffset = circumference;

    // Format time
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return hrs > 0
            ? `${hrs.toString().padStart(2, '0')}:${mins
                  .toString()
                  .padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
            : `${mins.toString().padStart(2, '0')}:${secs
                  .toString()
                  .padStart(2, '0')}`;
    }

    // Update display and ring
    function updateDisplay() {
        timerDisplay.textContent = formatTime(remainingSeconds);
        if (totalSeconds > 0) {
            const progress = circumference - (remainingSeconds / totalSeconds) * circumference;
            progressCircle.style.strokeDashoffset = progress;
        } else {
            progressCircle.style.strokeDashoffset = circumference;
        }
    }

    // 🔧 Show break modal (timer paused)
    function showBreakNotification() {
        clearInterval(timer);
        clearInterval(reminderTimer);
        isRunning = false;

        startBtn.disabled = false;
        continueBtn.disabled = false; // enable continue
        continueBtn.style.opacity = '1'; // visible

        // Play bell sound
        timerSound.currentTime = 0;
        timerSound.play().catch(err => console.log('Sound play blocked:', err));

        // Show modal
        modal.style.display = 'flex';

        closeModal.onclick = () => {
            modal.style.display = 'none';
            timerSound.pause();
            timerSound.currentTime = 0;
            // User will manually click Continue
        };
    }

    // 🔧 Start timer from beginning
    function startTimer() {
        if (isRunning) return;
        if (totalSeconds <= 0) {
            alert("Please set a timer duration first!");
            return;
        }

        // Prepare audio unlock
        timerSound.muted = true;
        timerSound.play().then(() => {
            timerSound.pause();
            timerSound.currentTime = 0;
            timerSound.muted = false;
        }).catch(() => {});

        // Start from totalSeconds (new session)
        if (remainingSeconds === 0) remainingSeconds = totalSeconds;

        isRunning = true;
        startBtn.disabled = true;
        continueBtn.disabled = true;
        continueBtn.style.opacity = '0.5';

        timer = setInterval(() => {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                updateDisplay();
                // Calculate how long user has been focusing
                const elapsedSeconds = totalSeconds - remainingSeconds;
                //Trigger notification when 30 minutes have passed and still time left
                if (elapsedSeconds === reminderInterval && remainingSeconds > 0){
                    showBreakNotification();
                }
            } else {
                clearInterval(timer);
                isRunning = false;
                showBreakNotification(); //Final alert when time runs out
            }
        }, 1000);
    }

    // 🔧 Continue timer (resume from where stopped)
    function continueTimer() {
        if (isRunning || remainingSeconds <= 0) return;

        isRunning = true;
        continueBtn.disabled = true;
        continueBtn.style.opacity = '0.5';
        startBtn.disabled = true;

        timer = setInterval(() => {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                updateDisplay();
            } else {
                clearInterval(timer);
                isRunning = false;
                showBreakNotification();
            }
        }, 1000);
    }

    // Reset timer
    function resetTimer() {
        clearInterval(timer);
        clearInterval(reminderTimer);
        isRunning = false;
        remainingSeconds = totalSeconds;
        updateDisplay();
        startBtn.disabled = false;
        continueBtn.disabled = true;
        continueBtn.style.opacity = '0.5';
    }

    // Set custom time
    function setCustomTime() {
        const hours = parseInt(hoursInput.value) || 0;
        const minutes = parseInt(minutesInput.value) || 0;
        const seconds = parseInt(secondsInput.value) || 0;
        totalSeconds = hours * 3600 + minutes * 60 + seconds;
        remainingSeconds = totalSeconds;

        if (totalSeconds <= 0) {
            alert('Please set a valid time (at least 1 second)');
            return;
        }
        updateDisplay();
    }

    // Validate time inputs
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
    continueBtn.addEventListener('click', continueTimer); // 🔧 new
    resetBtn.addEventListener('click', resetTimer);
    setTimeBtn.addEventListener('click', setCustomTime);
    hoursInput.addEventListener('change', () => validateInput(hoursInput, 23));
    minutesInput.addEventListener('change', () => validateInput(minutesInput, 59));
    secondsInput.addEventListener('change', () => validateInput(secondsInput, 59));

    // Init
    continueBtn.disabled = true;
    continueBtn.style.opacity = '0.5';
    updateDisplay();
});
