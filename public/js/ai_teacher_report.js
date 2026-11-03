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
 function restoreTabs(card){let key;try{key=sessionStorage.getItem(storageKey(card));}catch{} if(key==='work')key='chapters';if(['chapters','requirements'].includes(key)){const tab=card.querySelector('[data-ai-result-tab="'+key+'"]');if(tab && window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(tab).show();}}
 function clock(e){if(e.finished!=null)return e.finished;return Math.max(0,Math.floor((Date.now()-(e.anchor||Date.now()))/1000));}
 function stamp(seconds){return String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');}
 function problem(e){
  const state=e.state||{},status=state.status;
  if(['completed','cancelled'].includes(status))return '';
  if(e.denied)return 'Phiên đăng nhập đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.';
  if(e.network)return 'Không cập nhật được trạng thái do lỗi kết nối. Hệ thống sẽ kiểm tra lại; tác vụ nền có thể vẫn đang chạy.';
  const errors=Array.isArray(state.errors)?state.errors:[];
  if(errors.some(x=>x.code==='input_changed') || (status==='partial' && !errors.length && /Tệp, yêu cầu hoặc cấu hình xử lý đã thay đổi/.test(state.message||'')))return 'Đầu vào hoặc cấu hình xử lý đã thay đổi so với lượt đang chờ. Lượt này dừng trước khi gọi AI; đây không phải lỗi hết thời gian. Vui lòng tải lại trang rồi chọn Phân tích lại. Nếu lặp lại, quản trị viên cần khởi động lại worker để nạp mã và cấu hình mới.';
  if(status==='running'&&errors.length&&errors.every(x=>x.stage==='process'&&x.code==='timeout'))return '';
  if(status==='waiting'&&errors.length&&errors.every(x=>x.code==='budget_exhausted'))return '';
  const labels={timeout:'Lượt xử lý nền đã hết thời gian. Các bước đã lưu được giữ để tiếp tục; không cần phân tích lại từ đầu.',output_validation:'Bản tóm tắt AI chưa đạt yêu cầu kiểm tra nội dung hoặc độ dài. Phần đọc đã lưu được giữ; có thể tiếp tục để tạo lại riêng đầu ra lỗi.',request_failed:'Không gửi được yêu cầu phân tích. Vui lòng tải lại trang để kiểm tra trạng thái trước khi thử lại.',rate_limit:'API đang giới hạn lượt gọi hoặc hạn mức sử dụng.',authentication:'API key không hợp lệ hoặc không có quyền sử dụng mô hình. Quản trị viên cần kiểm tra cấu hình.',invalid_request:'API không chấp nhận yêu cầu. Quản trị viên cần kiểm tra cấu hình mô hình và dữ liệu gửi.',input_too_large:'Nội dung gửi AI vượt giới hạn cho phép.',extraction_failed:'Không trích xuất được nội dung văn bản của tài liệu.',worker_error:'Tiến trình xử lý nền gặp lỗi.',budget_exhausted:'Đã chạm giới hạn xử lý của lượt hiện tại.'};
  const messages=[...new Set(errors.map(x=>((x.engine_code||'').startsWith('reader_')&&x.engine_code!=='reader_model_limit'&&x.engine_code!=='reader_input_limit'&&x.code!=='budget_exhausted'?labels.output_validation:labels[x.code])||(status==='waiting'?'Dịch vụ AI gặp lỗi tạm thời.':'Một bước xử lý AI gặp lỗi.')))];
  if(active(status)&&state.worker_running===false)messages.push('Tiến trình chạy nền hiện chưa hoạt động; cần quản trị viên kiểm tra.');
  if(!messages.length)return '';
  if(errors.some(x=>x.code==='rate_limit'&&x.quota==='day'))messages.push('Nhà cung cấp báo đã chạm hạn mức ngày.');
  if(status==='waiting'&&Number(state.next_retry_at)>0){
   const retryDate=new Date(Number(state.next_retry_at)*1000);
   messages.push('Tự thử tiếp lúc '+retryDate.toLocaleTimeString('vi-VN',{hour12:false})+' ('+retryDate.toLocaleDateString('vi-VN')+'). Không cần bấm phân tích lại.');
  }else if(['partial','failed'].includes(status))messages.push('Lượt này đã dừng; không tự thử lại. Kết quả đã lưu được giữ.');
  return messages.join(' ');
 }
 function renderProblem(container,e){
  let notice=container.querySelector('[data-ai-processing-problem]');
  const message=problem(e);
  if(!message){notice?.remove();return;}
  if(!notice){notice=document.createElement('p');notice.setAttribute('data-ai-processing-problem','');notice.setAttribute('role','status');notice.setAttribute('aria-live','polite');notice.className='alert alert-warning mt-2 mb-2';container.appendChild(notice);}
  notice.hidden=false;notice.textContent=message;
 }
 function title(e){if(e.state?.status==='waiting'&&!!problem(e))return 'Đang tạm chờ — có sự cố cần lưu ý';if(e.state?.status==='partial'&&e.state?.errors?.some(x=>x.code==='budget_exhausted'))return 'Lượt phân tích mới đã dừng';if(e.state?.status==='partial')return 'Lượt phân tích chưa hoàn tất';if(e.network)return 'Đang kiểm tra kết nối';return {queued:'Đang chờ phân tích',running:'Đang phân tích báo cáo',waiting:'Đang tiếp tục xử lý',completed:'Đã có kết quả xử lý',partial:'Kết quả xử lý một phần',failed:'Không thể hoàn tất phân tích',cancelled:'Đã dừng phân tích'}[e.state?.status]||'Đang tiếp nhận yêu cầu';}
 function description(e){
  if(e.network)return 'Chưa cập nhật được trạng thái. Hệ thống sẽ kiểm tra lại; báo cáo và kết quả đã lưu được giữ.';
  if(e.denied)return 'Phiên đăng nhập đã thay đổi hoặc bạn không có quyền xem kết quả. Vui lòng đăng nhập lại.';
  const s=e.state?.status,errs=e.state?.errors||[];
  if(problem(e))return active(s)?'Kết quả đã lưu được giữ. Xem thông báo sự cố bên dưới.':'Lượt xử lý chưa hoàn tất. Xem nguyên nhân và hướng xử lý bên dưới.';
  if(s==='cancelled')return e.state?.message||'Đã dừng tiến trình phân tích. Bạn có thể bấm Phân tích lại bất cứ lúc nào.';
  if(s==='completed')return e.reused?'Đang dùng lại kết quả đã lưu cho báo cáo và yêu cầu hiện tại.':'Kết quả phân tích đã được lưu. Bạn có thể xem nội dung bên dưới.';
  if(s==='partial'&&errs.some(x=>x.code==='budget_exhausted'))return 'Lượt phân tích mới đã dừng ở giới hạn số lần tiếp tục. Kết quả đã lưu vẫn được giữ; chọn Tiếp tục từ tiến độ đã lưu để xử lý phần còn lại.';
  if(e.state?.worker_running===false)return 'Hệ thống đang chờ tiến trình phân tích hoạt động trở lại. Không cần nộp lại báo cáo.';
  if(s==='partial'&&e.state?.summary_status==='completed')return 'Bạn có thể xem bản tóm tắt và các dẫn chứng đã lưu bên dưới.';
  if(s==='partial'||s==='failed')return 'Một số nội dung chưa được xử lý xong. Kết quả hợp lệ đã được giữ. Bạn có thể thử lại hoặc liên hệ quản trị viên.';
  if(errs.some(x=>x.code==='rate_limit'))return 'Dịch vụ AI đang giới hạn lượt xử lý. Hệ thống giữ kết quả đã có và sẽ tiếp tục khi có thể.';
  return 'AI đang tổng hợp nội dung từ báo cáo. Quá trình này có thể mất vài phút. Bạn có thể tiếp tục sử dụng trang.';
 }
 function detail(e){const p=e.state?.progress||{},s=e.state?.status;if(s==='completed')return 'Đã hoàn tất xử lý và lưu kết quả.';
  const stage=String(p.stage||'');let step=/yêu cầu|dẫn chứng|đối chiếu/iu.test(stage)?'Đang đối chiếu yêu cầu của giảng viên.':/Map|map|extract/.test(stage)?'Đang đọc nội dung báo cáo.':'Đang tổng hợp nội dung báo cáo.';
  if(s==='waiting'&&problem(e))step='Đã tạm dừng xử lý để chờ thử tiếp.';
  if(stage.startsWith('Lập chỉ mục trang')&&s!=='waiting')step=stage+'. Đang chuẩn bị nguồn đối chiếu.';
  if(s==='queued')step='Yêu cầu đã được lưu, đang chờ đến lượt xử lý.';if(s==='submitting')step='Đang gửi yêu cầu phân tích.';
  return (p.map_total?`Đã đọc ${Math.min(p.map_done||0,p.map_total)}/${p.map_total} phần văn bản. `:'')+step;
 }
 function hideProcessingBanner(state){
  if(state?.status==='completed')return true;
  const errors=Array.isArray(state?.errors)?state.errors:[];
  return state?.status==='partial'&&state?.summary_status==='completed'&&errors.length>0&&
   errors.every(x=>x.stage==='requirements_review'&&x.engine_code==='review_incomplete');
 }
 function render(e){for(const card of cards(e)){
   card.querySelectorAll('[data-ai-review-submit]').forEach(b=>b.disabled=e.submitting||active(e.state?.status));
   card.querySelectorAll('[data-teacher-cancel-form]').forEach(f=>f.style.display=(active(e.state?.status)||e.submitting)?'inline-block':'none');
   card.querySelectorAll('[data-ai-processing-url]').forEach(b=>{
    const hide=hideProcessingBanner(e.state);b.hidden=hide;b.style.display=hide?'none':'';
    b.dataset.processingActive=active(e.state?.status)?'1':'0';b.dataset.processingStatus=e.state?.status||'idle';b.querySelector('[data-processing-title]').textContent=title(e);
    const summary=b.querySelector('[data-processing-summary-result]'),review=b.querySelector('[data-processing-review-result]');
    if(summary)summary.textContent='Tóm tắt báo cáo: '+(e.state?.summary_status==='completed'?(active(e.state?.status)?'Đã có bản tóm tắt để xem bên dưới; đối chiếu yêu cầu đang tiếp tục.':e.state?.status==='completed'?'Đã hoàn thành.':'Đang giữ kết quả đã lưu; lượt hiện tại chưa hoàn tất.'):(active(e.state?.status)?'Đang xử lý.':'Chưa hoàn tất.'));
    if(review){const r=e.state?.review_status;
      review.hidden=r==='not_configured';
      review.textContent='Đối chiếu yêu cầu: '+(r==='completed'?'Đã có kết quả — giảng viên cần kiểm tra lại.':r==='partial'?(active(e.state?.status)?'Đang đối chiếu yêu cầu.':(e.state?.review_has_results?'Có kết quả tham khảo — giảng viên vui lòng kiểm tra dẫn chứng.':'Chưa có kết quả để đối chiếu.')):(active(e.state?.status)?'Đang chờ hoặc đang xử lý.':'Chưa có kết quả hoàn tất.'));
    }

    b.querySelector('[data-processing-message]').textContent=problem(e)?'Kết quả đã lưu được giữ. Chọn Xem chi tiết tiến độ và thông báo để biết thêm thông tin.':description(e);
    b.querySelectorAll('[data-ai-processing-problem]').forEach(notice=>notice.remove());
    b.querySelector('[data-processing-step]').textContent=active(e.state?.status)?detail(e)+' Thời gian đã chờ: '+stamp(clock(e)):'';
    const open=b.querySelector('[data-open-ai-progress]');if(open){const stopped=['partial','failed'].includes(e.state?.status);open.hidden=!active(e.state?.status)&&!e.network&&!stopped;open.textContent='Xem chi tiết tiến độ và thông báo';}
    const result=b.querySelector('[data-ai-refresh-result]');if(result)result.hidden=e.state?.status!=='completed'||e.loaded;
   });
  }
  if(current!==e||!modalNode())return;
  const node=modalNode(),done=e.state?.status==='completed',cancelled=e.state?.status==='cancelled',failed=['partial','failed','cancelled'].includes(e.state?.status)||e.denied;
  renderProblem(node.querySelector('.modal-body'),e);
  node.querySelector('.modal-title').textContent=title(e);node.querySelector('[data-progress-description]').textContent=description(e);
  node.querySelector('[data-progress-detail]').textContent=cancelled?'Tiến trình phân tích đã được dừng.':(failed?'Đang giữ kết quả đã lưu; lượt hiện tại chưa hoàn tất.':detail(e));
  const savedNote=node.querySelector('[data-progress-saved-summary]');if(savedNote)savedNote.hidden=done||!cards(e).some(c=>c.dataset.showingSavedSummary==='1');
  node.querySelector('[data-progress-elapsed]').textContent=stamp(clock(e));
  node.querySelector('[data-progress-success]').hidden=!done;
  node.querySelector('[data-progress-timing]').hidden=done&&!!e.reused;
  node.querySelector('[data-progress-note]').textContent=done ? (e.reused?'Báo cáo và yêu cầu không thay đổi nên không cần gọi AI lại.':'Bạn có thể xem kết quả hoặc đóng cửa sổ này; kết quả đã được lưu.') : (cancelled ? 'Đã dừng phân tích. Bạn có thể bấm Thử lại để phân tích mới.' : 'Đóng cửa sổ không làm mất kết quả đã lưu. Nếu đang phân tích, tác vụ vẫn tiếp tục.');node.querySelector('[data-progress-view]').hidden=!done;
  const cancelBtn=node.querySelector('[data-progress-cancel]');if(cancelBtn)cancelBtn.hidden=!active(e.state?.status)&&!e.submitting;
  const retry=node.querySelector('[data-progress-retry]');retry.hidden=!failed||!!e.denied;
  retry.textContent=cancelled?'Phân tích lại':'Tiếp tục từ tiến độ đã lưu';
  const paused=e.state?.status==='waiting'&&!!problem(e);
  const bar=node.querySelector('.progress');bar.hidden=failed||done||paused;bar.setAttribute('aria-valuetext',done?'Hoàn tất xử lý':'Đang xử lý, chưa xác định phần trăm tổng thể');
  if(done){bar.setAttribute('aria-valuenow','100');bar.setAttribute('aria-valuemin','0');bar.setAttribute('aria-valuemax','100');}else bar.removeAttribute('aria-valuenow');
  node.querySelector('.progress-bar').classList.toggle('progress-bar-animated',!done&&!failed&&!paused);
 }
 function open(e){current=e;const node=modalNode();if(!node)return;if(node.parentElement!==document.body)document.body.appendChild(node);render(e);if(window.bootstrap?.Modal)bootstrap.Modal.getOrCreateInstance(node).show();}
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
   render(e);syncCoverage();return true;
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
 function syncCoverage(){
  const selectedPane=document.querySelector('#pills-tabContent-sv > .tab-pane.active');
  document.querySelectorAll('[data-ai-coverage-sidebar]').forEach(sidebar=>{
   const key=selectedPane?.dataset.reportKey||sidebar.dataset.reportKey;
   const card=[...document.querySelectorAll('.ai-teacher-report')].find(c=>c.dataset.reportKey===key&&!c.closest('dialog'));
   const template=card?.querySelector('template[data-ai-coverage-widget-source]')||selectedPane?.querySelector('template[data-ai-coverage-widget-source]');
   if(template){sidebar.dataset.reportKey=key;sidebar.replaceChildren(template.content.cloneNode(true));}
  });
 }
 function openCoverageRules(button){
  const content=button.closest('[data-ai-coverage-widget]')?.querySelector('template[data-ai-coverage-rules-content]');
  if(!content||!window.bootstrap?.Modal)return;
  const modal=document.createElement('div');modal.className='modal fade ai-coverage-modal';modal.tabIndex=-1;modal.setAttribute('aria-label','Thể lệ ước tính tỷ lệ');
  modal.innerHTML='<div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5">Thể lệ ước tính tỷ lệ</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button></div></div></div>';
  modal.querySelector('.modal-body').appendChild(content.content.cloneNode(true));document.body.appendChild(modal);
  modal.addEventListener('hidden.bs.modal',()=>{bootstrap.Modal.getInstance(modal)?.dispose();modal.remove();if(button.isConnected)button.focus({preventScroll:true});},{once:true});
  bootstrap.Modal.getOrCreateInstance(modal).show();
 }
 function openAiNotice(button){
  const content=button.closest('.ai-result-notice')?.querySelector('template[data-ai-notice-content]');
  if(!content||!window.bootstrap?.Modal)return;
  const noticeTitle=button.dataset.aiNoticeTitle||'Lưu ý về kết quả AI';
  const modal=document.createElement('div');modal.className='modal fade ai-coverage-modal ai-notice-modal';modal.tabIndex=-1;modal.setAttribute('aria-label',noticeTitle);
  modal.innerHTML='<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5">Lưu ý về kết quả AI</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đã hiểu</button></div></div></div>';
  modal.querySelector('.modal-title').textContent=noticeTitle;
  modal.querySelector('.modal-body').appendChild(content.content.cloneNode(true));
  (button.closest('dialog[open]')||document.body).appendChild(modal);
  modal.addEventListener('hidden.bs.modal',()=>{bootstrap.Modal.getInstance(modal)?.dispose();modal.remove();if(button.isConnected)button.focus({preventScroll:true});},{once:true});
  bootstrap.Modal.getOrCreateInstance(modal).show();
 }
 function openRequirements(button){
  const key=button.closest('[data-ai-coverage-widget]')?.dataset.reportKey;
  const card=[...document.querySelectorAll('.ai-teacher-report')].find(c=>c.dataset.reportKey===key&&!c.closest('dialog'));
  if(!card)return;
  const version=card.closest('[id^="pills-sv-v"]');
  if(version){const versionTab=document.getElementById(version.id+'-tab');if(versionTab&&window.bootstrap?.Tab)bootstrap.Tab.getOrCreateInstance(versionTab).show();}
  const body=card.querySelector('.ai-details');if(body)body.hidden=false;
  const toggle=card.querySelector('[data-ai-action="toggle"]');if(toggle){toggle.setAttribute('aria-expanded','true');const label=toggle.querySelector('.ai-toggle-label');if(label)label.textContent='Thu gọn';}
  const tab=card.querySelector('[data-ai-result-tab="requirements"]');if(!tab)return;
  if(window.bootstrap?.Tab)bootstrap.Tab.getOrCreateInstance(tab).show();
  requestAnimationFrame(()=>requestAnimationFrame(()=>{revealTab(tab);tab.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'center'});tab.focus({preventScroll:true});}));
 }
 document.addEventListener('click',event=>{
  const notice=event.target.closest('[data-ai-open-notice]');if(notice){event.preventDefault();openAiNotice(notice);}
  const rules=event.target.closest('[data-ai-coverage-rules]');if(rules){event.preventDefault();openCoverageRules(rules);}
  const requirements=event.target.closest('[data-ai-open-requirements]');if(requirements){event.preventDefault();openRequirements(requirements);}
 });
 function scan(){document.querySelectorAll('.ai-teacher-report').forEach(card=>{if(!initialized.has(card)){initialized.add(card);restoreTabs(card);}const b=card.querySelector('[data-ai-processing-url]');if(!b)return;
   let e=jobs.get(b.dataset.aiProcessingUrl);
   if(!e){e={url:b.dataset.aiProcessingUrl,key:card.dataset.reportKey,lastRevision:b.dataset.resultRevision||null,anchor:Date.now(),state:{status:b.dataset.processingStatus||'idle',summary_status:b.dataset.summaryStatus||null,review_status:b.dataset.reviewStatus||null,review_has_results:b.dataset.reviewHasResults==='1',queued_at:Number(b.dataset.processingStarted)||null,errors:JSON.parse(b.dataset.processingErrors||'[]')},loaded:true};jobs.set(e.url,e);
    // Background processing must not lock navigation or scrolling on page load.
    if(b.dataset.processingActive==='1'){e.loaded=false;poll(e);}}
   render(e);
  });syncCoverage();}
 async function submit(form,resume=false){const card=form.closest('.ai-teacher-report'),b=card.querySelector('[data-ai-processing-url]');if(!b)return;
  const e=jobs.get(b.dataset.aiProcessingUrl);if(!e||e.submitting||active(e.state?.status))return;
  e.submitting=true;e.loaded=false;e.reused=false;e.finished=null;e.anchor=Date.now();e.state={status:'submitting'};render(e);
  try{const body=new FormData(form);if(resume)body.set('fresh','0');const response=await fetch(form.action,{method:'POST',body,headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});
   if(!response.ok||response.redirected){if([401,403,419].includes(response.status))e.denied=true;throw Error('submit');}
   const data=await response.json();e.reused=!!data.reused;accept(e,data.state);if(active(e.state.status))poll(e);else{e.loaded=true;await refresh(e);}
  }catch{e.state={status:'partial',errors:[{code:'request_failed'}]};e.network=false;render(e);}finally{e.submitting=false;render(e);}
 }
 async function cancelJob(element){
  const card=element.closest?.('.ai-teacher-report') || (current ? cards(current)[0] : null);
  const form=card?.querySelector('[data-teacher-cancel-form]');
  const actionUrl=form?.action || card?.dataset.cancelUrl;
  if(!actionUrl) return;
  const csrfToken=form?.querySelector('input[name="_token"]')?.value || card?.dataset.csrfToken || '';
  const b=card?.querySelector('[data-ai-processing-url]');
  const e=b ? jobs.get(b.dataset.aiProcessingUrl) : current;
  if(e){ clearTimeout(e.timer); e.submitting=false; }
  try{
   const response=await fetch(actionUrl,{
    method:'POST',
    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrfToken},
    credentials:'same-origin'
   });
   if(response.ok){
    const data=await response.json();
    if(e){
     accept(e, data.state || {status:'cancelled',message:'Đã dừng tiến trình phân tích.'});
     e.loaded=true;
    }
   }
  }catch(err){
   if(form) form.submit();
  }
 }
 document.addEventListener('submit',event=>{
  const form=event.target;
  if(form.matches('[data-teacher-analyze-form]')){event.preventDefault();submit(form);}
  if(form.matches('[data-teacher-cancel-form]')){event.preventDefault();cancelJob(form);}
 });
 document.addEventListener('click',event=>{
  if(event.target.closest('[data-progress-cancel]')){event.preventDefault();cancelJob(event.target);}
 });
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
  if(event.target.closest('#pills-tab-sv')){syncCoverage();return;}
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
  if(event.target.closest('[data-progress-retry]')&&current){const f=cards(current).find(c=>!c.closest('dialog'))?.querySelector('[data-teacher-analyze-form]');if(f)submit(f,current.state?.status!=='cancelled');}
 });
 setInterval(()=>{for(const e of jobs.values())if(active(e.state?.status)||e.submitting)render(e);},1000);
 modalNode()?.addEventListener('hidden.bs.modal',()=>{if(current&&active(current.state?.status))try{sessionStorage.setItem('huit-ai-dismissed:'+current.url+':'+current.state.queued_at,'1');}catch{}});
 document.addEventListener('huit-ai-card-mounted',scan);
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',scan);else scan();
 window.huitTeacherReportUi={scan};
})();


