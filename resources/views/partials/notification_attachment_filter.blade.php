<select name="attachment" class="form-select form-select-sm" style="min-width: 0; max-width: 100%;" onchange="this.form.submit()" aria-label="Lọc tệp đính kèm">
    <option value="">-- Tệp đính kèm --</option>
    <option value="with" @selected(request('attachment') === 'with')>Có tệp đính kèm</option>
    <option value="without" @selected(request('attachment') === 'without')>Không có tệp đính kèm</option>
</select>
