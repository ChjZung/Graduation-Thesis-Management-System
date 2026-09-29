<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\HocKy;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Models\ThanhVienNhom;
use Carbon\Carbon;

class ThongKeDeTaiSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // 1. Đảm bảo học kỳ HK2425_1 (Đang diễn ra) tồn tại
        DB::table('HocKy')->updateOrInsert(
            ['MaHocKy' => 'HK2425_1'],
            [
                'TenHocKy' => 'Học kỳ 1 (2024-2025)',
                'NamHoc' => '2024-2025',
                'TrangThai' => 'Đang diễn ra',
                'updated_at' => $now
            ]
        );

        // 2. Danh sách 28 đề tài khóa luận thực tế gắn với các giảng viên thật của Khoa CNTT
        // QUY ĐỊNH BẮT BUỘC: Mỗi đề tài khóa luận chuẩn phải gồm đúng 3 sinh viên / 1 nhóm (không được 1 hay 2 SV)
        $topics = [
            // CNPM (Công nghệ phần mềm)
            [
                'MaDeTai' => 'DT_CNPM_01',
                'TenDeTai' => 'Xây dựng ứng dụng Microservices quản lý khóa luận tốt nghiệp với Spring Boot và Vue 3',
                'LinhVuc' => 'Công nghệ Web & Cloud',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV002', // TS. Trần Anh Dũng (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Nghiên cứu kiến trúc Microservices, xây dựng hệ thống quản lý đồ án tốt nghiệp phân tán có khả năng mở rộng cao.',
                'YeuCau' => 'Thành thạo Java Spring Boot, Vue.js 3, Docker và CSDL PostgreSQL.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_CNPM_02',
                'TenDeTai' => 'Phát triển hệ thống học trực tuyến thích ứng cá nhân hóa sử dụng Flutter và Firebase',
                'LinhVuc' => 'Ứng dụng Di động (Mobile)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV021', // ThS. Đặng Hải Giang (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Ứng dụng học tập trên di động phân tích hành vi và tự động đề xuất lộ trình ôn tập thích ứng cho học sinh.',
                'YeuCau' => 'Có kinh nghiệm lập trình Flutter, Dart, State Management (Bloc/Provider).',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_CNPM_03',
                'TenDeTai' => 'Nền tảng kiểm thử tự động API CI/CD tích hợp GitHub Actions và Playwright',
                'LinhVuc' => 'Công nghệ Web & Cloud',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Trưởng khoa đã duyệt',
                'MaGV' => 'GV023', // TS. Lê Hoàng Cường (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xây dựng quy trình tự động kiểm thử hồi quy và đo lường độ phủ mã nguồn trong môi trường DevOps.',
                'YeuCau' => 'Hiểu biết về CI/CD pipeline, Node.js, TypeScript và Playwright.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_CNPM_04',
                'TenDeTai' => 'Hệ thống thương mại điện tử đa nền tảng tối ưu hiệu năng SSR với Next.js và Redis',
                'LinhVuc' => 'Công nghệ Web & Cloud',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã đăng ký',
                'MaGV' => 'GV028', // ThS. Mai Tuyết Nga (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Thiết kế website bán lẻ đa kênh với cơ chế Server-side Rendering và Caching tối ưu trải nghiệm người dùng.',
                'YeuCau' => 'Thành thạo React/Next.js, Tailwind CSS, Redis và MySQL.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_CNPM_05',
                'TenDeTai' => 'Hệ thống quản lý công việc và quy trình tác nghiệp Kanban theo thời gian thực với WebSocket',
                'LinhVuc' => 'Công nghệ Web & Cloud',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Chờ duyệt cấp Bộ môn',
                'MaGV' => 'GV030', // ThS. Hoàng Thị Em (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Ứng dụng hỗ trợ làm việc nhóm trực quan, đồng bộ kéo thả thẻ công việc và thông báo qua WebSocket.',
                'YeuCau' => 'Kỹ năng làm việc với Socket.io, Node.js Express và MongoDB.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_CNPM_06',
                'TenDeTai' => 'Ứng dụng hỗ trợ lập kế hoạch học tập sinh viên sử dụng Flutter và Gemini AI API',
                'LinhVuc' => 'Trí tuệ nhân tạo (AI)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đang phản biện đề cương',
                'MaGV' => 'GV038', // ThS. Nguyễn Hoàng Anh (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Tích hợp Trí tuệ nhân tạo tạo sinh nhằm phân tích thời khóa biểu và tư vấn phân bổ thời gian học tập.',
                'YeuCau' => 'Kinh nghiệm gọi RESTful API, Flutter và xử lý Prompt Engineering.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_CNPM_07',
                'TenDeTai' => 'Hệ thống quản lý chuỗi cung ứng nông sản truy xuất nguồn gốc sử dụng Blockchain Hyperledger Fabric',
                'LinhVuc' => 'Công nghệ Web & Cloud',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Hoàn thành',
                'MaGV' => 'GV044', // TS. Thạch Văn Tài (CNPM)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xây dựng mạng Private Blockchain lưu vết quy trình gieo trồng, thu hoạch, vận chuyển nông sản sạch.',
                'YeuCau' => 'Hiểu biết về kiến trúc Blockchain, Smart Contract (Chaincode) và Golang.',
                'regCount' => 3,
            ],

            // HTTT (Hệ thống thông tin)
            [
                'MaDeTai' => 'DT_HTTT_01',
                'TenDeTai' => 'Thiết kế và triển khai phân hệ ERP Quản trị Nhân sự và Tính lương cho doanh nghiệp vừa và nhỏ',
                'LinhVuc' => 'Hệ thống Thông tin & ERP',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV003', // TS. Phạm Minh Tuấn (HTTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Phân tích quy trình chấm công, hợp đồng lao động, bảo hiểm và tính lương tự động theo luật lao động.',
                'YeuCau' => 'Nắm vững phân tích thiết kế hệ thống thông tin, C# ASP.NET Core và SQL Server.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_HTTT_02',
                'TenDeTai' => 'Hệ thống hỗ trợ ra quyết định phân tích dữ liệu bán lẻ đa kênh sử dụng Power BI và Data Warehouse',
                'LinhVuc' => 'Hệ thống Thông tin & ERP',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Trưởng khoa đã duyệt',
                'MaGV' => 'GV022', // ThS. Trần Thị Bích (HTTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xây dựng kho dữ liệu đa chiều (Data Warehouse), thiết kế ETL pipeline và bảng điều khiển trực quan KPI.',
                'YeuCau' => 'Hiểu về mô hình Star Schema, Snowflake, SQL Server SSIS, SSAS và Power BI.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_HTTT_03',
                'TenDeTai' => 'Phân tích và tối ưu hóa quy trình quản lý kho vận tại trung tâm phân phối hàng tiêu dùng',
                'LinhVuc' => 'Hệ thống Thông tin & ERP',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Chờ duyệt cấp Khoa',
                'MaGV' => 'GV029', // TS. Võ Đình Phúc (HTTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Khảo sát hiện trạng chuỗi cung ứng, mô hình hóa BPMN và đề xuất giải pháp phần mềm quản lý vị trí kho thông minh.',
                'YeuCau' => 'Kỹ năng mô hình hóa nghiệp vụ BPMN 2.0, phân tích CSDL quan hệ.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_HTTT_04',
                'TenDeTai' => 'Hệ thống Quản lý Quan hệ Khách hàng (CRM) tích hợp Omnichannel Zalo OA và Facebook Messenger',
                'LinhVuc' => 'Hệ thống Thông tin & ERP',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV039', // ThS. Lê Phương Thảo (HTTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Hợp nhất kênh tương tác khách hàng, phân bổ đơn tư vấn cho nhân viên và theo dõi phễu chuyển đổi.',
                'YeuCau' => 'Lập trình Web API, tích hợp Webhook Zalo/Facebook, PHP Laravel.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_HTTT_05',
                'TenDeTai' => 'Hệ thống quản lý bệnh án điện tử và điều phối phòng khám ngoại trú chuẩn HL7 FHIR',
                'LinhVuc' => 'Hệ thống Thông tin & ERP',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Yêu cầu chỉnh sửa',
                'MaGV' => 'GV045', // ThS. Uông Thị Uyên (HTTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Số hóa hồ sơ bệnh án, đặt lịch khám trực tuyến và quản lý chỉ định cận lâm sàng.',
                'YeuCau' => 'Hiểu về chuẩn y tế điện tử HL7, bảo mật dữ liệu y tế cá nhân.',
                'regCount' => 0,
            ],

            // KHDL (Khoa học dữ liệu & AI)
            [
                'MaDeTai' => 'DT_KHDL_01',
                'TenDeTai' => 'Nghiên cứu mô hình Transformer dự báo nhu cầu năng lượng điện tại TP. Hồ Chí Minh',
                'LinhVuc' => 'Khoa học dữ liệu (Data Science)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV007', // TS. Đỗ Anh Minh (KHDL)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Ứng dụng các mô hình học sâu chuỗi thời gian (PatchTST, Informer, LSTM) để dự báo phụ tải điện trung hạn.',
                'YeuCau' => 'Thành thạo Python, PyTorch/TensorFlow, xử lý dữ liệu Time-series với Pandas.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_KHDL_02',
                'TenDeTai' => 'Xây dựng trợ lý ảo tư vấn tuyển sinh đại học ứng dụng RAG và mô hình mã nguồn mở Llama-3',
                'LinhVuc' => 'Trí tuệ nhân tạo (AI)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV025', // TS. Dương Thúy Hằng (KHDL)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Hệ thống Retrieval-Augmented Generation tìm kiếm ngữ nghĩa văn bản đề án tuyển sinh và trả lời chính xác.',
                'YeuCau' => 'Kinh nghiệm với LangChain/LlamaIndex, Vector Database (Milvus/ChromaDB), Python.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_KHDL_03',
                'TenDeTai' => 'Phát hiện gian lận giao dịch thẻ tín dụng sử dụng kỹ thuật học mất cân bằng và Graph Neural Networks',
                'LinhVuc' => 'Khoa học dữ liệu (Data Science)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Trưởng khoa đã duyệt',
                'MaGV' => 'GV042', // TS. Tôn Nữ Quỳnh Như (KHDL)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Mô hình hóa các mối quan hệ giao dịch dạng đồ thị để phát hiện mẫu gian lận tinh vi với tỷ lệ dương tính giả thấp.',
                'YeuCau' => 'Kiến thức tốt về Machine Learning, Graph Theory, PyTorch Geometric.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_KHDL_04',
                'TenDeTai' => 'Hệ thống nhận dạng biển số xe và phân loại phương tiện giao thông thông minh với YOLOv9',
                'LinhVuc' => 'Trí tuệ nhân tạo (AI)',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã đăng ký',
                'MaGV' => 'GV007', // TS. Đỗ Anh Minh (KHDL)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Triển khai mô hình Computer Vision nhận diện biển kiểm soát giao thông thời gian thực trên Camera giám sát.',
                'YeuCau' => 'Kinh nghiệm xử lý ảnh OpenCV, huấn luyện mô hình YOLO, triển khai TensorRT.',
                'regCount' => 3,
            ],

            // KHMT (Khoa học máy tính)
            [
                'MaDeTai' => 'DT_KHMT_01',
                'TenDeTai' => 'Hệ thống phát hiện lỗi mã nguồn tự động sử dụng phân tích tĩnh và đồ thị phụ thuộc mã (AST/CFG)',
                'LinhVuc' => 'Khoa học máy tính & Thuật toán',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV006', // PGS.TS. Võ Quốc Phong (KHMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Trích xuất cây cú pháp trừu tượng và đồ thị luồng điều khiển để rà soát lỗi bảo mật phổ biến OWASP Top 10.',
                'YeuCau' => 'Hiểu sâu về cấu trúc dữ liệu, giải thuật, lý thuyết trình biên dịch.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_KHMT_02',
                'TenDeTai' => 'Nghiên cứu thuật toán lập lịch phân tán tối ưu chi phí tài nguyên trên môi trường Kubernetes',
                'LinhVuc' => 'Khoa học máy tính & Thuật toán',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Chờ duyệt cấp Bộ môn',
                'MaGV' => 'GV027', // TS. Trịnh Văn Khang (KHMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Phát triển Custom Scheduler cho Kubernetes nhằm cân bằng tải và tối ưu điện năng tiêu thụ cụm máy chủ.',
                'YeuCau' => 'Lập trình Golang, hiểu cơ chế hoạt động của Kubernetes Custom Controller.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_KHMT_03',
                'TenDeTai' => 'Mô phỏng mô hình giao thông đô thị đa tác tử (Multi-Agent System) nhằm tối ưu chu kỳ đèn tín hiệu',
                'LinhVuc' => 'Khoa học máy tính & Thuật toán',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Trưởng khoa đã duyệt',
                'MaGV' => 'GV043', // ThS. Cù Huy Sơn (KHMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Sử dụng phần mềm mô phỏng SUMO kết hợp thuật toán học tăng cường Q-Learning điều chỉnh thời gian đèn giao thông.',
                'YeuCau' => 'Lập trình Python, mô phỏng SUMO/TraCI, kiến thức Reinforcement Learning cơ bản.',
                'regCount' => 0,
            ],

            // ATTT (An toàn thông tin)
            [
                'MaDeTai' => 'DT_ATTT_01',
                'TenDeTai' => 'Xây dựng trung tâm điều hành an toàn thông tin (SOC) mã nguồn mở với Wazuh và TheHive',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV001', // PGS.TS. Nguyễn Văn Hùng (ATTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Triển khai hệ thống giám sát an ninh mạng SIEM/EDR, tự động phát hiện xâm nhập và kích hoạt kịch bản ứng phó sự cố.',
                'YeuCau' => 'Kiến thức mạng máy tính, Linux Administration, Elastic Stack, Wazuh.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_ATTT_02',
                'TenDeTai' => 'Nghiên cứu cơ chế phòng chống tấn công lừa đảo trực tuyến (Phishing) dựa trên phân tích Header và nội dung Email',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Trưởng khoa đã duyệt',
                'MaGV' => 'GV004', // TS. Trịnh Văn Thành (ATTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xây dựng plugin bảo vệ hộp thư, kiểm tra bản ghi SPF/DKIM/DMARC và dùng NLP lọc URL độc hại.',
                'YeuCau' => 'Hiểu về giao thức mạng SMTP/DNS, lập trình Python, bảo mật ứng dụng.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_ATTT_03',
                'TenDeTai' => 'Kỹ thuật phát hiện mã độc nhúng trong tập tin tài liệu Office sử dụng học máy và phân tích hành vi',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Chờ duyệt cấp Khoa',
                'MaGV' => 'GV024', // ThS. Ngô Đức Hải (ATTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Trích xuất đặc trưng macro, OLE streams từ file Word/Excel và phân loại tập tin độc hại trong môi trường Sandbox.',
                'YeuCau' => 'Kiến thức về Reverse Engineering, Sandbox Cuckoo, Python ML.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_ATTT_04',
                'TenDeTai' => 'Đánh giá an ninh và kiểm thử xâm nhập (Penetration Testing) hệ thống xác thực Single Sign-On (SSO)',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Từ chối',
                'MaGV' => 'GV040', // TS. Vũ Đình Khôi (ATTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Rà soát các lỗ hổng giao thức OAuth 2.0, OpenID Connect và SAML 2.0 trong các giải pháp SSO phổ biến.',
                'YeuCau' => 'Kiến thức vững về bảo mật Web, Burp Suite, giao thức xác thực hiện đại.',
                'LyDoTuChoi' => 'Phạm vi đề tài chưa rõ sản phẩm bàn giao cụ thể, cần bổ sung giải pháp khắc phục chi tiết.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_ATTT_05',
                'TenDeTai' => 'Xây dựng giải pháp Zero Trust Network Access (ZTNA) kiểm soát truy cập từ xa cho nhân viên',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã đăng ký',
                'MaGV' => 'GV046', // TS. Phí Văn Việt (ATTT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Ứng dụng kiến trúc Zero Trust thay thế VPN truyền thống, xác thực liên tục dựa trên bối cảnh và định danh thiết bị.',
                'YeuCau' => 'Hiểu về WireGuard, mTLS, Identity Provider (IdP) và chính sách kiểm soát truy cập.',
                'regCount' => 3,
            ],

            // MMT (Mạng máy tính & Viễn thông)
            [
                'MaDeTai' => 'DT_MMT_01',
                'TenDeTai' => 'Thiết kế mạng định nghĩa bằng phần mềm (SDN) tối ưu định tuyến và quản lý chất lượng dịch vụ (QoS)',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV005', // TS. Lê Hải Đăng (MMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xây dựng bộ điều khiển Ryu SDN Controller, tự động điều chỉnh luồng lưu lượng khi xảy ra nghẽn mạng.',
                'YeuCau' => 'Kinh nghiệm với Mininet, OpenFlow Protocol, lập trình Python mạng.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_MMT_02',
                'TenDeTai' => 'Hệ thống giám sát chất lượng không khí diện rộng ứng dụng công nghệ LoRaWAN và giao thức MQTT',
                'LinhVuc' => 'IoT & Hệ thống nhúng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đã công bố',
                'MaGV' => 'GV026', // ThS. Lý Thanh Lan (MMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Thiết kế trạm cảm biến tiêu thụ điện năng thấp, truyền dữ liệu khoảng cách xa qua LoRaWAN Gateway lên Cloud.',
                'YeuCau' => 'Lập trình vi điều khiển ESP32, mạch cảm biến IoT, MQTT broker.',
                'regCount' => 3,
            ],
            [
                'MaDeTai' => 'DT_MMT_03',
                'TenDeTai' => 'Giải pháp cân bằng tải lưu lượng mạng Internet sử dụng BGP đa đường truyền (Multi-homing)',
                'LinhVuc' => 'An toàn thông tin & Mạng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Đang phản biện đề cương',
                'MaGV' => 'GV041', // ThS. Huỳnh Quốc Bảo (MMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Mô phỏng hạ tầng định tuyến ISP với GNS3/EVE-NG, triển khai chính sách định tuyến BGP tối ưu độ trễ.',
                'YeuCau' => 'Kiến thức CCNA/CCNP, định tuyến động OSPF, BGP, phần mềm mô phỏng mạng.',
                'regCount' => 0,
            ],
            [
                'MaDeTai' => 'DT_MMT_04',
                'TenDeTai' => 'Hệ thống khóa cửa thông minh nhận diện khuôn mặt cục bộ trên thiết bị nhúng Raspberry Pi',
                'LinhVuc' => 'IoT & Hệ thống nhúng',
                'SoLuongSinhVienToiDa' => 3,
                'TrangThai' => 'Chờ duyệt cấp Bộ môn',
                'MaGV' => 'GV005', // TS. Lê Hải Đăng (MMT)
                'HocPhan' => 'Khóa luận tốt nghiệp',
                'MoTa' => 'Xử lý nhận diện khuôn mặt trực tiếp (Edge AI) không gửi ảnh lên đám mây nhằm đảm bảo quyền riêng tư.',
                'YeuCau' => 'Lập trình C++/Python trên Linux Embedded, OpenCV, tối ưu mô hình nhẹ.',
                'regCount' => 0,
            ],
        ];

        // 3. Thực hiện chèn đề tài và tạo liên kết nhóm sinh viên (đúng 3 sinh viên / 1 nhóm)
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('DeTai')->truncate();
        DB::table('PhieuDangKy')->truncate();
        DB::table('Nhom')->truncate();
        DB::table('ThanhVienNhom')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $availableStudents = DB::table('SinhVien')->orderBy('MaSV')->pluck('MaSV')->toArray();
        $svIndex = 0;

        foreach ($topics as $index => $t) {
            $maDT = $t['MaDeTai'];
            
            DB::table('DeTai')->insert([
                'MaDeTai'              => $maDT,
                'TenDeTai'             => $t['TenDeTai'],
                'MoTa'                 => $t['MoTa'] ?? 'Mô tả chi tiết mục tiêu nghiên cứu và phương pháp triển khai đề tài khóa luận.',
                'YeuCau'               => $t['YeuCau'] ?? 'Nắm vững kiến thức chuyên môn cơ sở ngành và kỹ năng lập trình.',
                'LinhVuc'              => $t['LinhVuc'],
                'SoLuongSinhVienToiDa' => 3, // Bắt buộc 3 sinh viên / đề tài
                'HocPhan'              => $t['HocPhan'] ?? 'Khóa luận tốt nghiệp',
                'TrangThai'            => $t['TrangThai'],
                'LyDoTuChoi'           => $t['LyDoTuChoi'] ?? null,
                'NgayDeXuat'           => Carbon::now()->subDays(rand(10, 30))->toDateString(),
                'NgayDuyetBM'          => in_array($t['TrangThai'], ['Trưởng khoa đã duyệt', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành', 'Chờ duyệt cấp Khoa']) ? Carbon::now()->subDays(rand(5, 15))->toDateString() : null,
                'NguoiDuyetBM'         => 'Trưởng bộ môn',
                'NgayDuyetKhoa'        => in_array($t['TrangThai'], ['Trưởng khoa đã duyệt', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành']) ? Carbon::now()->subDays(rand(1, 5))->toDateString() : null,
                'NguoiDuyetKhoa'       => 'Trưởng khoa CNTT',
                'NgayCongBo'           => in_array($t['TrangThai'], ['Đã công bố', 'Đã đăng ký', 'Hoàn thành']) ? Carbon::now()->subDays(rand(1, 4))->toDateString() : null,
                'MaGV'                 => $t['MaGV'],
                'MaHocKy'              => 'HK2425_1',
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            // Nếu đề tài có sinh viên đăng ký (regCount = 3)
            if ($t['regCount'] === 3 && count($availableStudents) >= ($svIndex + 3)) {
                $truongNhomSV = $availableStudents[$svIndex];
                $maNhom = 'NHOM' . $truongNhomSV;
                $tenNhom = 'NHOM' . $truongNhomSV;

                // Tạo nhóm theo quy chuẩn: mã nhóm và tên nhóm là NHOMmã nhóm trưởng
                DB::table('Nhom')->insert([
                    'MaNhom'     => $maNhom,
                    'TenNhom'    => $tenNhom,
                    'TrangThai'  => 'Đang hoạt động',
                    'NgayTao'    => Carbon::now()->subDays(rand(5, 20))->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Tạo thành viên nhóm: đúng 3 sinh viên (1 Trưởng nhóm, 2 Thành viên)
                for ($s = 0; $s < 3; $s++) {
                    $currSV = $availableStudents[$svIndex + $s];
                    DB::table('ThanhVienNhom')->insert([
                        'MaNhom'      => $maNhom,
                        'MaSV'        => $currSV,
                        'VaiTro'      => $s === 0 ? 'Trưởng nhóm' : 'Thành viên',
                        'TrangThai'   => 'Chính thức',
                        'NgayThamGia' => Carbon::now()->subDays(rand(5, 20))->toDateString(),
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }

                // Tạo Phiếu đăng ký
                $maPDK = 'PDK_' . $truongNhomSV;
                DB::table('PhieuDangKy')->insert([
                    'MaDangKy'   => $maPDK,
                    'MaNhom'     => $maNhom,
                    'MaDeTai'    => $maDT,
                    'NgayDangKy' => Carbon::now()->subDays(rand(1, 4))->toDateString(),
                    'TrangThai'  => 'Đã duyệt',
                    'NgayDuyet'  => Carbon::now()->subDays(rand(0, 2))->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $svIndex += 3;
            }
        }

        echo "Successfully seeded " . count($topics) . " topics with strictly 3 students per topic for HK2425_1.\n";
    }
}
