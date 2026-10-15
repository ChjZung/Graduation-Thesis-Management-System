<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$outputDir = __DIR__ . '/../sample_imports';

$headers = [
    'MaDeTai',
    'TenDeTai',
    'MoTa',
    'YeuCau',
    'LinhVuc',
    'MaHocPhan',
    'TenHocPhan',
    'MaGV',
    'TenGV',
    'MaHocKy',
    'SoLuongSinhVienToiDa',
    'TrangThai'
];

// Danh sách đề tài mới hoàn toàn, phân chia theo từng Giảng viên, Học kỳ HK2627_1 và Học phần theo Bộ môn
$data = [
    // -----------------------------------------------------------------------------------
    // 1. BỘ MÔN CÔNG NGHỆ PHẦN MỀM (CNPM)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT14',
        'TenDeTai'             => 'Phát triển hệ sinh thái hỗ trợ học tập Microlearning trên nền tảng Web và Mobile với Flutter và NestJS',
        'MoTa'                 => 'Nghiên cứu mô hình học tập vi mô (Microlearning), xây dựng ứng dụng di động và hệ thống backend quản lý bài giảng ngắn gọn, tích hợp thuật toán lặp lại ngắt quãng (Spaced Repetition) để nâng cao hiệu quả ghi nhớ cho sinh viên.',
        'YeuCau'               => 'Nắm vững lập trình di động Flutter (Dart), kiến thức lập trình Backend NestJS/TypeScript, hiểu biết về cơ sở dữ liệu MongoDB hoặc PostgreSQL.',
        'LinhVuc'              => 'Công nghệ phần mềm',
        'MaHocPhan'            => 'HP_KLCN_CNPM',
        'TenHocPhan'           => 'Khóa luận cử nhân - Công nghệ phần mềm',
        'MaGV'                 => 'GV00000002',
        'TenGV'                => 'TS. Trần Anh Dũng',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT15',
        'TenDeTai'             => 'Thiết kế và triển khai nền tảng cộng tác lập trình thời gian thực (Collaborative Code Editor) trên nền kiến trúc WebRTC và CRDT',
        'MoTa'                 => 'Nghiên cứu cấu trúc dữ liệu không xung đột (Conflict-free Replicated Data Types - CRDT), xây dựng trình soạn thảo mã nguồn trực tuyến đa người dùng với độ trễ siêu thấp, hỗ trợ voice/video call qua WebRTC và biên dịch code từ xa bằng Docker sandbox.',
        'YeuCau'               => 'Thành thạo lập trình Web Frontend (React/Vue), hiểu sâu về WebSocket, WebRTC, cấu trúc CRDT (Yjs hoặc Automerge) và kỹ thuật quản lý tiến trình an toàn với Docker.',
        'LinhVuc'              => 'Công nghệ phần mềm',
        'MaHocPhan'            => 'HP_KLTN_CNPM',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - Công nghệ phần mềm',
        'MaGV'                 => 'GV00000021',
        'TenGV'                => 'ThS. Đặng Hải Giang',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],

    // -----------------------------------------------------------------------------------
    // 2. BỘ MÔN HỆ THỐNG THÔNG TIN (HTTT)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT16',
        'TenDeTai'             => 'Xây dựng hệ thống quản lý chuỗi cung ứng nông sản thông minh ứng dụng công nghệ Blockchain và QR Code truy xuất nguồn gốc',
        'MoTa'                 => 'Phân tích chuỗi hành trình nông sản từ trang trại đến bàn ăn, thiết kế Smart Contract trên mạng Ethereum/Polygon để lưu trữ bất biến các mốc kiểm định chất lượng, xây dựng ứng dụng tra cứu qua quét mã QR cho người tiêu dùng.',
        'YeuCau'               => 'Kiến thức về phân tích nghiệp vụ chuỗi cung ứng, lập trình Smart Contract với Solidity, phát triển ứng dụng Web/App kết nối Web3 (Ethers.js/Web3.js).',
        'LinhVuc'              => 'Hệ thống thông tin',
        'MaHocPhan'            => 'HP_KLCN_HTTT',
        'TenHocPhan'           => 'Khóa luận cử nhân - Hệ thống thông tin',
        'MaGV'                 => 'GV00000003',
        'TenGV'                => 'TS. Phạm Minh Tuấn',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT17',
        'TenDeTai'             => 'Triển khai giải pháp phân tích hành vi khách hàng và tối ưu chiến dịch tiếp thị số (Customer Data Platform) cho sàn thương mại điện tử',
        'MoTa'                 => 'Thu thập sự kiện người dùng đa kênh theo thời gian thực (Clickstream, giao dịch, đánh giá), đồng bộ vào hồ dữ liệu (Data Lake), áp dụng mô hình phân cụm RFM và thuật toán dự đoán tỷ lệ rời bỏ (Churn Rate) để tự động hóa gợi ý khuyến mãi.',
        'YeuCau'               => 'Thành thạo truy vấn dữ liệu SQL/NoSQL, xử lý dòng dữ liệu với Apache Kafka hoặc RabbitMQ, kiến thức về mô hình RFM và trực quan hóa dashboard kinh doanh.',
        'LinhVuc'              => 'Hệ thống thông tin',
        'MaHocPhan'            => 'HP_KLTN_HTTT',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - Hệ thống thông tin',
        'MaGV'                 => 'GV00000022',
        'TenGV'                => 'ThS. Trần Thị Bích',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],

    // -----------------------------------------------------------------------------------
    // 3. BỘ MÔN AN TOÀN THÔNG TIN (ATTT)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT18',
        'TenDeTai'             => 'Nghiên cứu đánh giá lỗ hổng bảo mật ứng dụng Web API theo chuẩn OWASP API Top 10 và đề xuất giải pháp phòng ngừa tự động',
        'MoTa'                 => 'Phân tích các cơ chế tấn công vào giao diện lập trình ứng dụng (BOLA, Broken Authentication, Mass Assignment), xây dựng công cụ quét tự động tích hợp vào quy trình CI/CD DevSecOps và cấu hình Web Application Firewall (WAF) bảo vệ hệ thống.',
        'YeuCau'               => 'Hiểu biết sâu về kiến trúc RESTful API, giao thức HTTP/HTTPS, các lỗ hổng OWASP API Top 10, kỹ năng viết script kiểm thử bảo mật bằng Python/Go.',
        'LinhVuc'              => 'An toàn thông tin',
        'MaHocPhan'            => 'HP_KLCN_ATTT',
        'TenHocPhan'           => 'Khóa luận cử nhân - An toàn thông tin',
        'MaGV'                 => 'GV00000004',
        'TenGV'                => 'TS. Trịnh Văn Thành',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT19',
        'TenDeTai'             => 'Xây dựng giải pháp phát hiện mã độc tống tiền (Ransomware) dựa trên phân tích hành vi I/O và bộ nhớ hệ điều hành Windows',
        'MoTa'                 => 'Nghiên cứu cơ chế mã hóa tệp tin hàng loạt của các biến thể Ransomware, thu thập chỉ số hành vi thay đổi hệ thống tệp và tiến trình thông qua Windows Kernel Driver/Event Tracing for Windows (ETW), áp dụng học máy phát hiện sớm và cô lập tiến trình độc hại.',
        'YeuCau'               => 'Kiến thức về cấu trúc hệ điều hành Windows nội vi (Windows Internals), lập trình C/C++ hoặc Python, phân tích hành vi mã độc cơ bản.',
        'LinhVuc'              => 'An toàn thông tin',
        'MaHocPhan'            => 'HP_KLTN_ATTT',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - An toàn thông tin',
        'MaGV'                 => 'GV00000024',
        'TenGV'                => 'ThS. Ngô Đức Hải',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],

    // -----------------------------------------------------------------------------------
    // 4. BỘ MÔN KHOA HỌC MÁY TÍNH (KHMT)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT20',
        'TenDeTai'             => 'Nghiên cứu ứng dụng mô hình thị giác máy tính YOLOv8 và DeepSORT trong giám sát trật tự an toàn giao thông đô thị',
        'MoTa'                 => 'Thu thập và gán nhãn tập dữ liệu giao thông tại TP.HCM, huấn luyện mô hình YOLOv8 phát hiện phương tiện, kết hợp thuật toán DeepSORT theo dõi quỹ đạo, tự động nhận diện các hành vi vi phạm như vượt đèn đỏ, đi ngược chiều và lấn làn.',
        'YeuCau'               => 'Nền tảng toán học và xử lý ảnh số, thành thạo lập trình Python, thư viện OpenCV, PyTorch, có khả năng tối ưu hóa mô hình với TensorRT.',
        'LinhVuc'              => 'Khoa học máy tính',
        'MaHocPhan'            => 'HP_KLCN_KHMT',
        'TenHocPhan'           => 'Khóa luận cử nhân - Khoa học máy tính',
        'MaGV'                 => 'GV00000001',
        'TenGV'                => 'PGS.TS. Nguyễn Văn Hùng',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT21',
        'TenDeTai'             => 'Xây dựng trợ lý nghiên cứu học thuật tiếng Việt ứng dụng kỹ thuật sinh tăng cường tra cứu (Retrieval-Augmented Generation - RAG)',
        'MoTa'                 => 'Nghiên cứu kỹ thuật nhúng vector ngữ nghĩa (Semantic Embedding), xây dựng cơ sở dữ liệu vector lưu trữ hàng chục nghìn bài báo khoa học và luận văn tốt nghiệp, kết hợp mô hình ngôn ngữ lớn (LLM) để tổng hợp câu trả lời chính xác có trích dẫn nguồn bài báo rõ ràng.',
        'YeuCau'               => 'Hiểu biết sâu về kiến trúc Transformer, kỹ thuật RAG, Vector Database (Milvus/ChromaDB/Pinecone), thành thạo Python và framework LangChain/LlamaIndex.',
        'LinhVuc'              => 'Khoa học máy tính',
        'MaHocPhan'            => 'HP_KLTN_KHMT',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - Khoa học máy tính',
        'MaGV'                 => 'GV00000006',
        'TenGV'                => 'PGS.TS. Võ Quốc Phong',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],

    // -----------------------------------------------------------------------------------
    // 5. BỘ MÔN KHOA HỌC DỮ LIỆU & AI (KHDL)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT22',
        'TenDeTai'             => 'Xây dựng hệ thống dự đoán rủi ro tín dụng cá nhân và phát hiện giao dịch gian lận ngân hàng bằng mô hình Ensemble Learning',
        'MoTa'                 => 'Tiền xử lý và kỹ thuật hóa đặc trưng (Feature Engineering) trên tập dữ liệu tài chính quy mô lớn, xử lý mất cân bằng nhãn với SMOTE/ADASYN, áp dụng các thuật toán XGBoost, LightGBM, CatBoost để xếp hạng tín nhiệm và cảnh báo gian lận thẻ tín dụng.',
        'YeuCau'               => 'Tư duy phân tích thống kê định lượng tốt, thành thạo lập trình Python (Pandas, Scikit-learn, Imbalanced-learn), hiểu rõ các chỉ số đánh giá ROC-AUC, F1-Score, PR-AUC.',
        'LinhVuc'              => 'Khoa học dữ liệu',
        'MaHocPhan'            => 'HP_KLCN_KHDL',
        'TenHocPhan'           => 'Khóa luận cử nhân - Khoa học dữ liệu & AI',
        'MaGV'                 => 'GV00000007',
        'TenGV'                => 'TS. Đỗ Anh Minh',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT23',
        'TenDeTai'             => 'Phát triển đường ống xử lý dữ liệu lớn (Big Data Pipeline) phân tích cảm xúc cộng đồng trên mạng xã hội với Apache Spark và Kafka',
        'MoTa'                 => 'Xây dựng kiến trúc Lambda/Kappa thu thập bình luận từ Facebook, YouTube, diễn đàn trực tuyến theo thời gian thực, xử lý phân tán với Apache Spark Streaming, phân loại cảm xúc tích cực/tiêu cực và hiển thị biểu đồ xu hướng dư luận tức thời.',
        'YeuCau'               => 'Nắm vững nguyên lý hệ thống phân tán, thành thạo Apache Spark (PySpark), Apache Kafka, cơ sở dữ liệu Elasticsearch và công cụ trực quan hóa Kibana/Grafana.',
        'LinhVuc'              => 'Khoa học dữ liệu',
        'MaHocPhan'            => 'HP_KLTN_KHDL',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - Khoa học dữ liệu & AI',
        'MaGV'                 => 'GV00000025',
        'TenGV'                => 'TS. Dương Thúy Hằng',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],

    // -----------------------------------------------------------------------------------
    // 6. BỘ MÔN MẠNG MÁY TÍNH & VIỄN THÔNG (MMT)
    // -----------------------------------------------------------------------------------
    [
        'MaDeTai'              => 'DT24',
        'TenDeTai'             => 'Thiết kế hệ thống mạng định nghĩa bằng phần mềm (Software-Defined Networking - SDN) và định tuyến thông minh thích ứng theo luồng',
        'MoTa'                 => 'Nghiên cứu giao thức OpenFlow và bộ điều khiển SDN Controller (Ryu/ONOS), mô phỏng topo mạng doanh nghiệp với Mininet, phát triển giải pháp tối ưu hóa định tuyến động thích ứng theo lưu lượng băng thông thực tế để giảm thiểu tắc nghẽn mạng.',
        'YeuCau'               => 'Kiến thức vững chắc về giao thức mạng TCP/IP, kiến trúc SDN, kỹ năng lập trình mạng với Python và công cụ mô phỏng Mininet/Wireshark.',
        'LinhVuc'              => 'Mạng máy tính',
        'MaHocPhan'            => 'HP_KLCN_MMT',
        'TenHocPhan'           => 'Khóa luận cử nhân - Mạng máy tính & Viễn thông',
        'MaGV'                 => 'GV00000005',
        'TenGV'                => 'TS. Lê Hải Đăng',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ],
    [
        'MaDeTai'              => 'DT25',
        'TenDeTai'             => 'Xây dựng hệ thống nhà máy thông minh (Smart Factory) ứng dụng công nghệ mạng riêng ảo 5G/WiFi 6 và điện toán biên (Edge Computing)',
        'MoTa'                 => 'Thiết kế mô hình kết nối vạn vật công nghiệp (IIoT), thu thập dữ liệu rung chấn và nhiệt độ máy móc cơ khí thời gian thực, xử lý cảnh báo sự cố ngay tại Gateway biên chạy K3s (Lightweight Kubernetes) để đảm bảo độ khả dụng cao và giảm độ trễ điều khiển.',
        'YeuCau'               => 'Kiến thức về hạ tầng mạng không dây công nghiệp, giao thức truyền thông OPC UA/Modbus, lập trình nhúng Linux và containerization với Docker/K3s.',
        'LinhVuc'              => 'Mạng máy tính',
        'MaHocPhan'            => 'HP_KLTN_MMT',
        'TenHocPhan'           => 'Khóa luận tốt nghiệp - Mạng máy tính & Viễn thông',
        'MaGV'                 => 'GV00000026',
        'TenGV'                => 'ThS. Lý Thanh Lan',
        'MaHocKy'              => 'HK2627_1',
        'SoLuongSinhVienToiDa' => 3,
        'TrangThai'            => 'Chờ duyệt cấp Bộ môn'
    ]
];

