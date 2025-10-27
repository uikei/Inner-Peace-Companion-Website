// RelaxMode.js
//Mini Player
//Maximum 3 folder (Moditate, Sleep, Calm)


const audio = document.getElementById("audioPlayer");
const playerBar = document.getElementById('playerBar');
const playPauseBtn = document.getElementById("playPauseBtn");
const playPauseIcon = document.getElementById('playPauseIcon');
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const progressBar = document.getElementById("progressBar");
const currentTimeEl = document.getElementById("currentTime");
const durationEl = document.getElementById("duration");
const playerCover = document.getElementById('playerCover');
const playerTitle = document.getElementById('playerTitle');
const playerArtist = document.getElementById('playerArtist');

// --- PLAYLIST DATA (You can add more anytime) ---
const playlists = {
    meditate: [
        "assets/music/meditate/song1.mp3",
        "assets/music/meditate/song2.mp3",
        "assets/music/meditate/song3.mp3"
    ],
    sleep: [
        "AETHER - Density & Time.mp3",
        "Apollo - Telecasted.mp3",
        "Bah Dop Bop - Casa Rosa's Tulum Vibes.mp3",
        "Escapism - Yung Logos.mp3",
        "Fat Man - Yung Logos.mp3",
        "GROWTH_DECAY - Density & Time.mp3",
        "Lullabye No.108 - The Mini Vandals.mp3",
        "Malkaus - Aditya Verma.mp3",
        "PELAGIC - Density & Time.mp3",
        "Ricky Tar - Casa Rosa's Tulum Vibes.mp3",
        "Sleep Music No.1 - Chris Haugen.mp3",
        "Somnia Variation 1 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 2 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 3 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 4 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 5 relax and sleep - Reed Mathis.mp3,",
        "Somnia Variation 7 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 8 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 9 relax and sleep - Reed Mathis.mp3",
        "Somnia Variation 10 relax and sleep - Reed Mathis.mp3",
        "Suave Sleeth - Casa Rosa's Tulum Vibes.mp3",
        "Sugar High - Jeremy Korpas.mp3",
        "Thumri - Sandeep Das, Anisha Roy, Bivakar Chaudhuri.mp3",
        "Tiburtina - Schwartzy.mp3",
        "Toy Shipping - Jeremy Korpas.mp3",
        "Your Love - Yung Logos.mp3"
    ],
    calm: [
        "Akatsuki Rising - The Mini Vandals.mp3",
        "Anomori Wings - The Mini Vandals.mp3",
        "Call me crazy - Pratrick Patrikios.mp3",
        "Circuit Rush - The Mini Vandals.mp3",
        "City lights - Patrick Patrikios.mp3",
        "Country Sky - Telecasted.mp3",
        "Event Horizon - The Grey Room_Density & Time.mp3",
        "Floating Lanterns - The Mini Vandals.mp3",
        "From Here on In - Everet Almond.mp3",
        "Ghibli Station - The Mini Vandals.mp3",
        "Golden Hour - Telecasted.mp3",
        "Jomon Grove - The Mini Vandals.mp3",
        "June time - Patrick Patrikios.mp3",
        "Laniakea - THe Grey Room_Density & Time.mp3",
        "Long Distance - Mark Karan, Scott Guberman, Angeline Saris, Jeremy Hoenig.mp3",
        "Misidrection - The Grey Room_Density & Time.mp3",
        "On The Flip - THe Grey Room_Density & Time.mp3",
        "Ordinary Men - Mark Karan, Scott Guberman, Angeline Saris, Jeremy Hoenig.mp3",
        "Pulsar - The Grey Room_Density & Time.mp3",
        "Rain Over Kyoto Station - The Mini Vandals.mp3",
        "Resolution Or Reflection - The Grey Room_Clark Sims.mp3",
        "Sample Mind - Freedom Trail Studio.mp3",
        "Singularity - The Grey Room_Density & Time.mp3",
        "Taking in The Changes - Everet Almond.mp3",
        "True Crime Documentary and Chill - Rod Kim.mp3",
        "Twinkle - The Grey Room_Density & Time.mp3",
        "Viral Bliss, Im Uncomfortable - Ryan Stasik.mp3",
        "Viral Dance - Ryan Stasik.mp3",
        "Y2K Heist - Rod Kim.mp3",
        "You and I - Telecasted.mp3"
    ]
};

