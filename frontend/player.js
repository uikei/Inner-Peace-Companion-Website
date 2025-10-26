// player.js
(() => {
  const api = window.SONGS_API || '../Code/fetchSongs.php';
  const tracksListEl = document.getElementById('tracksList');

  // Player elements
  const playerBar = document.getElementById('playerBar');
  const playerCover = document.getElementById('playerCover');
  const playerTitle = document.getElementById('playerTitle');
  const playerArtist = document.getElementById('playerArtist');
  const playBtn = document.getElementById('playBtn');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const seekBar = document.getElementById('seekBar');
  const currentTimeEl = document.getElementById('currentTime');
  const durationEl = document.getElementById('duration');
  const shuffleBtn = document.getElementById('shuffleBtn');
  const repeatBtn = document.getElementById('repeatBtn');
  const volumeEl = document.getElementById('volume');

  let audio = new Audio();
  audio.preload = 'metadata';
  let songs = [];
  let index = 0;
  let isPlaying = false;
  let shuffle = false;
  let repeat = false;
  let seekUpdating = false;

  // fetch songs list from backend
  async function loadSongs() {
    try {
      const res = await fetch(api);
      const json = await res.json();
      songs = json.songs || [];
      renderList();
      if (songs.length) initTrack(0);
    } catch (err) {
      console.error('Failed to load songs', err);
      tracksListEl.innerHTML = '<p class="loading">Failed to load songs.</p>';
    }
  }

  function renderList(){
    if (!songs.length) {
      tracksListEl.innerHTML = '<p class="loading">No tracks found.</p>';
      return;
    }
    tracksListEl.innerHTML = '';
    songs.forEach((s, i) => {
      const card = document.createElement('div');
      card.className = 'track-card';
      card.dataset.index = i;

      const thumb = document.createElement('img');
      thumb.className = 'thumb';
      thumb.src = s.cover || 'images/default-cover.jpg';
      thumb.alt = s.title;

      const meta = document.createElement('div');
      meta.className = 'track-meta';
      meta.innerHTML = `<div class="track-title">${escapeHtml(s.title)}</div>
                        <div class="track-artist">${escapeHtml(s.artist)}</div>`;

      const actions = document.createElement('div');
      actions.className = 'track-actions';
      const btn = document.createElement('button');
      btn.className = 'circle-btn';
      btn.innerHTML = '▶';
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        playIndex(i);
      });

      actions.appendChild(btn);
      card.appendChild(thumb);
      card.appendChild(meta);
      card.appendChild(actions);

      card.addEventListener('click', () => {
        playIndex(i);
      });

      tracksListEl.appendChild(card);
    });
  }

  function initTrack(i) {
    index = i;
    const s = songs[index];
    if (!s) return;
    audio.src = s.file;
    // Use s.cover to set the playerCover src
    playerCover.src = s.cover || 'images/default-cover.jpg'; // Use a default image if no cover
    playerTitle.textContent = s.title;
    playerArtist.textContent = s.artist || '';
    playerBar.setAttribute('aria-hidden', 'false');

    // update list styles
    document.querySelectorAll('.track-card').forEach(c => c.classList.remove('playing'));
    const el = document.querySelector(`.track-card[data-index="${index}"]`);
    if (el) el.classList.add('playing');
  }

  function playIndex(i) {
    if (i === index && isPlaying) {
      pause();
      return;
    }
    if (i !== index) initTrack(i);
    play();
  }

  function play() {
    audio.play().then(() => {
      isPlaying = true;
      playBtn.textContent = '⏸';
      updateUIPlaying(true);
    }).catch(err => {
      console.error('Play error', err);
    });
  }

  function pause() {
    audio.pause();
    isPlaying = false;
    playBtn.textContent = '⏯';
    updateUIPlaying(false);
  }

  function updateUIPlaying(on) {
    const el = document.querySelector(`.track-card[data-index="${index}"] .circle-btn`);
    if (el) el.textContent = on ? '❚❚' : '▶';
    document.querySelectorAll('.track-card').forEach(c => {
      if (parseInt(c.dataset.index,10) === index) {
        c.classList.toggle('playing', on);
      }
    });
  }

  // controls
  playBtn.addEventListener('click', () => {
    isPlaying ? pause() : play();
  });
  prevBtn.addEventListener('click', () => {
    prev();
  });
  nextBtn.addEventListener('click', () => {
    next();
  });

  shuffleBtn.addEventListener('click', () => {
    shuffle = !shuffle;
    shuffleBtn.style.opacity = shuffle ? '1' : '0.6';
  });
  repeatBtn.addEventListener('click', () => {
    repeat = !repeat;
    repeatBtn.style.opacity = repeat ? '1' : '0.6';
  });

  volumeEl.addEventListener('input', ()=> {
    audio.volume = parseFloat(volumeEl.value);
  });

  function prev() {
    if (shuffle) {
      index = Math.floor(Math.random() * songs.length);
    } else {
      index = (index - 1 + songs.length) % songs.length;
    }
    initTrack(index);
    play();
  }

  function next() {
    if (shuffle) {
      index = Math.floor(Math.random() * songs.length);
    } else {
      index = (index + 1) % songs.length;
    }
    initTrack(index);
    play();
  }

  // audio events
  audio.addEventListener('loadedmetadata', () => {
    seekBar.max = Math.floor(audio.duration);
    durationEl.textContent = formatTime(audio.duration);
  });

  audio.addEventListener('timeupdate', () => {
    if (!seekUpdating) {
      seekBar.value = Math.floor(audio.currentTime);
      currentTimeEl.textContent = formatTime(audio.currentTime);
    }
  });

  audio.addEventListener('ended', () => {
    if (repeat) {
      audio.currentTime = 0;
      play();
    } else {
      next();
    }
  });

  // seek interactions
  seekBar.addEventListener('input', () => {
    seekUpdating = true;
    currentTimeEl.textContent = formatTime(seekBar.value);
  });

  seekBar.addEventListener('change', () => {
    audio.currentTime = seekBar.value;
    seekUpdating = false;
  });

  // helpers
  function formatTime(sec) {
    if (!sec || isNaN(sec)) return '0:00';
    sec = Math.floor(sec);
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s.toString().padStart(2,'0')}`;
  }

  function escapeHtml(text){
    return String(text || '').replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
  }

  // init
  loadSongs();
})();
