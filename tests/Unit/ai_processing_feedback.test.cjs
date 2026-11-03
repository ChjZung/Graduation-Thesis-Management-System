const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const source=fs.readFileSync(require('node:path').join(__dirname,'../../public/js/ai_teacher_report.js'),'utf8')
 .replace('window.huitTeacherReportUi={scan};','window.huitTeacherReportUi={scan,problem,renderProblem,detail};');
const window={};
vm.runInNewContext(source,{window,document:{readyState:'loading',addEventListener(){},getElementById(){return null;},createElement(){return {setAttribute(){}};}},setInterval(){}});
const ui=window.huitTeacherReportUi;
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
 const container={querySelector(){return notice;},appendChild(n){notice=n;}};
 ui.renderProblem(container,{state:{status:'waiting',errors:[{code:'worker_error'}]}});
 assert.equal(notice.hidden,false);
 assert.match(notice.textContent,/xử lý nền gặp lỗi/);
 ui.renderProblem(container,{state:{status:'completed',errors:[{code:'worker_error'}]}});
 assert.equal(notice.hidden,true);
 assert.equal(notice.textContent,'');
});
test('worker and network outages have actionable separate notices',()=>{
 assert.match(ui.problem({state:{status:'running',worker_running:false}}),/quản trị viên kiểm tra/);
 assert.match(ui.problem({network:true,state:{status:'running'}}),/lỗi kết nối/);
 assert.match(ui.problem({denied:true,state:{status:'partial'}}),/đăng nhập lại/);
});
