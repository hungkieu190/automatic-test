@echo off
SETLOCAL

REM Kiểm tra Python đã được cài đặt chưa
python --version >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo Python không được tìm thấy. Vui lòng cài đặt Python 3.8+ trước khi tiếp tục.
    exit /b 1
)

REM Tạo và kích hoạt môi trường ảo
echo Tạo môi trường ảo Python...
python -m venv venv

REM Kích hoạt môi trường ảo
echo Kích hoạt môi trường ảo...
call venv\Scripts\activate.bat

REM Cài đặt các gói phụ thuộc
echo Cài đặt các gói phụ thuộc...
pip install -r requirements.txt

REM Cài đặt ChromeDriver
echo Cài đặt ChromeDriver...
pip install webdriver-manager

REM Kiểm tra Chrome đã được cài đặt chưa
IF NOT EXIST "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    IF NOT EXIST "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
        echo CẢNH BÁO: Google Chrome không được tìm thấy. Vui lòng cài đặt Google Chrome trước khi chạy các test.
    )
)

echo.
echo Thiết lập hoàn tất! Để chạy các test:
echo 1. Kích hoạt môi trường ảo (nếu chưa kích hoạt):
echo    venv\Scripts\activate
echo 2. Chạy test đơn lẻ:
echo    python -m unittest phys-cookie-consent\test_consent_functionality.py
echo 3. Chạy tất cả các test:
echo    python phys-cookie-consent\run_all_tests.py

ENDLOCAL
