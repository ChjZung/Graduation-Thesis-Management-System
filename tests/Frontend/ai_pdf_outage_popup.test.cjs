const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const handlers = new Map();
const modalEvents = new Map();
const calls = [];
const document = {
    readyState: 'loading', querySelectorAll: () => [],
    addEventListener(name, callback) { handlers.set(name, [...(handlers.get(name) || []), callback]); },
    getElementById() { return {addEventListener: (name, callback) => modalEvents.set(name, callback)}; },
};
const context = {document, window:{}, sessionStorage:{setItem: () => {}}, setInterval:()=>0, setTimeout:()=>0,
    clearTimeout:()=>{}, fetch:async (...args)=>{calls.push(args);return {ok:true,json:async()=>({state:{status:'cancelled'}})};}};
vm.createContext(context);
let source = fs.readFileSync(path.resolve(__dirname, '../../public/js/ai_teacher_report.js'), 'utf8');
source = source.replace('window.huitTeacherReportUi={scan};',
    'window.huitTeacherReportUi={scan, setCurrent:(value)=>current=value};');
vm.runInContext(source, context);
context.window.huitTeacherReportUi.setCurrent({url:'/status',state:{status:'waiting',queued_at:1}});
modalEvents.get('hidden.bs.modal')();
assert.equal(calls.length,0);
const target = {closest: selector => selector === '.ai-teacher-report' ? {
    dataset:{cancelUrl:'/cancel',csrfToken:'fake'},querySelector:()=>null,
} : selector === '[data-progress-cancel]' ? target : null};
context.window.huitTeacherReportUi.setCurrent(null);
for (const handle of handlers.get('click')) handle({target,preventDefault(){}});
assert.equal(calls.length,1);
assert.equal(calls[0][0],'/cancel');
assert.equal(calls[0][1].method,'POST');
console.log('PASS: closing the modal never sends cancel; explicit stop sends one POST.');
