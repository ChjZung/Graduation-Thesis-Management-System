{{-- 
  HUIT - GRADUATION THESIS MANAGEMENT SYSTEM
  Component/Partial render danh sách Giảng viên theo nhóm Bộ môn cho các thẻ <select>
  Nhóm: <optgroup label="Tên Bộ Môn">
  Option: Danh xưng học thuật + Họ tên (Mã GV) — [X HĐ]
--}}
@php
    $statsMap = $currentSemesterFacultyStats ?? $facultyCouncilStats ?? [];
@endphp
@foreach($groupedGiangViens as $tenBoMon => $danhSachGV)
    <optgroup label="{{ $tenBoMon }}">
        @foreach($danhSachGV as $gv)
            @php
                $stat = $statsMap[$gv->MaGV] ?? null;
                $soHoiDong = $stat ? (int)$stat['total'] : 0;
                $tooltip = $stat['tooltip'] ?? "Chưa tham gia hội đồng nào";
                $roleText = $stat['roleText'] ?? '';
            @endphp
            <option value="{{ $gv->MaGV }}"
                    data-hoten="{{ $gv->HoTen }}"
                    data-bomon="{{ $tenBoMon }}"
                    data-hocham="{{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }}"
                    data-raw-text="{{ $gv->danh_xung_option }}"
                    data-so-hoidong="{{ $soHoiDong }}"
                    data-role-text="{{ $roleText }}"
                    data-tooltip="{{ $tooltip }}"
                    title="{{ $tooltip }}"
                    {{ (isset($selectedValue) && $selectedValue == $gv->MaGV) ? 'selected' : '' }}>
                {{ $gv->danh_xung_option }} — [{{ $soHoiDong }} HĐ]
            </option>
        @endforeach
    </optgroup>
@endforeach
