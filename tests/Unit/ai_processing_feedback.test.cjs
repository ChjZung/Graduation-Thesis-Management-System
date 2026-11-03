const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const source=fs.readFileSync(require('node:path').join(__dirname,'../../public/js/ai_teacher_report.js'),'utf8')
 .replace('window.huitTeacherReportUi={scan};','window.huitTeacherReportUi={scan,problem,renderProblem,detail,title,hideProcessingBanner};');
const window={};
vm.runInNewContext(source,{window,document:{readyState:'loading',addEventListener(){},getElementById(){return null;},createElement(){return {setAttribute(){}};}},setInterval(){}});
const ui=window.huitTeacherReportUi;
test('input mismatch is explained as a preflight stop, including old records without error codes',()=>{
 for(const state of [
  {status:'partial',errors:[{code:'input_changed'}]},
  {status:'partial',errors:[],message:'Tệp, yêu cầu hoặc cấu hình xử lý đã thay đổi. Hãy chọn Phân tích lại'},
 ]) {
  const message=ui.problem({state});
  assert.match(message,/dừng trước khi gọi AI/);
  assert.match(message,/khởi động lại worker/);
 }
});
test('rate limit explains waiting and retry without claiming daily quota',()=>{
 const e={state:{status:'waiting',errors:[{code:'rate_limit',quota:'unknown'}],next_retry_at:1793685529,progress:{map_done:8,map_total:28}}};
 assert.match(ui.problem(e),/API đang giới hạn/);
 assert.match(ui.problem(e),/Tự thử tiếp lúc/);
 assert.doesNotMatch(ui.problem(e),/chạm hạn mức ngày/);
 assert.match(ui.detail(e),/8\/28.*tạm dừng/);
});
test('terminal authentication errors do not promise automatic retry',()=>{
 const message=ui.problem({state:{status:'partial',summary_status:'completed',errors:[{code:'authentication'}]}});
 assert.match(message,/API key không hợp lệ/);
 assert.match(message,/không tự thử lại/);
 assert.doesNotMatch(message,/Tự thử tiếp lúc/);
});
test('unknown error content never exposes internal API messages',()=>{
 const message=ui.problem({state:{status:'failed',errors:[{code:'other',message:'secret-key <script>alert(1)</script>'}]}});
 assert.match(message,/Một bước xử lý AI gặp lỗi/);
 assert.doesNotMatch(message,/secret-key|<script>/);
});
test('warning appears and clears when processing completes',()=>{
 let notice;
 const container={querySelector(){return notice;},appendChild(n){notice=n;n.remove=()=>{notice=undefined;};}};
 ui.renderProblem(container,{state:{status:'waiting',errors:[{code:'worker_error'}]}});
 assert.equal(notice.hidden,false);
 assert.match(notice.textContent,/xử lý nền gặp lỗi/);
 ui.renderProblem(container,{network:true,denied:true,state:{status:'completed',errors:[{code:'worker_error'}]}});
 assert.equal(notice,undefined);
 ui.renderProblem(container,{state:{status:'completed',errors:[{code:'rate_limit'}]}});
 assert.equal(notice,undefined);
});
test('worker and network outages have actionable separate notices',()=>{
 assert.match(ui.problem({state:{status:'running',worker_running:false}}),/quản trị viên kiểm tra/);
 assert.match(ui.problem({network:true,state:{status:'running'}}),/lỗi kết nối/);
 assert.match(ui.problem({denied:true,state:{status:'partial'}}),/đăng nhập lại/);
});

test('legacy reader validation errors are not presented as provider rejection',()=>{
 const message=ui.problem({state:{status:'partial',errors:[{code:'invalid_request',engine_code:'reader_too_long'}]}});
 assert.match(message,/độ dài/);assert.doesNotMatch(message,/API không chấp nhận/);
});

test('partial run cannot claim the saved summary means current run completed',()=>{
 assert.equal(ui.title({state:{status:'partial',summary_status:'completed',errors:[{code:'invalid_request',engine_code:'reader_too_long'}]}}),'Lượt phân tích chưa hoàn tất');
});

