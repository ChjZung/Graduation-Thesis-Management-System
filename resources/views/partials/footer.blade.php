{{-- ========================================================
   HUIT Footer Component - Trường Đại Học Công Thương TP.HCM
   Sử dụng: @include('partials.footer')
======================================================== --}}
<footer class="huit-footer mt-auto">
    <div class="footer-top">
        <div class="container-fluid px-4">
            <div class="row g-4">

                {{-- Cột 1: Thông tin trường --}}
               <div class="col-lg-5 col-md-12">
                    <div class="footer-logo-area">
                        <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="footer-logo">
                        <div>
                            <div class="footer-brand-name">
                                Trường Đại Học Công Thương<br>TP. Hồ Chí Minh
                            </div>
                            <span class="footer-brand-sub">Ho Chi Minh City University of Industry and Trade (HUIT)</span>
                        </div>
                    </div>

                    <p class="footer-desc">
                        Hệ thống quản lý đồ án &amp; khóa luận tốt nghiệp trực tuyến, hỗ trợ Giảng viên và Sinh viên trong suốt quá trình thực hiện đồ án.
                    </p>

                    <div class="footer-contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>140 Lê Trọng Tấn, Phường Tây Thạnh, Q. Tân Phú, TP. Hồ Chí Minh</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <span>(028) 38 163 318</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>info@huit.edu.vn</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-globe"></i>
                        <span>www.huit.edu.vn</span>
                    </div>

                    {{-- Social Icons --}}
                    <div class="social-icons">
                        <a href="https://www.facebook.com/truongdhcttphcm" target="_blank" class="social-icon-btn social-fb" title="Facebook HUIT">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://www.youtube.com/@truongdhcttphcm" target="_blank" class="social-icon-btn social-yt" title="YouTube HUIT">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                        <a href="https://www.instagram.com/huit.edu.vn/" target="_blank" class="social-icon-btn social-ig" title="Instagram HUIT">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://zalo.me/0283816331" target="_blank" class="social-icon-btn social-zalo" title="Zalo HUIT">
                            <i class="fa-solid fa-comment-dots"></i>
                        </a>
                        <a href="https://www.tiktok.com/@huit.edu.vn" target="_blank" class="social-icon-btn social-tiktok" title="TikTok HUIT">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                    </div>
                </div>

              

                {{-- Cột 3: Hệ thống --}}
              <div class="col-lg-3 col-md-6">
                    <div class="footer-col-title">Hệ Thống QLĐA</div>
                    <ul class="footer-link-list">
                        <li>
                            <a href="{{ route('login') }}">
                                <i class="fa-solid fa-chevron-right"></i> Đăng nhập
                            </a>
                        </li>
                        @auth
                        @if(Auth::user()->VaiTro === 'gv')
                        <li><a href="{{ route('giangvien.detai.index') }}"><i class="fa-solid fa-chevron-right"></i> Đề tài của tôi</a></li>
                        <li><a href="{{ route('giangvien.duyet.index') }}"><i class="fa-solid fa-chevron-right"></i> Duyệt đăng ký</a></li>
                        @elseif(Auth::user()->VaiTro === 'sv')
                        <li><a href="{{ route('sinhvien.dangky.index') }}"><i class="fa-solid fa-chevron-right"></i> Đăng ký đề tài</a></li>
                        <li><a href="{{ route('sinhvien.baocao.index') }}"><i class="fa-solid fa-chevron-right"></i> Báo cáo tiến độ</a></li>
                        @elseif(Auth::user()->VaiTro === 'admin')
                        <li><a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chevron-right"></i> Dashboard Admin</a></li>
                        @endif
                        <li>
                            <a href="{{ route('profile.show') }}">
                                <i class="fa-solid fa-chevron-right"></i> Hồ sơ cá nhân
                            </a>
                        </li>
                        @endauth
                    </ul>

                    <div class="footer-col-title mt-4">Liên Hệ Khoa</div>
                    <ul class="footer-link-list">
                        <li><a href="#" onclick="return false"><i class="fa-solid fa-chevron-right"></i> Khoa CNTT</a></li>
                        <li><a href="#" onclick="return false"><i class="fa-solid fa-chevron-right"></i> Phòng Đào Tạo</a></li>
                        <li><a href="#" onclick="return false"><i class="fa-solid fa-chevron-right"></i> Phòng CTSV</a></li>
                    </ul>
                </div>

                {{-- Cột 4: Bản đồ --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-col-title">Bản Đồ Định Vị</div>
                    <div class="footer-map-frame">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0774831940624!2d106.6178!3d10.7985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752bbd0c53e62b%3A0xa58484d0f7af26b4!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBUaHXGoW5nIFRQLkhDTQ!5e0!3m2!1svi!2svn!4v1700000000000"
                            width="100%"
                            height="220"
                            style="border:0; display:block;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Vị trí Trường ĐH Công Thương TP.HCM">
                        </iframe>
                    </div>

                    <div class="mt-3 p-3 rounded" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">
                        <div style="font-size: 0.78rem; color: rgba(255,255,255,0.6); line-height: 1.65;">
                            <i class="fa-regular fa-clock me-1" style="color: #8FC9FF;"></i>
                            <strong style="color: rgba(255,255,255,0.85);">Giờ làm việc:</strong>
                            Thứ 2 – Thứ 6: 7:30 – 17:00<br>
                            <i class="fa-solid fa-headset me-1" style="color: #8FC9FF;"></i>
                            <strong style="color: rgba(255,255,255,0.85);">Hỗ trợ kỹ thuật:</strong> it-support@huit.edu.vn
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <hr class="footer-divider">

    {{-- Copyright bar --}}
    <div class="footer-copyright">
        <div class="container-fluid px-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <p class="mb-0">
                    &copy; {{ date('Y') }} <span>HUIT</span> – Hệ Thống Quản Lý Đồ Án &amp; Khóa Luận Tốt Nghiệp.
                    Phát triển bởi <span>Sinh viên Khoa CNTT</span>.
                </p>
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.35);">
                    <i class="fa-solid fa-shield-halved me-1" style="color: #8FC9FF;"></i>
                    Hệ thống bảo mật &amp; mã hóa dữ liệu theo tiêu chuẩn nhà trường
                </div>
            </div>
        </div>
    </div>
</footer>
