/* Premium-tab demonstrations are editable in admin only: no POST, AJAX or frontend code. */
document.addEventListener('DOMContentLoaded',function(){
  const ids=['content4','content5','content6','content7','content8'];
  ids.forEach(function(id){
    const panel=document.getElementById(id);if(!panel)return;
    const notice=panel.querySelector('.lps-free-tab-notice');if(!notice)return;
    const status=document.createElement('p');status.className='lps-free-demo-status';status.setAttribute('role','status');notice.insertAdjacentElement('afterend',status);
    panel.querySelectorAll('[data-lps-tab-demo]').forEach(function(button){button.addEventListener('click',function(){status.textContent='Preview only. This feature is available in Login Page Styler Pro; no change was saved or applied.';});});
    if(id==='content8'){
      const boxes=Array.from(panel.querySelectorAll('.lps-ip-checkbox'));const all=panel.querySelector('#lps-ip-select-all');const selected=panel.querySelector('#lps-ip-selected-count');
      function update(){const count=boxes.filter(b=>b.checked).length;if(selected)selected.textContent=count+' selected';if(all)all.checked=count===boxes.length;}
      if(all)all.addEventListener('change',function(){boxes.forEach(b=>{b.checked=all.checked;});update();});boxes.forEach(b=>b.addEventListener('change',update));
    }
  });
});
