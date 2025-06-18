# Automatic Test Suite - ThimPress

Bộ test tự động cho Cookie Consent Plugin và các plugin khác của ThimPress.

## Cấu trúc dự án

```
thimpress/
├── docs/                     # Tài liệu hướng dẫn
├── phys-cookie-consent/      # Test cho Cookie Consent Plugin
│   ├── test_compatibility.py # Test tương thích
│   ├── test_functionality.py # Test chức năng
│   ├── test_multilingual.py  # Test đa ngôn ngữ
│   ├── test_security.py      # Test bảo mật
│   └── run_all_tests.py      # Script chạy tất cả test
├── source-code/              # Mã nguồn plugin
├── .gitignore                # Cấu hình Git ignore
├── requirements.txt          # Danh sách thư viện Python
├── setup.sh                  # Script cài đặt cho Linux/macOS
└── setup.bat                 # Script cài đặt cho Windows
```

## Cài đặt

### Yêu cầu

- Python 3.8+
- Google Chrome
- Git

### Cài đặt trên Linux/macOS

```bash
# Clone repository
git clone https://github.com/hungkieu190/automatic-test.git
cd automatic-test/thimpress

# Chạy script cài đặt
chmod +x setup.sh
./setup.sh
```

### Cài đặt trên Windows

```bash
# Clone repository
git clone https://github.com/hungkieu190/automatic-test.git
cd automatic-test\thimpress

# Chạy script cài đặt
setup.bat
```

## Chạy test

### Kích hoạt môi trường ảo

**Linux/macOS:**
```bash
source venv/bin/activate
```

**Windows:**
```bash
venv\Scripts\activate
```

### Chạy test đơn lẻ

```bash
# Chạy test chức năng chấp nhận cookie
python -m unittest phys-cookie-consent/test_consent_functionality.TestCookieConsentFunctionality.test_2_1_accept_all_cookies

# Chạy test từ chối cookie
python -m unittest phys-cookie-consent/test_consent_functionality.TestCookieConsentFunctionality.test_2_2_reject_all_cookies
```

### Chạy tất cả test

```bash
python phys-cookie-consent/run_all_tests.py
```

## Báo cáo lỗi

Nếu bạn gặp lỗi khi chạy test, vui lòng tạo issue trên GitHub với các thông tin sau:
1. Môi trường (OS, phiên bản Python, phiên bản Chrome)
2. Mô tả lỗi
3. Log lỗi
4. Các bước tái hiện lỗi

## Đóng góp

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/amazing-feature`)
3. Commit thay đổi (`git commit -m 'Add some amazing feature'`)
4. Push lên branch (`git push origin feature/amazing-feature`)
5. Tạo Pull Request
