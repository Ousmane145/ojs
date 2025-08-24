// plugins/generic/premiumSubmissionHelper/js/premiumHelper.js
(function () {
  function findAbstractField() {
    const candidates = [
      'textarea[name="abstract"]',
      'textarea#abstract',
      'textarea[name*="abstract"]',
      'textarea[aria-label*="Résumé"]',
      'textarea[aria-label*="Abstract"]',
    ];
    for (const sel of candidates) {
      const el = document.querySelector(sel);
      if (el) return el;
    }
    // fallback: longest textarea on the page
    const areas = Array.from(document.querySelectorAll('textarea'));
    if (areas.length === 0) return null;
    return areas.sort((a, b) => (b.value?.length || 0) - (a.value?.length || 0))[0];
  }

  function ensureUI() {
    const abstract = findAbstractField();
    if (!abstract) return;

    if (abstract.dataset.premiumHelperAttached) return; // avoid duplicates
    abstract.dataset.premiumHelperAttached = "1";

    const container = document.createElement('div');
    container.className = 'pkpFormField__control premium-helper-container';
    container.style.marginTop = '0.5rem';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pkpButton pkpButton--default';
    btn.textContent = 'Analyser avec IA (Premium)';

    const result = document.createElement('div');
    result.className = 'premium-helper-result';
    result.style.marginTop = '0.5rem';

    btn.addEventListener('click', async () => {
      const text = abstract.value.trim();
      if (!text) {
        result.textContent = 'Veuillez saisir un résumé avant de lancer l’analyse.';
        return;
      }
      result.textContent = 'Analyse en cours…';
      try {
        const resp = await fetch('/api/v1/premium-submission-helper/analyze', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ abstract: text })
        });
        const data = await resp.json();
        if (!resp.ok) {
          result.textContent = data?.message || 'Erreur lors de l’analyse.';
          return;
        }
        result.innerHTML = `Score IA : <strong>${(data.score * 100).toFixed(0)}%</strong> — ${data.message}`;
      } catch (e) {
        result.textContent = 'Erreur réseau inattendue.';
      }
    });

    container.appendChild(btn);
    container.appendChild(result);
    abstract.parentElement?.appendChild(container);
  }

  // Try now, and also after SPA updates
  document.addEventListener('DOMContentLoaded', ensureUI);
  setTimeout(ensureUI, 1000);
  const obs = new MutationObserver(() => ensureUI());
  obs.observe(document.documentElement, { childList: true, subtree: true });
})();