test('sidebar opens the matching report version and requirements tab',()=>{
 const calls=[];
 const tab={closest(){return null;},scrollIntoView(){calls.push('scroll');},focus(){calls.push('focus');}};
 const versionButton={id:'version-button'};
 const body={hidden:true};
 const card={dataset:{reportKey:'latest'},closest(selector){return selector==='dialog'?null:{id:'pills-sv-v3'};},querySelector(selector){return selector==='.ai-details'?body:selector==='[data-ai-result-tab="requirements"]'?tab:null;}};
 const bs={Tab:{getOrCreateInstance(target){return {show(){calls.push(target===tab?'requirements':'version');}};}}};
 const win={bootstrap:bs};
 const doc={readyState:'loading',addEventListener(){},getElementById(id){return id==='pills-sv-v3-tab'?versionButton:null;},querySelectorAll(){return [card];}};
 const instrumented=source.replace('window.huitTeacherReportUi={scan,problem,renderProblem,detail,title,hideProcessingBanner};','window.huitTeacherReportUi={openRequirements};');
 vm.runInNewContext(instrumented,{window:win,document:doc,bootstrap:bs,setInterval(){},requestAnimationFrame(fn){fn();},matchMedia(){return {matches:true};}});
 win.huitTeacherReportUi.openRequirements({closest(){return {dataset:{reportKey:'latest'}};}});
 assert.equal(body.hidden,false);
 assert.deepEqual(calls,['version','requirements','scroll','focus']);
});

test('large status banner hides after completion or a terminal review-only gap',()=>{
 assert.equal(ui.hideProcessingBanner({status:'completed',errors:[{code:'old'}]}),true);
 assert.equal(ui.hideProcessingBanner({status:'partial',summary_status:'completed',errors:[{stage:'requirements_review',engine_code:'review_incomplete'}]}),true);
 assert.equal(ui.hideProcessingBanner({status:'running',summary_status:'completed',errors:[]}),false);
 assert.equal(ui.hideProcessingBanner({status:'partial',summary_status:'completed',errors:[{stage:'summary',engine_code:'reader_too_long'}]}),false);
 assert.equal(ui.hideProcessingBanner({status:'partial',summary_status:'completed',errors:[{stage:'requirements_review',code:'authentication'}]}),false);
});

test('coverage sidebar follows selected version, including a version without AI results',()=>{
 let selected='v1';let rendered;
 const template=value=>({content:{cloneNode(){return value;}}});
 const pane={get dataset(){return {reportKey:selected};},querySelector(){return template('no-results-'+selected);}};
 const sidebar={dataset:{reportKey:'v2'},replaceChildren(value){rendered=value;}};
 const v1={dataset:{reportKey:'v1'},closest(){return null;},querySelector(){return template('80%-v1');}};
 const v2={dataset:{reportKey:'v2'},closest(){return null;},querySelector(){return null;}};
 const doc={readyState:'loading',addEventListener(){},getElementById(){return null;},querySelector(){return pane;},querySelectorAll(selector){return selector==='[data-ai-coverage-sidebar]'?[sidebar]:[v1,v2];}};
 const win={};
 const code=source.replace(/window.huitTeacherReportUi=\{scan,problem,[^;]+\};/,'window.huitTeacherReportUi={syncCoverage};');
 vm.runInNewContext(code,{window:win,document:doc,setInterval(){}});
 win.huitTeacherReportUi.syncCoverage();assert.equal(sidebar.dataset.reportKey,'v1');assert.equal(rendered,'80%-v1');
 selected='v2';win.huitTeacherReportUi.syncCoverage();assert.equal(sidebar.dataset.reportKey,'v2');assert.equal(rendered,'no-results-v2');
 selected='v1';win.huitTeacherReportUi.syncCoverage();assert.equal(rendered,'80%-v1');
});
