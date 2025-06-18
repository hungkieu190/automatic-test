#!/bin/bash

# Kiểm tra Python đã được cài đặt chưa
if ! command -v python3 &> /dev/null; then
    echo "Python không được tìm thấy. Vui lòng cài đặt Python 3.8+ trước khi tiếp tục."
    exit 1
fi

# Tạo và kích hoạt môi trường ảo
echo "Tạo môi trường ảo Python..."
python3 -m venv venv

# Kích hoạt môi trường ảo
if [[ "$OSTYPE" == "darwin"* ]] || [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # macOS hoặc Linux
    source venv/bin/activate
elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
    # Windows
    source venv/Scripts/activate
else
    echo "Hệ điều hành không được hỗ trợ. Vui lòng kích hoạt môi trường ảo theo cách thủ công."
    exit 1
fi

# Cài đặt các gói phụ thuộc
echo "Cài đặt các gói phụ thuộc..."
pip install -r requirements.txt

# Cài đặt ChromeDriver
echo "Cài đặt ChromeDriver..."
pip install webdriver-manager

# Kiểm tra Chrome đã được cài đặt chưa
if ! command -v google-chrome &> /dev/null && ! command -v chrome &> /dev/null && ! [ -f "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" ] && ! [ -f "C:/Program Files/Google/Chrome/Application/chrome.exe" ] && ! [ -f "C:/Program Files (x86)/Google/Chrome/Application/chrome.exe" ]; then
    echo "CẢNH BÁO: Google Chrome không được tìm thấy. Vui lòng cài đặt Google Chrome trước khi chạy các test."
fi

echo "Thiết lập hoàn tất! Để chạy các test:"
echo "1. Kích hoạt môi trường ảo (nếu chưa kích hoạt):"
if [[ "$OSTYPE" == "darwin"* ]] || [[ "$OSTYPE" == "linux-gnu"* ]]; then
    echo "   source venv/bin/activate"
else
    echo "   venv\\Scripts\\activate"
fi
echo "2. Chạy test đơn lẻ:"
echo "   python -m unittest phys-cookie-consent/test_consent_functionality.py"
echo "3. Chạy tất cả các test:"
echo "   python phys-cookie-consent/run_all_tests.py"
