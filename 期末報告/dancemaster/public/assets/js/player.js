/* =====================================================================
 * DanceMaster 核心播放器
 * 統一封裝 YouTube IFrame API 與 本地 <video>，對外提供相同介面：
 *   play() pause() getTime() setTime() getDuration() setSpeed()
 * 三大賣點：精細倍速 / 一鍵鏡面 / A-B 段落循環
 * ===================================================================== */

(function () {
  const V = window.DM_VIDEO || { source: 'youtube', src: '' };

  // 統一播放器介面（adapter pattern）
  let adapter = null;
  let isPlaying = false;

  // ---- A/B 循環狀態 ----
  let pointA = null;   // 秒
  let pointB = null;
  let loopOn = false;

  // ---- DOM ----
  const $ = (id) => document.getElementById(id);
  const btnPlay   = $('btnPlay');
  const seekBar   = $('seekBar');
  const timeLabel = $('timeLabel');
  const speedPills= $('speedPills');
  const btnMirror = $('btnMirror');
  const mirrorFlag= $('mirrorFlag');
  const videoStage= $('videoStage');
  const btnSetA   = $('btnSetA');
  const btnSetB   = $('btnSetB');
  const btnLoop   = $('btnLoop');
  const btnClearAB= $('btnClearAB');
  const abDisplay = $('abDisplay');
  const btnCheckin= $('btnCheckin');
  const checkinMsg= $('checkinMsg');
  // ---- 沉浸模式 / 覆蓋層 ----
  const playerLayout = $('playerLayout');
  const clickCatcher = $('clickCatcher');
  const centerPlay   = $('centerPlay');
  const btnImmersive = $('btnImmersive');
  const btnExit      = $('btnExit');
  let hideTimer = null;

  /* ---------- 時間格式 ---------- */
  function fmt(t) {
    if (isNaN(t) || t < 0) t = 0;
    const m = Math.floor(t / 60);
    const s = Math.floor(t % 60);
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  /* =================================================================
   * 本地 mp4 adapter
   * ================================================================= */
  function buildLocalAdapter(videoEl) {
    return {
      play: () => videoEl.play(),
      pause: () => videoEl.pause(),
      getTime: () => videoEl.currentTime,
      setTime: (t) => { videoEl.currentTime = t; },
      getDuration: () => videoEl.duration || 0,
      setSpeed: (r) => { videoEl.playbackRate = r; },
      el: videoEl,
    };
  }

  /* =================================================================
   * YouTube adapter（透過 IFrame API）
   * ================================================================= */
  let ytReady = false;
  function buildYTAdapter(player) {
    return {
      play: () => player.playVideo(),
      pause: () => player.pauseVideo(),
      getTime: () => player.getCurrentTime() || 0,
      setTime: (t) => player.seekTo(t, true),
      getDuration: () => player.getDuration() || 0,
      setSpeed: (r) => player.setPlaybackRate(r),
      el: player.getIframe(),
    };
  }

  /* =================================================================
   * 初始化播放器（依來源分流）
   * ================================================================= */
  function initLocal() {
    const v = $('localPlayer');
    adapter = buildLocalAdapter(v);
    v.addEventListener('loadedmetadata', onReady);
    v.addEventListener('play',  () => setPlayUI(true));
    v.addEventListener('pause', () => setPlayUI(false));
    bindControls();
  }

  // YouTube API 會自動呼叫這個全域函式
  window.onYouTubeIframeAPIReady = function () {
    const holder = $('ytPlayer');
    if (!holder) return;
    const vid = holder.getAttribute('data-video-id');
    new YT.Player('ytPlayer', {
      videoId: vid,
      playerVars: { controls: 0, rel: 0, modestbranding: 1, playsinline: 1 },
      events: {
        onReady: (e) => {
          adapter = buildYTAdapter(e.target);
          ytReady = true;
          onReady();
          bindControls();
        },
        onStateChange: (e) => {
          if (e.data === YT.PlayerState.PLAYING) setPlayUI(true);
          if (e.data === YT.PlayerState.PAUSED || e.data === YT.PlayerState.ENDED) setPlayUI(false);
        }
      }
    });
  };

  /* ---------- 播放鍵 UI ---------- */
  function setPlayUI(playing) {
    isPlaying = playing;
    btnPlay.textContent = playing ? '⏸' : '▶';
    btnPlay.classList.toggle('playing', playing);
    syncCenter();
    poke();
  }

  /* =================================================================
   * 共用控制邏輯（兩種來源共用）
   * ================================================================= */
  function onReady() {
    updateLoop();
  }

  function bindControls() {
    if (bindControls._done) return; // 避免重複綁定
    bindControls._done = true;

    // 播放 / 暫停
    btnPlay.addEventListener('click', () => {
      isPlaying ? adapter.pause() : adapter.play();
    });

    // 進度條拖動
    seekBar.addEventListener('input', () => {
      const d = adapter.getDuration();
      adapter.setTime((seekBar.value / 100) * d);
    });

    // 精細倍速
    speedPills.querySelectorAll('button').forEach((b) => {
      b.addEventListener('click', () => {
        speedPills.querySelectorAll('button').forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        adapter.setSpeed(parseFloat(b.dataset.speed));
      });
    });

    // 一鍵鏡面：用 CSS scaleX(-1) 翻轉整個影片舞台
    btnMirror.addEventListener('click', () => {
      const on = videoStage.classList.toggle('mirrored');
      btnMirror.classList.toggle('active', on);
      mirrorFlag.classList.toggle('show', on);
    });

    // A/B 標記
    btnSetA.addEventListener('click', () => {
      pointA = adapter.getTime();
      if (pointB !== null && pointA >= pointB) pointB = null; // A 必須在 B 之前
      refreshAB();
    });
    btnSetB.addEventListener('click', () => {
      pointB = adapter.getTime();
      if (pointA !== null && pointB <= pointA) pointA = null;
      refreshAB();
    });
    btnLoop.addEventListener('click', () => {
      loopOn = !loopOn;
      updateLoop();
    });
    btnClearAB.addEventListener('click', () => {
      pointA = pointB = null; loopOn = false;
      updateLoop(); refreshAB();
    });

    // 打卡（實際 POST 到 checkin.php 寫入 JSON；私人影片無打卡鈕）
    if (btnCheckin) btnCheckin.addEventListener('click', doCheckin);

    bindImmersive();

    // 進度更新迴圈
    setInterval(tick, 250);
  }

  /* =================================================================
   * 透明點擊層 + 中央播放浮標 + 沉浸練習模式
   * ================================================================= */
  function togglePlay() {
    if (!adapter) return;
    isPlaying ? adapter.pause() : adapter.play();
  }

  // 暫停時顯示中央播放浮標（接管來源原生中央鈕的視覺角色）
  function syncCenter() {
    if (!centerPlay) return;
    centerPlay.classList.toggle('show', !isPlaying);
  }

  // 沉浸模式：滑鼠不動 2.5s 自動淡出控制列，移動再浮現
  function poke() {
    if (!playerLayout || !playerLayout.classList.contains('immersive')) return;
    playerLayout.classList.remove('idle');
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
      if (isPlaying) playerLayout.classList.add('idle');
    }, 2500);
  }

  function enterImmersive() {
    if (!playerLayout) return;
    playerLayout.classList.add('immersive');
    document.body.classList.add('immersive-lock');
    // 嘗試原生全螢幕（失敗也不影響 CSS 沉浸層）
    const fsEl = playerLayout;
    if (fsEl.requestFullscreen) fsEl.requestFullscreen().catch(() => {});
    poke();
  }

  function exitImmersive() {
    if (!playerLayout) return;
    playerLayout.classList.remove('immersive', 'idle');
    document.body.classList.remove('immersive-lock');
    clearTimeout(hideTimer);
    if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
  }

  function bindImmersive() {
    if (bindImmersive._done) return;
    bindImmersive._done = true;

    // 透明層點擊＝播放/暫停（點一下並叫出控制列）
    if (clickCatcher) clickCatcher.addEventListener('click', () => { togglePlay(); poke(); });
    if (centerPlay)   centerPlay.addEventListener('click', () => { togglePlay(); poke(); });

    if (btnImmersive) btnImmersive.addEventListener('click', enterImmersive);
    if (btnExit)      btnExit.addEventListener('click', exitImmersive);

    // 沉浸模式下的滑鼠移動／觸控唤醒控制列
    ['mousemove', 'touchstart', 'keydown'].forEach(ev =>
      document.addEventListener(ev, poke, { passive: true })
    );

    // Esc 退出（並處理使用者直接按 F11/瀏覽器退出全螢幕的狀況）
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && playerLayout.classList.contains('immersive')) exitImmersive();
    });
    document.addEventListener('fullscreenchange', () => {
      if (!document.fullscreenElement && playerLayout.classList.contains('immersive')) {
        playerLayout.classList.remove('immersive', 'idle');
        document.body.classList.remove('immersive-lock');
      }
    });
  }

  function refreshAB() {
    abDisplay.textContent =
      `A：${pointA !== null ? fmt(pointA) : '—'}　B：${pointB !== null ? fmt(pointB) : '—'}`;
    // 兩點都齊全才能開啟循環
    btnLoop.disabled = !(pointA !== null && pointB !== null);
    if (btnLoop.disabled) { loopOn = false; updateLoop(); }
  }

  function updateLoop() {
    btnLoop.classList.toggle('on', loopOn);
    btnLoop.textContent = loopOn ? '🔁 A/B 循環：開' : '🔁 A/B 循環：關';
  }

  /* ---------- 每 250ms 更新一次 ---------- */
  function tick() {
    if (!adapter) return;
    const t = adapter.getTime();
    const d = adapter.getDuration();
    if (d > 0) {
      seekBar.value = (t / d) * 100;
      timeLabel.textContent = `${fmt(t)} / ${fmt(d)}`;
    }
    // A/B 循環核心：超過 B 就跳回 A
    if (loopOn && pointA !== null && pointB !== null) {
      if (t >= pointB || t < pointA - 0.5) {
        adapter.setTime(pointA);
      }
    }
  }

  /* ---------- 打卡（真實寫入後端） ---------- */
  function doCheckin() {
    const id = btnCheckin.dataset.id;
    btnCheckin.disabled = true;
    checkinMsg.textContent = '記錄中…';
    fetch('checkin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const el = document.getElementById('practiceCount');
        if (el) el.textContent = data.count;
        checkinMsg.textContent = '已記錄今日練舞 ✓';
      } else {
        checkinMsg.textContent = '打卡失敗，請重試';
      }
    })
    .catch(() => { checkinMsg.textContent = '連線失敗，請重試'; })
    .finally(() => {
      setTimeout(() => { btnCheckin.disabled = false; checkinMsg.textContent = ''; }, 2500);
    });
  }

  /* =================================================================
   * 啟動
   * ================================================================= */
  if (V.source === 'youtube') {
    // 等 onYouTubeIframeAPIReady 觸發
  } else {
    initLocal();
  }
})();
