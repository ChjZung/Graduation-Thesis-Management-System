<div class="card border shadow-none rounded-3 mb-4 bg-white" id="sectionTemplateSelector">
    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark small text-uppercase">Mẫu báo cáo</h6>
        <span class="badge bg-primary text-uppercase" id="badgeActiveTemplate">
            {{ strtoupper($data['mauBaoCao'] ?? 'mau_01') }}
        </span>
    </div>

    <div class="card-body p-3">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-2.5" id="templateCardsGrid">
            @foreach($danhMuc['mauBaoCaos'] as $mauKey => $mau)
                @php
                    $currentMau = $data['mauBaoCao'] ?? ($filters['mau_bao_cao'] ?? 'mau_01');
                    $isActive = ($currentMau === $mauKey);
                @endphp
                <div class="col">
                    <div class="card h-100 template-card cursor-pointer rounded-3 {{ $isActive ? 'active-template' : '' }}"
                         data-mau="{{ $mauKey }}"
                         role="button"
                         tabindex="0">
                        
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge template-badge {{ $isActive ? 'bg-primary text-white' : 'bg-light text-secondary border' }}">
                                    {{ $mau['soHieu'] }}
                                </span>
                                <i class="fa-solid {{ $mau['icon'] }} template-icon {{ $isActive ? 'text-primary' : 'text-muted' }}"></i>
                            </div>

                            <div class="fw-semibold template-title mb-1 text-dark" style="font-size: 0.82rem; line-height: 1.35;">
                                {{ $mau['ten'] }}
                            </div>

                            <div class="text-muted template-desc line-clamp-2 mb-3" style="font-size: 0.72rem;">
                                {{ $mau['moTa'] }}
                            </div>

                            <div class="mt-auto">
                                <button type="button" class="btn {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm w-100 py-1 template-btn {{ $isActive ? 'fw-semibold' : '' }}" style="font-size: 0.75rem;">
                                    {{ $isActive ? 'Đang chọn' : 'Chọn mẫu' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
