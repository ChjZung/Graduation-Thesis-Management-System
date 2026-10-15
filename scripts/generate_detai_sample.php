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

$data = [
    [
        'DT01',
        'Xây dựng hệ thống quản lý và hỗ trợ học tập trực tuyến tích hợp AI Assistant cho trường Đại học Công Thương TP.HCM',
        'Nghiên cứu thiết kế kiến trúc vi dịch vụ (Microservices), phát triển nền tảng học tập trực tuyến hỗ trợ tương tác thời gian thực, tích hợp trợ lý ảo AI giải đáp thắc mắc bài giảng.',
        'Thành thạo lập trình Web Fullstack (NodeJS/Laravel, ReactJS/VueJS), kiến thức về RESTful API, Docker và hiểu biết cơ bản về tích hợp LLM API.',
        'Công nghệ phần mềm',
        'HP_KLCN_CNPM',
        'Khóa luận cử nhân - Công nghệ phần mềm',
        'GV00000002',
        'TS. Trần Anh Dũng',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT02',
        'Phát triển ứng dụng di động theo dõi sức khỏe và lịch trình rèn luyện thể thao đa nền tảng với Flutter',
        'Xây dựng ứng dụng di động đa nền tảng kết nối các thiết bị đeo thông minh (Wearables) qua BLE, phân tích chỉ số vận động và đưa ra cảnh báo sức khỏe cá nhân hóa.',
        'Có kỹ năng lập trình Dart/Flutter, nắm vững mô hình State Management (Bloc/Provider), giao tiếp qua giao thức Bluetooth Low Energy và cơ sở dữ liệu SQLite.',
        'Công nghệ phần mềm',
        'HP_KLTN_CNPM',
        'Khóa luận tốt nghiệp - Công nghệ phần mềm',
        'GV00000021',
        'ThS. Đặng Hải Giang',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT03',
        'Thiết kế và triển khai hệ thống hoạch định nguồn lực doanh nghiệp (ERP) mini cho chuỗi bán lẻ',
        'Phân tích nghiệp vụ bán lẻ đa kênh (Omnichannel), thiết kế cơ sở dữ liệu quan hệ tối ưu hóa hiệu năng, xây dựng các phân hệ quản lý kho hàng, đơn hàng và báo cáo tài chính.',
        'Thành thạo thiết kế cơ sở dữ liệu quan hệ (PostgreSQL/MySQL), kiến thức nghiệp vụ quản lý kho, bán hàng và thành thạo lập trình Backend.',
        'Hệ thống thông tin',
        'HP_KLCN_HTTT',
        'Khóa luận cử nhân - Hệ thống thông tin',
        'GV00000003',
        'TS. Phạm Minh Tuấn',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT04',
        'Xây dựng kho dữ liệu và hệ thống thông tin hỗ trợ ra quyết định kinh doanh (BI) cho bệnh viện',
        'Xây dựng quy trình trích xuất, chuyển đổi và nạp dữ liệu (ETL) từ các nguồn dữ liệu phân tán, thiết kế mô hình Data Warehouse dạng Star Schema và trực quan hóa dashboard PowerBI/Tableau.',
        'Kỹ năng viết truy vấn SQL nâng cao, kiến thức về ETL/Data Warehouse, thành thạo công cụ trực quan hóa dữ liệu PowerBI hoặc Apache Superset.',
        'Hệ thống thông tin',
        'HP_KLTN_HTTT',
        'Khóa luận tốt nghiệp - Hệ thống thông tin',
        'GV00000022',
        'ThS. Trần Thị Bích',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT05',
        'Nghiên cứu phát hiện tấn công giả mạo (Phishing) và mã độc trong thư điện tử bằng kỹ thuật học máy',
        'Nghiên cứu các đặc trưng kỹ thuật của email lừa đảo, huấn luyện mô hình Machine Learning phân loại văn bản và tệp đính kèm độc hại, tích hợp giải pháp thành tiện ích mở rộng bảo vệ người dùng.',
        'Kiến thức vững về an toàn mạng, giao thức email (SMTP, IMAP), thành thạo lập trình Python và thư viện Scikit-learn, Regex parsing.',
        'An toàn thông tin',
        'HP_KLCN_ATTT',
        'Khóa luận cử nhân - An toàn thông tin',
        'GV00000004',
        'TS. Trịnh Văn Thành',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT06',
        'Xây dựng hệ thống giám sát an ninh mạng (SIEM) mã nguồn mở và cảnh báo sớm nguy cơ tấn công cho doanh nghiệp vừa và nhỏ',
        'Thu thập, chuẩn hóa và tổng hợp log từ các máy chủ và thiết bị mạng thông qua Wazuh và Elastic Stack (ELK), xây dựng các luật phát hiện xâm nhập và gửi thông báo khẩn cấp tới quản trị viên.',
        'Hiểu biết sâu về hệ thống Linux/Network, kiến thức về các mô hình tấn công mạng (MITRE ATT&CK), kỹ năng cấu hình ElasticSearch, Logstash, Kibana.',
        'An toàn thông tin',
        'HP_KLTN_ATTT',
        'Khóa luận tốt nghiệp - An toàn thông tin',
        'GV00000024',
        'ThS. Ngô Đức Hải',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT07',
        'Nghiên cứu thuật toán nhận diện khuôn mặt và nhận dạng cảm xúc thời gian thực ứng dụng trong lớp học thông minh',
        'Nghiên cứu các mô hình mạng nơ-ron tích chập (CNN) hiện đại cho bài toán Face Recognition và Emotion Detection, tối ưu hóa mô hình chạy trên thiết bị biên (Edge Computing).',
        'Nền tảng toán học và xử lý ảnh tốt, thành thạo Python, PyTorch hoặc TensorFlow, OpenCV, có kinh nghiệm huấn luyện mô hình Deep Learning.',
        'Khoa học máy tính',
        'HP_KLCN_KHMT',
        'Khóa luận cử nhân - Khoa học máy tính',
        'GV00000001',
        'PGS.TS. Nguyễn Văn Hùng',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT08',
        'Ứng dụng xử lý ngôn ngữ tự nhiên (NLP) tóm tắt văn bản pháp quy tiếng Việt tự động',
        'Nghiên cứu các kỹ thuật tóm tắt văn bản trích xuất (Extractive) và sinh văn bản (Abstractive) dựa trên kiến trúc Transformer và mô hình tiền huấn luyện tiếng Việt (PhoBERT, ViT5).',
        'Hiểu biết về kiến trúc Transformer, kinh nghiệm fine-tuning mô hình ngôn ngữ lớn (LLM), thành thạo thư viện HuggingFace Transformers và PyTorch.',
        'Khoa học máy tính',
        'HP_KLTN_KHMT',
        'Khóa luận tốt nghiệp - Khoa học máy tính',
        'GV00000006',
        'PGS.TS. Võ Quốc Phong',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT09',
        'Xây dựng hệ thống gợi ý đề tài và tài liệu học tập cá nhân hóa dựa trên học máy và đồ thị tri thức (Knowledge Graph)',
        'Phân tích lịch sử học tập và sở thích nghiên cứu của sinh viên, xây dựng đồ thị tri thức học thuật, áp dụng thuật toán Graph Neural Networks và Collaborative Filtering để đưa ra đề xuất tối ưu.',
        'Nắm vững thuật toán hệ thống gợi ý (Recommendation Systems), kỹ năng làm việc với Graph Database (Neo4j), thành thạo Python và Pandas/Numpy.',
        'Khoa học dữ liệu',
        'HP_KLCN_KHDL',
        'Khóa luận cử nhân - Khoa học dữ liệu & AI',
        'GV00000007',
        'TS. Đỗ Anh Minh',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT10',
        'Dự báo nhu cầu năng lượng tái tạo và tối ưu hóa điều độ lưới điện thông minh bằng mô hình chuỗi thời gian',
        'Thu thập dữ liệu cảm biến khí tượng và phụ tải điện, áp dụng các mô hình chuỗi thời gian (ARIMA, LSTM, Prophet) để dự báo sản lượng điện mặt trời/gió phục vụ vận hành lưới điện.',
        'Hiểu biết về phân tích chuỗi thời gian (Time-series analysis), thành thạo thư viện Deep Learning (Keras/PyTorch), tư duy phân tích thống kê định lượng tốt.',
        'Khoa học dữ liệu',
        'HP_KLTN_KHDL',
        'Khóa luận tốt nghiệp - Khoa học dữ liệu & AI',
        'GV00000025',
        'TS. Dương Thúy Hằng',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT11',
        'Thiết kế và triển khai hệ thống IoT quan trắc môi trường thông minh ứng dụng giao thức MQTT và LoRaWAN',
        'Thiết kế mạch cảm biến thu thập thông số bụi mịn, nhiệt độ, độ ẩm truyền qua sóng tầm xa LoRaWAN, máy chủ gateway xử lý gói tin qua MQTT Broker và lưu trữ TimescaleDB.',
        'Có kỹ năng lập trình vi điều khiển (ESP32/STM32), hiểu rõ giao thức truyền thông IoT (MQTT, HTTP, LoRa), kiến thức quản trị máy chủ mạng Linux.',
        'Mạng máy tính',
        'HP_KLCN_MMT',
        'Khóa luận cử nhân - Mạng máy tính & Viễn thông',
        'GV00000005',
        'TS. Lê Hải Đăng',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
    [
        'DT12',
        'Xây dựng giải pháp cân bằng tải và giám sát hạ tầng đám mây tự động dựa trên Kubernetes và Prometheus',
        'Triển khai cụm Kubernetes có khả năng tự động co giãn (Horizontal Pod Autoscaling), thiết lập hệ thống giám sát Prometheus kết hợp cảnh báo Grafana và điều hướng tải NGINX Ingress Controller.',
        'Thành thạo công nghệ ảo hóa và container (Docker, Kubernetes), kiến thức về định tuyến mạng, DNS, chứng chỉ SSL và công cụ DevOps.',
        'Mạng máy tính',
        'HP_KLTN_MMT',
        'Khóa luận tốt nghiệp - Mạng máy tính & Viễn thông',
        'GV00000026',
        'ThS. Lý Thanh Lan',
        'HK2627_1',
        3,
        'Chờ duyệt cấp Bộ môn'
    ],
];

function writeDualFiles($baseName, $headers, $data, $dir) {
    // 1. CSV (UTF-8 with BOM)
    $csvFile = $dir . '/' . $baseName . '.csv';
    $fp = fopen($csvFile, 'w');
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($data as $r) {
        fputcsv($fp, $r);
    }
    fclose($fp);
    echo " -> Đã tạo {$baseName}.csv\n";

    // 2. XLSX
    $xlsxFile = $dir . '/' . $baseName . '.xlsx';
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('DeTai_KhoaLuan');

    foreach ($headers as $colIdx => $h) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue("{$colLetter}1", $h);
    }

    $rowIdx = 2;
    foreach ($data as $row) {
        foreach ($row as $colIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValueExplicit("{$colLetter}{$rowIdx}", (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $rowIdx++;
    }

    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $lastRow = max(2, $rowIdx - 1);

    // Style Header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '0072CE']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]
        ]
    ];
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // Style Data
    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ]
    ];
    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);

    // Column widths
    $widths = [
        'A' => 12, // MaDeTai
        'B' => 45, // TenDeTai
        'C' => 50, // MoTa
        'D' => 45, // YeuCau
        'E' => 22, // LinhVuc
        'F' => 16, // MaHocPhan
        'G' => 38, // TenHocPhan
        'H' => 15, // MaGV
        'I' => 25, // TenGV
        'J' => 14, // MaHocKy
        'K' => 16, // SoLuongSinhVienToiDa
        'L' => 15  // TrangThai
    ];
    foreach ($widths as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    try {
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxFile);
        echo " -> Đã tạo {$baseName}.xlsx\n";
    } catch (\Throwable $e) {
        $altFile = $dir . '/' . $baseName . '_CapNhat.xlsx';
        $writer->save($altFile);
        echo " -> Lưu ý: {$baseName}.xlsx đang mở, đã tạo: {$baseName}_CapNhat.xlsx\n";
    }
}

writeDualFiles('11_DeTai_KhoaLuan_Mau', $headers, $data, $outputDir);
writeDualFiles('DanhSach_DeTai_Mau', $headers, $data, $outputDir);

echo "Hoàn tất tạo file mẫu Đề tài khóa luận tốt nghiệp!\n";
