// RelaxMode.js 
// //Mini Player 
// //Maximum 3 folder (Moditate, Sleep, Calm)

const audio = document.getElementById("audioPlayer"); 
const playerBar = document.getElementById('player-bar');
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

let currentCategory = null; 
let currentIndex = 0; 
let isPlaying = false;

// --- PLAYLIST DATA (You can add more anytime) --- 
const playlists = {
    meditate: [ 
        "A Stroll - The Grey Room_Density & Time.mp3", 
        "Bodega Cat - Dyalla.mp3", "Charm - Anno Domini Beats.mp3", 
        "Chosen - Anno Domini Beats.mp3", 
        "Circa 1983 - Freedom Trail Studio.mp3", 
        "Club Love - Everet Almond.mp3", 
        "Farm Country - Telecasted.mp3", 
        "Floating On Fire - The Grey Room_Density & TIme.mp3", 
        "Flutter - The Grey Room_Clark Sims.mp3", 
        "For Our Friends - Telecasted.mp3", 
        "Funk It - Dyalla.mp3", 
        "Helios - Jeremy Black.mp3", 
        "Island Life - Telecasted.mp3", 
        "Lion - Dyalla.mp3", 
        "Matterhorn - Jeremy Black.mp3", 
        "Nebula - The Grey Room_Density & Time.mp3", 
        "Oats with Sugar - Casa Rosa's Tulum Vibes.mp3", 
        "On The Beach - Telecasted.mp3", 
        "Pawn - The Grey Room_Golden Palms.mp3", 
        "Powered Walts - The Mini Vandals.mp3", 
        "Rancho Chilla - Jeremy Black.mp3", 
        "Special - The Grey Room_Clark Sims.mp3", 
        "Suitcase - Jeremy Black.mp3", 
        "The Girl from Saint-Anne-des-Plaines - The Mini Vandals.mp3", 
        "Touch - Anno Domini Beats.mp3", 
        "uWu Victory - Rod Kim.mp3", 
        "Window Shopping - Jeremy Black.mp3", 
        "With You - Everet Almond.mp3", 
        "You Know Me - Jeremy Black.mp3" 
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

function filePath(category, filename) {
    return `../src/music/${category}/${encodeURIComponent(filename)}`;
}

//Show Player Bar
function showPlayer(){
    playerBar.classList.remove('hidden');
}

//Hiden Player Bar
function hidePlayer(){
    playerBar.classList.add('hidden');
}

function basenameNoExt(filename){ 
    return filename.replace(/\.[^/.]+$/, ""); 
}

// Load song by category + index
function loadSong(category, index){ 
    if (!playlists[category] || !playlists[category][index]) 
        return false; 
    const filename = playlists[category][index];
    //set audio src
    audio.src = filePath(category, filename); 
    audio.load();
    //set UI cover 
    const candidateCover = `../src/image/cover/${category}.jpeg`; 
    playerCover.src = candidateCover;
    playerArtist.textContent = ""; 
    //reset progress UI
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
    const handler = (e) => {
        if (e) e.stopPropagation(); 
        if (!playlists[category] || playlists[category].length === 0) { 
            alert("No songs configured for category: " + category + ". Please add filenames to playlists in Relax.js"); 
            return; 
        }
        currentCategory = category;
        currentIndex = 0;
        if (loadSong(currentCategory, currentIndex)){
            showPlayer();
            playSong();
            document.querySelectorAll('.track-card').forEach(c => {
            c.classList.remove('playing');
            const btnImg = c.querySelector('.track-btn img');
            if (btnImg) btnImg.src = "../src/ui/playBtn.png";
            });

            // Set this card to active + show PAUSE icon
            card.classList.add('playing');
            const activeBtnImg = card.querySelector('.track-btn img');
            if (activeBtnImg) activeBtnImg.src = "../src/ui/pauseBtn.png";
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
    durationEl.textContent = isNaN(audio.duration) ? "0:00" : formatTime(audio.duration);
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