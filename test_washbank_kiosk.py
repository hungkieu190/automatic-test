import pytest
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException

class TestWashbankKiosk:
    @classmethod
    def setup_class(cls):
        """Chạy một lần trước tất cả các test"""
        cls.service = Service(ChromeDriverManager().install())
        cls.driver = webdriver.Chrome(service=cls.service)
        cls.driver.implicitly_wait(10)
        
    def setup_method(self):
        """Chạy trước mỗi test case"""
        self.driver.get("https://admin.washbank.co.kr/kiosk/login/")
        self.login()
    
    def teardown_class(cls):
        """Chạy một lần sau tất cả các test"""
        # Bỏ comment dòng dưới nếu muốn đóng trình duyệt sau khi chạy xong
        # cls.driver.quit()
        pass

    def login(self):
        """Hàm đăng nhập"""
        try:
            # Nhập username
            username_input = WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.NAME, "username"))
            )
            username_input.clear()
            username_input.send_keys("s001admin")

            # Nhập password
            password_input = self.driver.find_element(By.NAME, "password")
            password_input.clear()
            password_input.send_keys("ss1adm")

            # Click nút đăng nhập
            submit_btn = self.driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
            submit_btn.click()

            # Chờ đăng nhập thành công
            WebDriverWait(self.driver, 20).until(
                lambda driver: "/kiosk/" in driver.current_url and "login" not in driver.current_url
            )
            print(f"Đăng nhập thành công! URL hiện tại: {self.driver.current_url}")
            
        except Exception as e:
            print(f"Lỗi khi đăng nhập: {str(e)}")
            raise

    def test_check_dashboard(self):
        """Kiểm tra trang dashboard sau khi đăng nhập"""
        try:
            # Kiểm tra các phần tử trên dashboard
            WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, ".dashboard-header"))
            )
            print("Đã tải xong trang dashboard")
            
            # Kiểm tra URL có chứa dashboard
            assert "dashboard" in self.driver.current_url.lower()
            
        except Exception as e:
            print(f"Lỗi khi kiểm tra dashboard: {str(e)}")
            raise

    def test_navigation_menu(self):
        """Test điều hướng menu"""
        try:
            # Ví dụ: Kiểm tra menu nào đó
            # menu_item = self.driver.find_element(By.LINK_TEXT, "Tên menu")
            # menu_item.click()
            # assert "expected_url" in self.driver.current_url
            print("Đang kiểm tra điều hướng menu...")
            
        except Exception as e:
            print(f"Lỗi khi kiểm tra menu: {str(e)}")
            raise

    def test_another_feature(self):
        """Test một tính năng khác"""
        try:
            # Viết test case mới ở đây
            print("Đang kiểm tra tính năng khác...")
            
        except Exception as e:
            print(f"Lỗi khi kiểm tra tính năng: {str(e)}")
            raise

# Để chạy test, sử dụng lệnh:
# pytest test_washbank_kiosk.py -v -s --pdb
# Tùy chọn --pdb sẽ dừng ở điểm lỗi để debug
# Tùy chọn -s để hiển thị print statement
# Tùy chọn -v để hiển thị chi tiết kết quả test