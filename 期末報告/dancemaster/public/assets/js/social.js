/* DanceMaster 社群互動：按讚 / 訂閱 / 分享 */
(function () {
  async function post(url, data) {
    const body = new URLSearchParams(data);
    const res = await fetch(url, { method: 'POST', body });
    return res.json();
  }

  function toast(msg) {
    let t = document.getElementById('dm-toast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'dm-toast';
      t.className = 'dm-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2200);
  }

  document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const act = btn.dataset.act;

    // ---- 按讚 ----
    if (act === 'like') {
      btn.disabled = true;
      try {
        const r = await post('like.php', { id: btn.dataset.id });
        if (r.ok) {
          btn.classList.toggle('on', r.liked);
          btn.querySelector('.ic').textContent = r.liked ? '❤' : '🤍';
          const c = btn.querySelector('.like-count');
          if (c) c.textContent = r.count;
        } else {
          toast(r.msg || '操作失敗');
        }
      } catch (_) { toast('網路錯誤'); }
      btn.disabled = false;
    }

    // ---- 訂閱 ----
    if (act === 'subscribe') {
      btn.disabled = true;
      try {
        const r = await post('subscribe.php', { target: btn.dataset.target });
        if (r.ok) {
          btn.classList.toggle('on', r.subscribed);
          const lbl = btn.querySelector('.sub-label');
          if (lbl) lbl.textContent = r.subscribed ? '已訂閱' : '＋ 訂閱';
          const cnt = document.querySelector('[data-sub-count="' + btn.dataset.target + '"]');
          if (cnt) cnt.textContent = r.count;
          toast(r.subscribed ? '已訂閱 ✓' : '已取消訂閱');
        } else {
          toast(r.msg || '操作失敗');
        }
      } catch (_) { toast('網路錯誤'); }
      btn.disabled = false;
    }

    // ---- 分享 ----
    if (act === 'share') {
      const path = btn.dataset.url || location.pathname;
      const full = new URL(path, location.href).href;
      const title = btn.dataset.title || document.title;
      if (navigator.share) {
        try { await navigator.share({ title: title, url: full }); } catch (_) {}
      } else if (navigator.clipboard) {
        try { await navigator.clipboard.writeText(full); toast('已複製分享連結 ✓'); }
        catch (_) { prompt('複製這個連結分享：', full); }
      } else {
        prompt('複製這個連結分享：', full);
      }
    }
  });
})();
