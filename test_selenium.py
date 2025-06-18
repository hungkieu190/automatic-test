from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time

# Cấu hình ChromeDriver
chrome_options = Options()
# chrome_options.add_argument("--headless")  # Chạy ở chế độ không giao diện (tùy chọn)
chrome_options.add_argument("--no-sandbox")  # Thêm tùy chọn này nếu chạy trên Linux server
# chrome_options.add_argument("--disable-dev-shm-usage")  # Thêm tùy chọn này nếu chạy trên Linux server

# Sử dụng WebDriverManager để tự động tải ChromeDriver phù hợp
service = Service(ChromeDriverManager().install())

# Khởi tạo trình duyệt
driver = webdriver.Chrome(service=service, options=chrome_options)

try:
    # Mở trang đăng nhập WordPress
    driver.get("https://demowp.online/wp-login.php")
    
    # Tìm và điền thông tin đăng nhập
    username_field = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.ID, "user_login"))
    )
    password_field = driver.find_element(By.ID, "user_pass")
    
    # Nhập thông tin đăng nhập
    username_field.send_keys("admin")  # Thay bằng tên người dùng admin
    password_field.send_keys("!M2y4n0d2s96")  # Thay bằng mật khẩu admin
    
    # Nhấn nút đăng nhập
    login_button = driver.find_element(By.ID, "wp-submit")
    login_button.click()
    
    # Chờ và kiểm tra đăng nhập thành công (chuyển hướng đến dashboard)
    WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.ID, "wpadminbar"))
    )
    print("Đăng nhập thành công!")
    
    # Điều hướng đến trang quản lý khóa học của LearnPress
    driver.get("https://demowp.online/wp-admin/edit.php?post_type=lp_course")
    
    # Kiểm tra xem danh sách khóa học có hiển thị hay không
    courses_table = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "wp-list-table"))
    )
    
    # Kiểm tra xem có khóa học nào trong danh sách hay không
    course_rows = courses_table.find_elements(By.CSS_SELECTOR, ".type-lp_course, .post-type-lp_course")
    if len(course_rows) > 0:
        print(f"Tìm thấy {len(course_rows)} khóa học trong danh sách!")
    else:
        print("Không tìm thấy khóa học nào!")
        
except Exception as e:
    print(f"Lỗi xảy ra: {str(e)}")
    
finally:
    # Đóng trình duyệt
    time.sleep(2)  # Đợi 2 giây để quan sát (tùy chọn)
    driver.quit()