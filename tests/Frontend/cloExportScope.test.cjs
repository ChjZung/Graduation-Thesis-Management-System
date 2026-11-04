const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/clo_export_modal.js'), 'utf8');
function setup(selected = {}) {
    class Select {
        constructor(value = '') { this.value = value; this.options = []; this.handlers = {}; }
        add(option) { this.options.push(option); }
        replaceChildren(...options) { this.options = options; this.value = ''; }
        addEventListener(name, handler) { this.handlers[name] = handler; }
        get selectedOptions() { return this.options.filter(o => o.value === this.value); }
        change(value) { this.value = value; this.handlers.change(); }
    }
    const fields = { MaHocKy: new Select('HK1'), MaNganh: new Select('CNTT'), DotBaoVe: new Select(), MaHoiDong: new Select() };
    const button = {}, summary = {};
    const form = {
        dataset: { options: JSON.stringify([
            { semester: 'HK1', major: 'CNTT', round: 'Đợt 1', council: 'HD1', name: 'Hội đồng 1' },
            { semester: 'HK1', major: 'CNTT', round: 'Đợt 2', council: 'HD2', name: 'Hội đồng 2' },
            { semester: 'HK1', major: 'CNTT', round: 'Đợt 1', council: null, name: null },
        ]), ...selected },
        querySelector: selector => selector === '[type="submit"]' ? button : selector === '[data-scope-summary]' ? summary : fields[selector.match(/name="(.*?)"/)[1]],
        addEventListener() {},
    };
    const functionSource = source.slice(source.indexOf('function initForm(form)'), source.indexOf('async function open(url)'));
    vm.runInNewContext(functionSource + '\ninitForm(form);', { form, Option: function(textContent, value) { Object.assign(this, { textContent, value }); } });
    return { fields, button, summary };
}
test('optional round and council allow major scope, while required selections gate export', () => {
    const { fields, button, summary } = setup();
    assert.equal(button.disabled, false);
    assert.match(summary.textContent, /Toàn ngành trong học kỳ/);
    fields.DotBaoVe.change('Đợt 1');
    assert.deepEqual(fields.MaHoiDong.options.map(o => o.value), ['', 'HD1']);
    fields.MaHoiDong.change('HD1');
    assert.match(summary.textContent, /Hội đồng 1/);
    fields.DotBaoVe.change('Đợt 2');
    assert.equal(fields.MaHoiDong.value, '');
    assert.match(summary.textContent, /Toàn ngành trong Đợt 2/);
    fields.MaNganh.change('');
    assert.equal(button.disabled, true);
    assert.equal(fields.MaHoiDong.disabled, true);
});
test('bookmark selection restores council and clearing round exposes all selectable councils', () => {
    const { fields, summary } = setup({ selectedRound: 'Đợt 1', selectedCouncil: 'HD1' });
    assert.equal(fields.MaHoiDong.value, 'HD1');
    assert.match(summary.textContent, /Hội đồng 1/);
    fields.DotBaoVe.change('');
    assert.deepEqual(fields.MaHoiDong.options.map(o => o.value), ['', 'HD1', 'HD2']);
    assert.equal(fields.MaHoiDong.value, 'HD1');
    fields.MaHoiDong.change('');
    assert.match(summary.textContent, /Toàn ngành trong học kỳ/);
});
