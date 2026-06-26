/* DanceMaster Demo 贊助 Modal */
(function () {
  const modal = document.getElementById('sponsorModal');
  if (!modal) return;
  const nameEl = document.getElementById('sponsorName');
  const pills = document.getElementById('amountPills');
  const confirmBtn = document.getElementById('sponsorConfirm');
  const result = document.getElementById('sponsorResult');
  const closeBtn = document.getElementById('sponsorClose');
  let toUid = null, amount = 100;

  function open(to, name) {
    toUid = to;
    nameEl.textContent = name;
    result.hidden = true; result.textContent = '';
    confirmBtn.disabled = false;
    modal.hidden = false;
  }
  function close() { modal.hidden = true; }

  document.querySelectorAll('[id^="btnSponsor"]').forEach(b => {
    b.addEventListener('click', () => open(b.dataset.to, b.dataset.name));
  });
  closeBtn.addEventListener('click', close);
  modal.addEventListener('click', e => { if (e.target === modal) close(); });

  pills.addEventListener('click', e => {
    const p = e.target.closest('[data-amt]');
    if (!p) return;
    pills.querySelectorAll('button').forEach(x => x.classList.remove('active'));
    p.classList.add('active');
    amount = parseInt(p.dataset.amt, 10);
  });

  confirmBtn.addEventListener('click', async () => {
    confirmBtn.disabled = true;
    try {
      const res = await fetch('sponsor.php', {
        method: 'POST',
        body: new URLSearchParams({ to: toUid, amount: amount })
      });
      const r = await res.json();
      if (r.ok) {
        result.hidden = false;
        result.className = 'sponsor-result ok';
        result.innerHTML = '✓ 已 Demo 贊助 <b>$' + r.amount + '</b> 給 ' + r.to_name + '！<br>累計贊助：$' + r.total;
        const stat = document.querySelector('.sponsor-stat b');
        if (stat) stat.textContent = r.total;
      } else {
        result.hidden = false;
        result.className = 'sponsor-result err';
        result.textContent = r.msg || '贊助失敗';
        confirmBtn.disabled = false;
      }
    } catch (_) {
      result.hidden = false;
      result.className = 'sponsor-result err';
      result.textContent = '網路錯誤';
      confirmBtn.disabled = false;
    }
  });
})();
