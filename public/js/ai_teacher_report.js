/* Teacher presentation only. One status poller per authorized report URL. */
(() => {
 'use strict';
 if(window.huitTeacherReportUi) return;
 const initialized=new WeakSet(),tabScroll=new WeakMap(); const jobs=new Map(); let current=null;const modalNode=()=>document.getElementById('ai-teacher-progress');
 const active=s=>['queued','running','waiting'].includes(s);
 const cards=e=>[...document.querySelectorAll('.ai-teacher-report')].filter(c=>c.dataset.reportKey===e.key);
 const storageKey=c=>'huit-ai-tab:'+location.pathname+':'+c.dataset.reportKey;
 function revealTab(tab){
  const nav=tab.closest('.ai-report-tabs');if(!nav?.scrollTo)return;
  const bounds=nav.getBoundingClientRect(),button=tab.getBoundingClientRect();
  const delta=button.left<bounds.left ? button.left-bounds.left : button.right>bounds.right ? button.right-bounds.right : 0;
  if(delta)nav.scrollTo({left:Math.max(0,nav.scrollLeft+delta),behavior:'instant'});
 }
 function restoreTabs(card){let key;try{key=sessionStorage.getItem(storageKey(card));}catch{} if(['chapters','work','requirements'].includes(key)){const tab=card.querySelector('[data-ai-result-tab="'+key+'"]');if(tab && window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(tab).show();}}
 function clock(e){if(e.finished!=null)return e.finished;return Math.max(0,Math.floor((Date.now()-(e.anchor||Date.now()))/1000));}
 function stamp(seconds){return String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');}
 function title(e){if(e.state?.status==='partial'&&e.state?.summary_status==='completed')return 'Tóm tắt đã hoàn thành';if(e.network)return 'Đang kiểm tra kết nối';return {queued:'Đang chờ phân tích',running:'Đang phân tích báo cáo',waiting:'Đang tiếp tục xử lý',completed:'Đã có kết quả xử lý',partial:'Kết quả xử lý một phần',failed:'Không thể hoàn tất phân tích'}[e.state?.status]||'Đang tiếp nhận yêu cầu';}
 function description(e){
  if(e.network)return 'Chưa cập nhật được trạng thái. Hệ thống sẽ kiểm tra lại; báo cáo và kết quả đã lưu được giữ.';
  if(e.denied)return 'Phiên đăng nhập đã thay đổi hoặc bạn không có quyền xem kết quả. Vui lòng đăng nhập lại.';
  const s=e.state?.status,errs=e.state?.errors||[];
  if(s==='completed')return e.reused?'Đang dùng lại kết quả đã lưu cho báo cáo và yêu cầu hiện tại.':'Kết quả phân tích đã được lưu. Bạn có thể xem nội dung bên dưới.';
  if(e.state?.worker_running===false)return 'Hệ thống đang chờ tiến trình phân tích hoạt động trở lại. Không cần nộp lại báo cáo.';
  if(s==='partial'&&e.state?.summary_status==='completed')return 'Bạn có thể xem bản tóm tắt và các dẫn chứng đã lưu bên dưới.';
  if(s==='partial'||s==='failed')return 'Một số nội dung chưa được xử lý xong. Kết quả hợp lệ đã được giữ. Bạn có thể thử lại hoặc liên hệ quản trị viên.';
  if(errs.some(x=>x.code==='rate_limit'))return 'Dịch vụ AI đang giới hạn lượt xử lý. Hệ thống giữ kết quả đã có và sẽ tiếp tục khi có thể.';
  return 'AI đang tổng hợp nội dung từ báo cáo. Quá trình này có thể mất vài phút. Bạn có thể tiếp tục sử dụng trang.';
 }
 function detail(e){const p=e.state?.progress||{},s=e.state?.status;if(s==='completed')return 'Đã hoàn tất xử lý và lưu kết quả.';
  const stage=String(p.stage||'');let step=/yêu cầu|dẫn chứng|đối chiếu/iu.test(stage)?'Đang đối chiếu yêu cầu của giảng viên.':/Map|map|extract/.test(stage)?'Đang đọc nội dung báo cáo.':'Đang tổng hợp nội dung báo cáo.';
  if(s==='queued')step='Yêu cầu đã được lưu, đang chờ đến lượt xử lý.';if(s==='submitting')step='Đang gửi yêu cầu phân tích.';
  return (p.map_total?`Đã đọc ${Math.min(p.map_done||0,p.map_total)}/${p.map_total} phần văn bản. `:'')+step;
 }
 function render(e){for(const card of cards(e)){
   card.querySelectorAll('[data-ai-review-submit]').forEach(b=>b.disabled=e.submitting||active(e.state?.status));
   card.querySelectorAll('[data-ai-processing-url]').forEach(b=>{
    b.dataset.processingActive=active(e.state?.status)?'1':'0';b.dataset.processingStatus=e.state?.status||'idle';b.querySelector('[data-processing-title]').textContent=title(e);
    const summary=b.querySelector('[data-processing-summary-result]'),review=b.querySelector('[data-processing-review-result]');
    if(summary)summary.textContent='Tóm tắt báo cáo: '+(e.state?.summary_status==='completed'?'Đã hoàn thành.':(active(e.state?.status)?'Đang xử lý.':'Chưa hoàn tất.'));
    if(review){const r=e.state?.review_status;
      review.hidden=r==='not_configured';
      review.textContent='Đối chiếu yêu cầu: '+(r==='completed'?'Đã có kết quả — giảng viên cần kiểm tra lại.':r==='partial'?(active(e.state?.status)?'Đang đối chiếu yêu cầu.':(e.state?.review_has_results?'Có kết quả tham khảo — giảng viên vui lòng kiểm tra dẫn chứng.':'Chưa có kết quả để đối chiếu.')):(active(e.state?.status)?'Đang chờ hoặc đang xử lý.':'Chưa có kết quả hoàn tất.'));
    }

    b.querySelector('[data-processing-message]').textContent=description(e);
    b.querySelector('[data-processing-step]').textContent=active(e.state?.status)?detail(e)+' Thời gian đã chờ: '+stamp(clock(e)):'';
    const open=b.querySelector('[data-open-ai-progress]');if(open)open.hidden=!active(e.state?.status)&&!e.network;
    const result=b.querySelector('[data-ai-refresh-result]');if(result)result.hidden=e.state?.status!=='completed'||e.loaded;
   });
  }
  if(current!==e||!modalNode())return;
  const node=modalNode(),done=e.state?.status==='completed',failed=['partial','failed'].includes(e.state?.status)||e.denied;
  node.querySelector('.modal-title').textContent=title(e);node.querySelector('[data-progress-description]').textContent=description(e);
  node.querySelector('[data-progress-detail]').textContent=failed?'Các kết quả đã lưu vẫn được giữ để đối chiếu.':detail(e);
  node.querySelector('[data-progress-elapsed]').textContent=stamp(clock(e));
  node.querySelector('[data-progress-success]').hidden=!done;
  node.querySelector('[data-progress-timing]').hidden=done&&!!e.reused;
  node.querySelector('[data-progress-note]').textContent=done ? (e.reused?'Báo cáo và yêu cầu không thay đổi nên không cần gọi AI lại.':'Bạn có thể xem kết quả hoặc đóng cửa sổ này; kết quả đã được lưu.') : 'Đóng cửa sổ không làm mất kết quả đã lưu. Nếu đang phân tích, tác vụ vẫn tiếp tục.';node.querySelector('[data-progress-view]').hidden=!done;
  node.querySelector('[data-progress-retry]').hidden=!failed||!!e.denied;
  const bar=node.querySelector('.progress');bar.hidden=failed||done;bar.setAttribute('aria-valuetext',done?'Hoàn tất xử lý':'Đang xử lý, chưa xác định phần trăm tổng thể');
  if(done){bar.setAttribute('aria-valuenow','100');bar.setAttribute('aria-valuemin','0');bar.setAttribute('aria-valuemax','100');}else bar.removeAttribute('aria-valuenow');
  node.querySelector('.progress-bar').classList.toggle('progress-bar-animated',!done);
 }
 function open(e){current=e;render(e);if(window.bootstrap?.Modal)bootstrap.Modal.getOrCreateInstance(modalNode()).show();}
 function accept(e,data){e.state=data;e.network=false;if(!e.reused&&data.queued_at&&data.server_time){e.anchor=Date.now()-Math.max(0,data.server_time-data.queued_at)*1000;}
  if(active(data.status))e.finished=null;else e.finished=clock(e);render(e);
 }
 function schedule(e,ms){clearTimeout(e.timer);if(!e.denied&&active(e.state?.status))e.timer=setTimeout(()=>poll(e),ms);}
 async function refresh(e){if(e.refreshing)return e.refreshing;
  e.refreshing=(async()=>{const response=await fetch(location.href,{headers:{Accept:'text/html'},credentials:'same-origin',cache:'no-store'});if(!response.ok||response.redirected)throw Error('refresh');
   const parsed=new DOMParser().parseFromString(await response.text(),'text/html');
   const replacement=[...parsed.querySelectorAll('.ai-teacher-report')].find(c=>c.dataset.reportKey===e.key);if(!replacement)throw Error('missing-card');
   replacement.querySelectorAll('script').forEach(s=>s.remove());
   const targets=cards(e);if(!targets.length)return false;
   const pagePosition={x:window.scrollX,y:window.scrollY};
   for(const existing of targets){
    const expanded=!!existing.closest('dialog');
    const oldBody=existing.querySelector('.ai-details');
    const position={top:oldBody?.scrollTop||0,left:oldBody?.scrollLeft||0};
    const selection=[...existing.querySelectorAll('[role="tab"].active')].map(t=>({attribute:t.hasAttribute('data-ai-result-tab')?'data-ai-result-tab':t.hasAttribute('data-ai-work-tab')?'data-ai-work-tab':'data-ai-requirement-tab',value:t.dataset.aiResultTab||t.dataset.aiWorkTab||t.dataset.aiRequirementTab}));
    const detailKey=d=>d.dataset.documentNode ? 'node:'+d.dataset.documentNode :
      'detail:'+(d.querySelector('summary')?.textContent||'')+'|'+(d.parentElement.querySelector('h4,h3')?.textContent||'')+'|'+
      [...d.querySelectorAll('[data-citation-quote]')].map(b=>b.dataset.citationPage+':'+b.dataset.citationQuote).join('|');
    const opened=new Set([...existing.querySelectorAll('details[open]')].map(detailKey));
    const fresh=document.importNode(replacement,true);
    fresh.querySelectorAll('details').forEach(d=>{d.open=opened.has(detailKey(d));});
    if(expanded){
     fresh.classList.add('ai-summary-expanded');
     const ids=new Map();[fresh,...fresh.querySelectorAll('[id]')].forEach(n=>{if(n.id){ids.set(n.id,n.id+'-expanded');n.id+='-expanded';}});
     [fresh,...fresh.querySelectorAll('[aria-controls],[aria-labelledby],[data-bs-target]')].forEach(n=>{
      for(const attr of ['aria-controls','aria-labelledby','data-bs-target']){
       const value=n.getAttribute(attr);if(!value)continue;
       n.setAttribute(attr,value.split(' ').map(id=>{const hash=id.startsWith('#');return (hash?'#':'')+(ids.get(hash?id.slice(1):id)||(hash?id.slice(1):id));}).join(' '));
      }
     });
     fresh.querySelector('.ai-title').textContent='Tóm tắt báo cáo AI — Xem lớn';
     fresh.querySelector('[data-ai-action="open-large"]')?.remove();fresh.querySelector('[data-ai-action="toggle"]')?.remove();
     const close=existing.querySelector('[data-ai-action="close-large"]');if(close)fresh.querySelector('.ai-actions').appendChild(close.cloneNode(true));
    }
    const body=fresh.querySelector('.ai-details');if(body&&oldBody)body.hidden=oldBody.hidden;
    existing.replaceWith(fresh);if(expanded)fresh.closest('dialog').setAttribute('aria-labelledby',fresh.querySelector('.ai-title').id);initialized.add(fresh);
    for(const chosen of selection){const tab=fresh.querySelector('['+chosen.attribute+'="'+chosen.value+'"]');if(tab&&window.bootstrap?.Tab)bootstrap.Tab.getOrCreateInstance(tab).show();}
    if(!selection.length)restoreTabs(fresh);
    requestAnimationFrame(()=>{if(body){body.scrollTop=position.top;body.scrollLeft=position.left;}window.scrollTo({left:pagePosition.x,top:pagePosition.y,behavior:'instant'});});
   }
   render(e);return true;
  })().catch(()=>{for(const c of cards(e)){const b=c.querySelector('[data-ai-refresh-result]');if(b){b.hidden=false;b.textContent='Xem kết quả mới';}}return false;}).finally(()=>e.refreshing=null);
  return e.refreshing;
 }
 async function poll(e){if(e.polling||e.denied)return;e.polling=true;
  try{const response=await fetch(e.url,{headers:{Accept:'application/json'},cache:'no-store',credentials:'same-origin'});
   if(response.redirected||[401,403,404].includes(response.status)){e.denied=true;e.state={status:'partial'};render(e);return;}
   if(!response.ok)throw Error('status');const data=await response.json();accept(e,data);
   const changed=data.result_revision&&data.result_revision!==e.lastRevision;
   if(changed || (!active(data.status)&&data.status==='completed'&&!e.loaded)){
    if(await refresh(e)){e.lastRevision=data.result_revision;if(!active(data.status))e.loaded=true;}
   }
   if(!active(data.status))clearTimeout(e.timer);else schedule(e,5000);
  }catch{e.network=true;render(e);schedule(e,15000);}finally{e.polling=false;}
 }
 function scan(){document.querySelectorAll('.ai-teacher-report').forEach(card=>{if(!initialized.has(card)){initialized.add(card);restoreTabs(card);}const b=card.querySelector('[data-ai-processing-url]');if(!b)return;
   let e=jobs.get(b.dataset.aiProcessingUrl);
   if(!e){e={url:b.dataset.aiProcessingUrl,key:card.dataset.reportKey,lastRevision:b.dataset.resultRevision||null,anchor:Date.now(),state:{status:b.dataset.processingStatus||'idle',summary_status:b.dataset.summaryStatus||null,review_status:b.dataset.reviewStatus||null,review_has_results:b.dataset.reviewHasResults==='1',queued_at:Number(b.dataset.processingStarted)||null},loaded:true};jobs.set(e.url,e);
    if(b.dataset.processingActive==='1'){e.loaded=false;poll(e);if(!current){let dismissed=false;try{dismissed=sessionStorage.getItem('huit-ai-dismissed:'+e.url+':'+e.state.queued_at)==='1';}catch{}if(!dismissed)open(e);}}}
   render(e);
  });}
 async function submit(form){const card=form.closest('.ai-teacher-report'),b=card.querySelector('[data-ai-processing-url]');if(!b)return;
  const e=jobs.get(b.dataset.aiProcessingUrl);if(!e||e.submitting||active(e.state?.status))return;
  card.closest('dialog[open]')?.close();e.submitting=true;e.loaded=false;e.reused=false;e.finished=null;e.anchor=Date.now();e.state={status:'submitting'};open(e);render(e);
  try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});
   if(!response.ok||response.redirected){if([401,403,419].includes(response.status))e.denied=true;throw Error('submit');}
   const data=await response.json();e.reused=!!data.reused;accept(e,data.state);if(active(e.state.status))poll(e);else{e.loaded=true;await refresh(e);}
  }catch{e.state={status:'partial'};e.network=false;render(e);}finally{e.submitting=false;render(e);}
 }
 document.addEventListener('submit',event=>{const form=event.target;if(form.matches('[data-teacher-analyze-form]')){event.preventDefault();submit(form);}});
 // Reserve the current panel height during tab layout so scroll containers cannot
 // clamp to zero when Bootstrap briefly hides the old pane before showing the new.
 document.addEventListener('show.bs.tab',event=>{
  const tab=event.target.closest('[data-ai-result-tab], [data-ai-requirement-tab], [data-ai-work-tab]'),card=tab?.closest('.ai-teacher-report');if(!card)return;
  const container=tab.closest('.ai-details'),content=tab.dataset.aiWorkTab ? tab.closest('[data-ai-work-summary]').querySelector('[data-ai-work-content]') : tab.dataset.aiRequirementTab ? tab.closest('[data-teacher-requirements-summary]').querySelector('[data-ai-requirement-content]') : card.querySelector('.tab-content');
  if(!container||!content)return;
  if(tab.dataset.aiWorkTab||tab.dataset.aiRequirementTab){
   const siblings=[...tab.closest('[role="tablist"]').querySelectorAll('[role="tab"]')];
   const previous=event.relatedTarget;
   const direction=previous&&siblings.indexOf(tab)<siblings.indexOf(previous)?-1:1;
   content.style.setProperty('--ai-subtab-enter',(direction*14)+'px');
  }
  const snapshot={container,content,top:container.scrollTop,left:container.scrollLeft,x:window.scrollX,y:window.scrollY};
  const contentTop=content.getBoundingClientRect().top-container.getBoundingClientRect().top+container.scrollTop;
  content.style.minHeight=Math.max(0,container.scrollTop+container.clientHeight-contentTop)+'px';tabScroll.set(card,snapshot);
 });
 document.addEventListener('shown.bs.tab',event=>{
  const t=event.target.closest('[data-ai-result-tab], [data-ai-requirement-tab], [data-ai-work-tab]'),c=t?.closest('.ai-teacher-report');if(!c)return;
  if(t.dataset.aiResultTab)try{sessionStorage.setItem(storageKey(c),t.dataset.aiResultTab);}catch{}
  revealTab(t);
  const s=tabScroll.get(c);if(!s)return;tabScroll.delete(c);
  // Keep a shorter tab tall enough to preserve the reading position. Remove the
  // reservation only needs the visible viewport, not the entire previous panel.
  requestAnimationFrame(()=>{s.container.scrollTop=s.top;s.container.scrollLeft=s.left;window.scrollTo({left:s.x,top:s.y,behavior:'instant'});});
 });
 document.addEventListener('click',event=>{
  const card=event.target.closest('.ai-teacher-report'),b=card?.querySelector('[data-ai-processing-url]'),e=b&&jobs.get(b.dataset.aiProcessingUrl);
  if(event.target.closest('[data-open-ai-progress]')&&e)open(e);
  if(event.target.closest('[data-ai-refresh-result]')&&e){refresh(e);}
  if(event.target.closest('[data-progress-view]')&&current){const entry=current;bootstrap.Modal.getOrCreateInstance(modalNode()).hide();refresh(entry).then(()=>{const c=cards(entry).find(x=>!x.closest('dialog'));if(c){c.querySelector('.ai-details').hidden=false;c.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});}});}
  if(event.target.closest('[data-progress-retry]')&&current){const f=cards(current).find(c=>!c.closest('dialog'))?.querySelector('[data-teacher-analyze-form]');if(f)submit(f);}
 });
 setInterval(()=>{for(const e of jobs.values())if(active(e.state?.status)||e.submitting)render(e);},1000);
 modalNode()?.addEventListener('hidden.bs.modal',()=>{if(current&&active(current.state?.status))try{sessionStorage.setItem('huit-ai-dismissed:'+current.url+':'+current.state.queued_at,'1');}catch{}});
 document.addEventListener('huit-ai-card-mounted',scan);
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',scan);else scan();
 window.huitTeacherReportUi={scan};
})();


