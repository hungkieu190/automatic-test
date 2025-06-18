#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import unittest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

class TestCookieConsentFunctionality(unittest.TestCase):
    """Test cases for cookie consent functionality."""
    
    BASE_URL = "https://demowp.online/"  # Thay đổi URL này theo cấu hình của bạn
    
    def setUp(self):
        """Set up test environment before each test."""
        # Thiết lập Chrome với chế độ ẩn danh
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        
        # Khởi tạo WebDriver
        self.driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        self.driver.implicitly_wait(10)
        
        # Xóa tất cả cookie trước mỗi test
        self.driver.delete_all_cookies()
        
        # Truy cập trang web
        self.driver.get(self.BASE_URL)
        
        # Đợi cho banner hiển thị
        try:
            self.cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
        except TimeoutException:
            self.fail("Cookie banner không hiển thị")
    
    def tearDown(self):
        """Clean up after each test."""
        if self.driver:
            self.driver.quit()
    
    def test_2_1_accept_all_cookies(self):
        """Kịch bản 2.1: Kiểm tra nút \"Chấp nhận tất cả\"."""
        try:
            # Nhấn nút chấp nhận tất cả
            accept_button = self.driver.find_element(
                By.CSS_SELECTOR, 
                "#physcookie-banner .cookie-action button[onclick='physCookieAcceptAll()']"
            )
            accept_button.click()

            # Đợi cho banner biến mất khỏi DOM
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "physcookie-banner"))
            )
            
            # Kiểm tra cookie đã được lưu
            cookies = self.driver.get_cookies()
            consent_cookie = next(
                (cookie for cookie in cookies if cookie['name'] == 'physcookie-consent'), 
                None
            )
            
            self.assertIsNotNone(consent_cookie, "Cookie đồng ý không được lưu")
            
            # Kiểm tra giá trị cookie chứa tất cả danh mục
            cookie_value = consent_cookie['value']
            self.assertIn("analytics", cookie_value, "Cookie phân tích không được chấp nhận")
            self.assertIn("ads", cookie_value, "Cookie quảng cáo không được chấp nhận")
            self.assertIn("functional", cookie_value, "Cookie chức năng không được chấp nhận")
            
            # Kiểm tra các script liên quan đã được tải
            # Lưu ý: Điều này phụ thuộc vào cách triển khai cụ thể của plugin
            # Ví dụ kiểm tra Google Analytics đã được tải
            ga_loaded = self.driver.execute_script(
                "return typeof ga !== 'undefined' || typeof gtag !== 'undefined' || typeof __gaTracker !== 'undefined'"
            )
            self.assertTrue(ga_loaded, "Script Google Analytics không được tải sau khi chấp nhận")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra nút chấp nhận tất cả: {str(e)}")
    
    def test_2_2_reject_all_cookies(self):
        """Kịch bản 2.2: Kiểm tra nút \"Từ chối tất cả\"."""
        try:
            # Nhấn nút từ chối tất cả
            reject_button = self.driver.find_element(
                By.CSS_SELECTOR, 
                "#physcookie-banner .cookie-action button[onclick='physCookieRejectAll()']"
            )
            reject_button.click()

            # Đợi cho banner biến mất khỏi DOM
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "physcookie-banner"))
            )
            
            # Kiểm tra cookie đã được lưu
            cookies = self.driver.get_cookies()
            consent_cookie = next(
                (cookie for cookie in cookies if cookie['name'] == 'physcookie-consent'), 
                None
            )
            
            self.assertIsNotNone(consent_cookie, "Cookie đồng ý không được lưu")
            
            # Kiểm tra giá trị cookie chỉ chứa cookie cần thiết
            cookie_value = consent_cookie['value']
            self.assertIn("necessary", cookie_value, "Cookie cần thiết không được chấp nhận")
            self.assertNotIn("analytics", cookie_value, "Cookie phân tích vẫn được chấp nhận dù từ chối")
            self.assertNotIn("ads", cookie_value, "Cookie quảng cáo vẫn được chấp nhận dù từ chối")
            self.assertNotIn("functional", cookie_value, "Cookie chức năng vẫn được chấp nhận dù từ chối")
            
            # Kiểm tra các script không cần thiết không được tải
            # Lưu ý: Điều này phụ thuộc vào cách triển khai cụ thể của plugin
            ga_loaded = self.driver.execute_script(
                "return typeof ga !== 'undefined' || typeof gtag !== 'undefined' || typeof __gaTracker !== 'undefined'"
            )
            self.assertFalse(ga_loaded, "Script Google Analytics vẫn được tải dù từ chối")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra nút từ chối tất cả: {str(e)}")
    
    def test_2_3_customize_cookie_settings(self):
        """Kịch bản 2.3: Kiểm tra tùy chỉnh cài đặt cookie."""
        try:
            # Nhấn nút tùy chỉnh
            customize_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .cookie-action button.btn-outline[onclick=\"physCustomise()\"]")
            customize_button.click()
            
            # Đợi cho giao diện tùy chỉnh hiển thị
            cookie_settings = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "physcookie-customise"))
            )
            
            # Tắt cookie phân tích, bật cookie quảng cáo và chức năng
            analytics_checkbox = self.driver.find_element(By.CSS_SELECTOR, "#consent-analytics")
            if analytics_checkbox.is_selected():
                analytics_checkbox.click()  # Tắt nếu đang bật
                
            ads_checkbox = self.driver.find_element(By.CSS_SELECTOR, "#consent-ads")
            if not ads_checkbox.is_selected():
                ads_checkbox.click()  # Bật nếu đang tắt
                
            functional_checkbox = self.driver.find_element(By.CSS_SELECTOR, "#consent-functional")
            if not functional_checkbox.is_selected():
                functional_checkbox.click()  # Bật nếu đang tắt
            
            # Lưu cài đặt
            save_button = self.driver.find_element(By.CSS_SELECTOR, "#physcookie-customise .cookie-action button.btn-outline[onclick=\"savePhysConsent()\"]")
            save_button.click()
            
            # Đợi cho modal biến mất
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "physcookie-customise"))
            )
            
            # Kiểm tra cookie đã được lưu đúng theo lựa chọn
            cookies = self.driver.get_cookies()
            consent_cookie = next((cookie for cookie in cookies if cookie['name'] == 'physcookie-consent'), None)
            
            self.assertIsNotNone(consent_cookie, "Cookie đồng ý không được lưu")
            
            # Kiểm tra giá trị cookie phản ánh lựa chọn của người dùng
            cookie_value = consent_cookie['value']
            self.assertIn("necessary", cookie_value, "Cookie cần thiết không được chấp nhận")
            self.assertNotIn("analytics", cookie_value, "Cookie phân tích vẫn được chấp nhận dù đã tắt")
            self.assertIn("ads", cookie_value, "Cookie quảng cáo không được chấp nhận dù đã bật")
            self.assertIn("functional", cookie_value, "Cookie chức năng không được chấp nhận dù đã bật")
            
            # Kiểm tra script tương ứng
            ga_loaded = self.driver.execute_script(
                "return typeof ga !== 'undefined' || typeof gtag !== 'undefined' || typeof __gaTracker !== 'undefined'"
            )
            self.assertFalse(ga_loaded, "Script Google Analytics vẫn được tải dù đã tắt")
            
            # Làm mới trang để kiểm tra các script quảng cáo đã tải
            self.driver.refresh()
            
            # Kiểm tra script quảng cáo đã tải (ví dụ: Google Ads)
            ads_loaded = self.driver.execute_script(
                "return typeof googletag !== 'undefined' || typeof adsbygoogle !== 'undefined'"
            )
            self.assertTrue(ads_loaded, "Script quảng cáo không được tải dù đã bật")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra tùy chỉnh cài đặt cookie: {str(e)}")
    
    def test_2_4_invalid_request_handling(self):
        """Kịch bản 2.4: Kiểm tra lỗi khi gửi yêu cầu không hợp lệ."""
        # Lưu ý: Test này giả định rằng bạn có quyền truy cập vào console của trình duyệt
        # Trong thực tế, bạn có thể cần sử dụng công cụ như Postman để gửi yêu cầu AJAX giả mạo
        
        try:
            # Gửi yêu cầu AJAX giả mạo với dữ liệu không hợp lệ
            invalid_request_result = self.driver.execute_script("""
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl || '/wp-admin/admin-ajax.php', false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('action=cookie_consent_settings&cookie_category=invalid_category');
                return xhr.status;
            """)
            
            # Kiểm tra phản hồi lỗi (mã 400 hoặc 500)
            self.assertIn(invalid_request_result, [400, 500], "Yêu cầu không hợp lệ không trả về lỗi")
            
            # Kiểm tra trang web vẫn hoạt động bình thường
            self.driver.refresh()
            
            # Kiểm tra banner vẫn hiển thị sau khi làm mới trang
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            self.assertTrue(cookie_banner.is_displayed(), "Cookie banner không hiển thị sau khi xử lý yêu cầu không hợp lệ")
            
        except Exception as e:
            self.fail(f"Lỗi khi kiểm tra xử lý yêu cầu không hợp lệ: {str(e)}")

if __name__ == '__main__':
    unittest.main()
