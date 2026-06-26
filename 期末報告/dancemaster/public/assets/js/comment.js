/* DanceMaster 留言：AJAX 送出並即時插入 */
(function () {
  const form = document.getElementById('commentForm');
  if (!form) return;
  const text = document.getElementById('commentText');
  const list = document.getElementById('commentList');
  const cntEl = document.getElementById('cmtCount');
  const empty = document.getElementById('cmtEmpty');
  const vid = form.dataset.vid;

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const val = text.value.trim();
    if (!val) return;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
      const res = await fetch('comment.php', {
        method: 'POST',
        body: new URLSearchParams({ video_id: vid, text: val })
      });
      const r = await res.json();
      if (r.ok) {
        const c = r.comment;
        const proCls = c.user_role === 1 ? ' pro' : '';
        const proBadge = c.user_role === 1
          ? '<span class="verified-badge sm">✔</span><span class="pro-tag">認證舞者</span>'
          : '';
        const html =
          '<div class="comment' + proCls + ' just-added">' +
            '<span class="avatar sm" style="background:' + esc(c.color) + '">' + esc(c.initial) + '</span>' +
            '<div class="cmt-body">' +
              '<div class="comment-head">' +
                '<span class="name">' + esc(c.user_name) + '</span>' +
                proBadge +
                '<span class="cmt-date">' + esc(c.created) + '</span>' +
              '</div>' +
              '<p>' + esc(c.text) + '</p>' +
            '</div>' +
          '</div>';
        list.insertAdjacentHTML('afterbegin', html);
        if (empty) empty.style.display = 'none';
        if (cntEl) cntEl.textContent = (parseInt(cntEl.textContent, 10) || 0) + 1;
        text.value = '';
      } else {
        alert(r.msg || '留言失敗');
      }
    } catch (_) {
      alert('網路錯誤，請稍後再試');
    }
    btn.disabled = false;
  });
})();