function exportDualFiles($baseName, $headers, $data, $dir) {
    // 1. Xuất file CSV (UTF-8 BOM hiển thị chuẩn trên mọi bản Excel tiếng Việt)
    $csvFile = $dir . '/' . $baseName . '.csv';
    $fp = fopen($csvFile, 'w');
    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($fp, $headers);
    foreach ($data as $row) {
        $line = [];
        foreach ($headers as $col) {
            $line[] = $row[$col] ?? '';
        }
        fputcsv($fp, $line);
    }
    fclose($fp);
    echo " -> Đã tạo file: {$baseName}.csv\n";

    // 2. Xuất file Excel .xlsx có định dạng màu sắc chuyên nghiệp
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('DanhSach_DeTai');

    // Header styling
    $headerColor = '1E3A8A'; // Dark Navy Blue
    foreach ($headers as $colIdx => $colName) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue("{$colLetter}1", $colName);
    }

    $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = "A1:{$lastColLetter}1";

    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11,
            'name' => 'Calibri'
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $headerColor]
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ]
    ]);
    $sheet->getRowDimension(1)->setRowHeight(28);

    // Data rows
    $rowIdx = 2;
    foreach ($data as $row) {
        foreach ($headers as $colIdx => $colName) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $val = $row[$colName] ?? '';
            $sheet->setCellValue("{$colLetter}{$rowIdx}", $val);
        }

        // Zebra striping
        if ($rowIdx % 2 === 0) {
            $sheet->getStyle("A{$rowIdx}:{$lastColLetter}{$rowIdx}")->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC']
            ]);
        }
        $sheet->getRowDimension($rowIdx)->setRowHeight(22);
        $rowIdx++;
    }

    // Auto widths & wrap text
    $wrapColumns = ['TenDeTai', 'MoTa', 'YeuCau', 'TenHocPhan'];
    foreach ($headers as $colIdx => $colName) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        if (in_array($colName, $wrapColumns)) {
            $sheet->getColumnDimension($colLetter)->setWidth(40);
            $sheet->getStyle("{$colLetter}2:{$colLetter}" . ($rowIdx - 1))->getAlignment()->setWrapText(true);
        } else {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }

    // Border
    $totalRows = count($data) + 1;
    $sheet->getStyle("A1:{$lastColLetter}{$totalRows}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CBD5E1']
            ]
        ]
    ]);

    try {
        $writer = new Xlsx($spreadsheet);
        $xlsxFile = $dir . '/' . $baseName . '.xlsx';
        $writer->save($xlsxFile);
        echo " -> Đã tạo file: {$baseName}.xlsx\n";
    } catch (\Throwable $e) {
        // Nếu người dùng đang mở file trong Excel thì lưu ra file _Moi
        $altFile = $dir . '/' . $baseName . '_CapNhat.xlsx';
        $writer->save($altFile);
        echo " -> Lưu ý: {$baseName}.xlsx đang được mở trong Excel, đã lưu bản mới tại: {$baseName}_CapNhat.xlsx\n";
    }
}

echo "=== BẮT ĐẦU TẠO FILE ĐỀ TÀI MỚI PHÂN THEO GIẢNG VIÊN, HỌC KỲ & HỌC PHẦN ===\n";

// Tạo 2 bộ file với tên thân thiện
exportDualFiles('DanhSach_DeTai_Moi_TheoHocPhan_HocKy', $headers, $data, $outputDir);
exportDualFiles('11_DeTai_KhoaLuan_Moi', $headers, $data, $outputDir);

echo "=== HOÀN TẤT THÀNH CÔNG! ===\n";
