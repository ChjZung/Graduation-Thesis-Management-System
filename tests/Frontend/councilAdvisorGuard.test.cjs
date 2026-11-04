const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('resources/views/truongkhoa/hoidong/create.blade.php', 'utf8');
const guard = source.slice(source.indexOf('    function getSelectedAdvisorMap()'), source.indexOf('    // Kiểm tra toàn diện thành viên:'));
function option(value) {
    const attrs = { 'data-raw-text': 'Tên trùng nhau', 'data-so-hoidong': '0' };
    return {value, disabled:false, textContent:'Tên trùng nhau', getAttribute:k=>attrs[k], hasAttribute:k=>k in attrs, setAttribute:(k,v)=>attrs[k]=v};
}
function fixture() {
    let advisors = ['GV44'];
    const selects = Array.from({length:4}, (_,i)=>({value:i===0?'GV44':'', options:[option(''),option('GV44'),option('GV23'),option('GV02')], selectedIndex:1, classList:{add(){},remove(){}}}));
    const context = {Map, Set, document:{querySelectorAll(selector) {
        if(selector==='.select-faculty')return selects;
        return advisors.map(id=>({closest:()=>({getAttribute:k=>k==='data-gvhd-id'?id:'Đề tài mẫu'})}));
    }, getElementById:()=>null}, getSelectRoleName:()=> 'Thư ký', getScheduleConflictsForTime:()=>new Map(), updateAllWorkloadHints() {}};
    vm.createContext(context);vm.runInContext(guard,context);
    return {selects,context,setAdvisors:value=>{advisors=value;}};
}
test('blocks supervisor by ID for every role, clears a newly conflicting selection, allows same-name colleague',()=>{
    const f=fixture();f.context.syncFacultySelectionState();
    assert.equal(f.selects[0].value,'');
    for(const sel of f.selects){assert.equal(sel.options[1].disabled,true);assert.match(sel.options[1].textContent,/GVHD/);assert.equal(sel.options[2].disabled,false);}
});
test('removing topic unlocks its advisor while duplicate and schedule constraints still apply',()=>{
    const f=fixture();f.context.syncFacultySelectionState();f.setAdvisors([]);
    f.selects[0].value='GV23';f.selects[0].selectedIndex=2;
    f.context.getScheduleConflictsForTime=()=>new Map([['GV02',{councilName:'Hội đồng khác'}]]);
    f.context.syncFacultySelectionState();
    assert.equal(f.selects[1].options[1].disabled,false);
    assert.equal(f.selects[1].options[2].disabled,true);
    assert.equal(f.selects[1].options[3].disabled,true);
});
test('all selected advisors are excluded, including topic outside current filtered rows',()=>{
    const f=fixture();f.setAdvisors(['GV44','GV23']);f.context.syncFacultySelectionState();
    for(const sel of f.selects){assert.equal(sel.options[1].disabled,true);assert.equal(sel.options[2].disabled,true);}
});
