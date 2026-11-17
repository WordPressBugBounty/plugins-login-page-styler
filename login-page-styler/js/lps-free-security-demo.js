/* Free-only demo: no AJAX, no POST, no credential or session operations. */
document.addEventListener('DOMContentLoaded', function () {
  const isPreview = document.querySelector('.lps-free-pro-banner');
  if (!isPreview) return;
  document.querySelectorAll('.lps-free-demo-form').forEach(function (form) {
    form.addEventListener('submit', function (e) { e.preventDefault(); show('Preview only — upgrade to Pro to save changes.'); });
  });
  let status = document.createElement('p'); status.className='lps-free-demo-status'; status.setAttribute('role','status');
  isPreview.insertAdjacentElement('afterend', status);
  function show(message) { status.textContent = message; }
  document.querySelectorAll('[data-lps-demo-action]').forEach(function (button) {
    button.addEventListener('click', function () { show('Preview only — this action is available in Pro.'); });
  });
  let generate=document.getElementById('generate_temp_url_button');
  if (generate) generate.addEventListener('click', function () {
    const url=document.getElementById('lps_temp_access_url');
    if(url) url.value='https://example.com/?temp_access=DEMO-ONLY';
    show('Example only — this link cannot sign anyone in. No URL was generated.');
  });
  ['revoke_temp_access_button','lps_terminate_temp_sessions','lps_revoke_and_terminate'].forEach(function(id){
    let b=document.getElementById(id); if(b) b.addEventListener('click',function(){show('Preview only — no access link or session was changed.');});
  });
  document.querySelectorAll('.lps-block-card button').forEach(function(b){
    b.addEventListener('click',function(){show('Preview only — IP and country rules are not saved or applied in Free.');});
  });
});