let currentCategory = null;
let currentIndex = 0;
let isPlaying = false;

function filePath(category, filename) {
    return '../src/music/${category}/${encodeURIComponent(filename)}'.replace(/%20/g, '');
}

//Show Player Bar
function showPlayer(){
    showPlayer.classList.remove('hidden');
}

//Hiden Player Bar
function hidePlayer(){
    playerBar.classList.add('hidden');
}

// Load song by category + index
function loadSong(category, index){
    if (!playlists[category] || !playlists[category][index]) return false;
    const filename = playlists[category][index];
    audio.src = filePath(category, filename);
    audio.load();
    // update UI (cover + title)
    // try to find a cover image in ../src/banner/<category>.jpg/png — fallback to the track card image if not found
    playerCover.src = `../src/banner/${category}.jpg`;
    playerTitle.textContent = filename.replace(/\.[^/.]+$/, ""); // filename without extension
    playerArtist.textContent = ""; // optional: set artist if you have it
    progressBar.value = 0;
    currentTimeEl.textContent = "0:00";
    durationEl.textContent = "0:00";
    return true;
}

function playSong() {
    if (!audio.src) return;
    audio.play().then(()=>{
        isPlaying = true;
        playPauseIcon.src = "../src/ui/pauseBtn.png";
    }).catch(err=>{
        console.error("Play failed:" , err);
    })
}

function pauseSong() {
    audio.pause(); 
    isPlaying = false;
    playPauseBtn.querySelector("img").src = "../src/ui/playBtn.png";
}

// Prev / Next
function prevTrack(){
    if (!currentCategory) return;
    const list = playlists[currentCategory];
    currentIndex = (currentIndex - 1 + list.length) % list.length;
    if (loadSong(currentCategory, currentIndex)) playSong();
}
function nextTrack(){
    if (!currentCategory) return;
    const list = playlists[currentCategory];
    currentIndex = (currentIndex + 1) % list.length;
    if (loadSong(currentCategory, currentIndex)) playSong();
}
// click handlers for cards
document.querySelectorAll('.track-card').forEach(card=>{
    const btn = card.querySelector('.track-btn');
    const category = card.dataset.category;
    // when whole card clicked OR button clicked -> play category (PM-1: start at 0)
    const handler = (e) => {
        e && e.stopPropagation();
        if (!playlists[category] || playlists[category].length === 0){
            alert("No songs configured for category: " + category + ". Please add filenames to playlists in RelaxMode.js");
            return;
        }
        currentCategory = category;
        currentIndex = 0;
        if (loadSong(currentCategory, currentIndex)){
            showPlayer();
            playSong();
            // mark playing visual on card
            document.querySelectorAll('.track-card').forEach(c => c.classList.remove('playing'));
            card.classList.add('playing');
        }
    };
    card.addEventListener('click', handler);
    if (btn) btn.addEventListener('click', handler);
});

// player buttons
playPauseBtn.addEventListener('click', (e)=>{
    e.stopPropagation();
    if (!currentCategory) return;
    isPlaying ? pauseSong() : playSong();
});
prevBtn.addEventListener('click', (e)=>{ e.stopPropagation(); prevTrack(); });
nextBtn.addEventListener('click', (e)=>{ e.stopPropagation(); nextTrack(); });

// audio events -> update UI
audio.addEventListener('loadedmetadata', ()=>{
    progressBar.max = Math.floor(audio.duration) || 0;
    durationEl.textContent = formatTime(audio.duration);
});
audio.addEventListener('timeupdate', ()=>{
    if (!isNaN(audio.duration)){
        progressBar.value = Math.floor(audio.currentTime);
        currentTimeEl.textContent = formatTime(audio.currentTime);
        durationEl.textContent = formatTime(audio.duration);
    }
});
audio.addEventListener('ended', ()=>{
    // auto play next in same category
    if (!currentCategory) return;
    const list = playlists[currentCategory];
    currentIndex = (currentIndex + 1) % list.length;
    if (loadSong(currentCategory, currentIndex)) playSong();
});

// seeking via range
progressBar.addEventListener('input', ()=>{
    audio.currentTime = progressBar.value;
});

// small helper
function formatTime(sec){
    if (!sec || isNaN(sec)) return "0:00";
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

// initially hide player
hidePlayer();
// -------------------------------------------------------------------------------------------