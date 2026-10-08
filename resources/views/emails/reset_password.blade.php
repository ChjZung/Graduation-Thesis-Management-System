<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #003B73 0%, #0072CE 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 25px;
            line-height: 1.6;
        }
        .content p {
            margin-bottom: 18px;
            font-size: 15px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0072CE, #004A8F);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 114, 206, 0.3);
        }
        .note {
            background-color: #f8fafc;
            border-left: 4px solid #0072CE;
            padding: 12px 15px;
            font-size: 13px;
            color: #64748b;
            margin-top: 20px;
            border-radius: 4px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 15px 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .break-link {
            word-break: break-all;
            color: #0072CE;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Hệ Thống Quản Lý Khóa Luận Tốt Nghiệp</h2>
            <p>Trường Đại Học Công Thương TP. Hồ Chí Minh (HUIT)</p>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $name }}</strong>,</p>
            <p>Hệ thống vừa nhận được yêu cầu đặt lại mật khẩu cho tài khoản sử dụng địa chỉ email này.</p>
            <p>Vui lòng nhấn vào nút bên dưới để tiến hành đặt lại mật khẩu của bạn:</p>
            
            <div class="btn-container">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Đặt Lại Mật Khẩu</a>
            </div>

            <div class="note">
                <p style="margin: 0 0 5px 0; font-weight: bold; color: #334155;">Lưu ý quan trọng:</p>
                <ul style="margin: 0; padding-left: 18px;">
                    <li>Liên kết đặt lại mật khẩu này chỉ có hiệu lực trong vòng <strong>15 phút</strong> kể từ khi gửi.</li>
                    <li>Liên kết này chỉ có thể sử dụng được <strong>1 lần duy nhất</strong>.</li>
                    <li>Nếu bạn không yêu cầu đặt lại mật khẩu, xin vui lòng bỏ qua email này. Mật khẩu của bạn vẫn hoàn toàn an toàn.</li>
                </ul>
            </div>

            <p style="margin-top: 25px; font-size: 13px; color: #64748b;">
                Nếu nút trên không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt web:<br>
                <a href="{{ $resetUrl }}" class="break-link">{{ $resetUrl }}</a>
            </p>
        </div>
        <div class="footer">
            HUIT &copy; {{ date('Y') }} - Hệ thống quản lý công tác khóa luận tốt nghiệp.
        </div>
    </div>
</body>
</html>
