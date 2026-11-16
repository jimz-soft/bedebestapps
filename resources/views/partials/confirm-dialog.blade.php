<style>
    #confirm-dialog{position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;padding:1.25rem}
    #confirm-dialog.is-open{display:flex}
    #confirm-dialog-backdrop{position:absolute;inset:0;background:rgba(4,4,10,.72);backdrop-filter:blur(8px)}
    #confirm-dialog-panel{
        position:relative;width:100%;max-width:26rem;border-radius:1.5rem;padding:1.5rem;
        background:linear-gradient(165deg,rgba(24,24,38,.98),rgba(12,12,20,.98));
        border:1px solid rgba(228,199,107,.28);
        box-shadow:0 0 0 1px rgba(139,92,246,.12),0 24px 60px rgba(0,0,0,.55),0 0 40px rgba(6,182,212,.08);
        transform:translateY(12px) scale(.96);opacity:0;transition:transform .2s ease,opacity .2s ease;
    }
    #confirm-dialog.is-open #confirm-dialog-panel{transform:translateY(0) scale(1);opacity:1}
    #confirm-dialog-icon{
        width:3rem;height:3rem;border-radius:9999px;display:grid;place-items:center;margin-bottom:1rem;
        background:linear-gradient(135deg,rgba(139,92,246,.25),rgba(6,182,212,.2));
        border:1px solid rgba(228,199,107,.35);color:#e4c76b;
    }
    #confirm-dialog[data-tone=danger] #confirm-dialog-icon{
        background:linear-gradient(135deg,rgba(239,68,68,.22),rgba(217,70,239,.15));
        border-color:rgba(248,113,113,.4);color:#fca5a5;
    }
    #confirm-dialog[data-tone=danger] #confirm-dialog-panel{border-color:rgba(248,113,113,.35)}
    #confirm-dialog-ok{
        background:linear-gradient(90deg,#8b5cf6,#06b6d4);color:#fff;font-weight:600;
    }
    #confirm-dialog[data-tone=danger] #confirm-dialog-ok{
        background:linear-gradient(90deg,#ef4444,#d946ef);
    }
</style>
<div id="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message" hidden>
    <div id="confirm-dialog-backdrop"></div>
    <div id="confirm-dialog-panel">
        <div id="confirm-dialog-icon" aria-hidden="true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
        </div>
        <h2 id="confirm-dialog-title" class="font-gaming text-xl mb-2 text-brand-gold">Konfirmasi</h2>
        <p id="confirm-dialog-message" class="text-sm text-gray-300 leading-relaxed mb-6"></p>
        <div class="flex flex-wrap justify-end gap-3">
            <button type="button" id="confirm-dialog-cancel" class="px-5 py-2 rounded-full bg-white/10 text-sm text-gray-200 hover:bg-white/15">Batal</button>
            <button type="button" id="confirm-dialog-ok" class="px-5 py-2 rounded-full text-sm">Ya, lanjutkan</button>
        </div>
    </div>
</div>
<script>
(function () {
    const dialog = document.getElementById('confirm-dialog');
    if (!dialog) return;

    const titleEl = document.getElementById('confirm-dialog-title');
    const messageEl = document.getElementById('confirm-dialog-message');
    const okBtn = document.getElementById('confirm-dialog-ok');
    const cancelBtn = document.getElementById('confirm-dialog-cancel');
    const dialogBackdrop = document.getElementById('confirm-dialog-backdrop');
    let pendingConfirm = null;
    let lastFocus = null;

    function closeDialog() {
        dialog.classList.remove('is-open');
        dialog.hidden = true;
        dialog.removeAttribute('data-tone');
        pendingConfirm = null;
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
        lastFocus = null;
    }

    function openDialog(options) {
        lastFocus = document.activeElement;
        titleEl.textContent = options.title || 'Konfirmasi';
        messageEl.textContent = options.message || '';
        okBtn.textContent = options.confirmLabel || 'Ya, lanjutkan';
        dialog.dataset.tone = options.tone || 'default';
        pendingConfirm = options.onConfirm || null;
        dialog.hidden = false;
        requestAnimationFrame(function () {
            dialog.classList.add('is-open');
            okBtn.focus();
        });
    }

    function confirmAction() {
        const run = pendingConfirm;
        closeDialog();
        if (typeof run === 'function') {
            run();
        }
    }

    cancelBtn.addEventListener('click', closeDialog);
    dialogBackdrop.addEventListener('click', closeDialog);
    okBtn.addEventListener('click', confirmAction);
    document.addEventListener('keydown', function (event) {
        if (!dialog.classList.contains('is-open')) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDialog();
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirmSkip === '1') {
            delete form.dataset.confirmSkip;
            return;
        }

        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        const message = (submitter && submitter.dataset.confirm) || form.dataset.confirm;
        if (!message) return;

        event.preventDefault();
        openDialog({
            title: (submitter && submitter.dataset.confirmTitle) || form.dataset.confirmTitle || 'Konfirmasi',
            message: message,
            confirmLabel: (submitter && submitter.dataset.confirmAction) || form.dataset.confirmAction || 'Ya, simpan',
            tone: ((submitter && submitter.dataset.confirmTone) || form.dataset.confirmTone || 'default'),
            onConfirm: function () {
                form.dataset.confirmSkip = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(submitter || undefined);
                } else {
                    form.submit();
                }
            },
        });
    });
})();
</script>
