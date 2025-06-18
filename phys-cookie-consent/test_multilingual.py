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

class TestMultilingual(unittest.TestCase):
    """Test cases for multilingual support of cookie consent plugin."""
    
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
    
    def tearDown(self):
        """Clean up after each test."""
        if self.driver:
            self.driver.quit()
    
    def test_7_1_language_detection(self):
        """Kịch bản 7.1: Kiểm tra phát hiện ngôn ngữ."""
        # Thiết lập ngôn ngữ trình duyệt là tiếng Anh
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--lang=en-US")
        
        # Khởi tạo WebDriver với ngôn ngữ tiếng Anh
        driver_en = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        
        try:
            # Truy cập trang web
            driver_en.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner_en = WebDriverWait(driver_en, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Lấy nội dung tiếng Anh
            banner_title_en = cookie_banner_en.find_element(By.CSS_SELECTOR, ".cookie-title").text
            accept_button_en = cookie_banner_en.find_element(By.CSS_SELECTOR, ".accept-all").text
            
            # Đóng trình duyệt tiếng Anh
            driver_en.quit()
            
            # Thiết lập ngôn ngữ trình duyệt là tiếng Việt
            chrome_options = Options()
            chrome_options.add_argument("--incognito")
            chrome_options.add_argument("--window-size=1920,1080")
            chrome_options.add_argument("--lang=vi-VN")
            
            # Khởi tạo WebDriver với ngôn ngữ tiếng Việt
            driver_vi = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
            
            # Truy cập trang web
            driver_vi.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner_vi = WebDriverWait(driver_vi, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Lấy nội dung tiếng Việt
            banner_title_vi = cookie_banner_vi.find_element(By.CSS_SELECTOR, ".cookie-title").text
            accept_button_vi = cookie_banner_vi.find_element(By.CSS_SELECTOR, ".accept-all").text
            
            # Đóng trình duyệt tiếng Việt
            driver_vi.quit()
            
            # So sánh nội dung
            self.assertNotEqual(banner_title_en, banner_title_vi, 
                              "Tiêu đề banner không thay đổi theo ngôn ngữ")
            self.assertNotEqual(accept_button_en, accept_button_vi, 
                              "Nút chấp nhận không thay đổi theo ngôn ngữ")
            
            # Kiểm tra nội dung tiếng Anh
            self.assertIn("cookie", banner_title_en.lower(), 
                        "Tiêu đề tiếng Anh không chứa từ 'cookie'")
            self.assertIn("accept", accept_button_en.lower(), 
                        "Nút chấp nhận tiếng Anh không chứa từ 'accept'")
            
            # Kiểm tra nội dung tiếng Việt
            self.assertIn("cookie", banner_title_vi.lower(), 
                        "Tiêu đề tiếng Việt không chứa từ 'cookie'")
            self.assertIn("chấp nhận", accept_button_vi.lower(), 
                        "Nút chấp nhận tiếng Việt không chứa từ 'chấp nhận'")
            
        except (TimeoutException, NoSuchElementException) as e:
            if 'driver_en' in locals() and driver_en:
                driver_en.quit()
            if 'driver_vi' in locals() and driver_vi:
                driver_vi.quit()
            self.fail(f"Lỗi khi kiểm tra phát hiện ngôn ngữ: {str(e)}")
    
    def test_7_2_translation_completeness(self):
        """Kịch bản 7.2: Kiểm tra độ đầy đủ của bản dịch."""
        # Đăng nhập vào WordPress admin
        self.driver.get(f"{self.BASE_URL}/wp-login.php")
        
        username_field = self.driver.find_element(By.ID, "user_login")
        password_field = self.driver.find_element(By.ID, "user_pass")
        submit_button = self.driver.find_element(By.ID, "wp-submit")
        
        username_field.send_keys("admin")
        password_field.send_keys("!M2y4n0d2s96")
        submit_button.click()
        
        try:
            # Thay đổi ngôn ngữ WordPress sang tiếng Việt
            # Lưu ý: Trong thực tế, bạn cần cài đặt gói ngôn ngữ tiếng Việt trước
            self.driver.get(f"{self.BASE_URL}/wp-admin/options-general.php")
            
            # Kiểm tra xem có thể thay đổi ngôn ngữ không
            try:
                language_dropdown = self.driver.find_element(By.ID, "WPLANG")
                
                # Nếu có dropdown ngôn ngữ, thử thay đổi sang tiếng Việt
                from selenium.webdriver.support.ui import Select
                select = Select(language_dropdown)
                select.select_by_value("vi")
                
                # Lưu cài đặt
                save_button = self.driver.find_element(By.ID, "submit")
                save_button.click()
                
                # Đợi cho trang tải lại
                WebDriverWait(self.driver, 10).until(
                    EC.presence_of_element_located((By.ID, "WPLANG"))
                )
            except NoSuchElementException:
                # Nếu không tìm thấy dropdown, bỏ qua bước này
                pass
            
            # Truy cập trang cài đặt cookie
            self.driver.get(f"{self.BASE_URL}/wp-admin/admin.php?page=phys-cookie-consent")
            
            # Đợi cho trang tải xong
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "cookie-consent-settings"))
            )
            
            # Lấy tất cả nhãn và trường nhập liệu
            labels = self.driver.find_elements(By.CSS_SELECTOR, "label")
            inputs = self.driver.find_elements(By.CSS_SELECTOR, "input[type='text'], textarea")
            
            # Kiểm tra không có nhãn hoặc placeholder bị thiếu bản dịch
            for label in labels:
                # Kiểm tra không có chuỗi mặc định tiếng Anh không được dịch
                label_text = label.text.lower()
                self.assertFalse(
                    (label_text.startswith("default") or label_text.startswith("english")) and "translation" in label_text,
                    f"Nhãn '{label.text}' có thể chưa được dịch"
                )
            
            for input_field in inputs:
                placeholder = input_field.get_attribute("placeholder")
                if placeholder:
                    # Kiểm tra không có placeholder mặc định tiếng Anh không được dịch
                    placeholder_text = placeholder.lower()
                    self.assertFalse(
                        (placeholder_text.startswith("default") or placeholder_text.startswith("english")) and "translation" in placeholder_text,
                        f"Placeholder '{placeholder}' có thể chưa được dịch"
                    )
            
            # Đăng xuất
            self.driver.get(f"{self.BASE_URL}/wp-login.php?action=logout")
            confirm_logout = WebDriverWait(self.driver, 10).until(
                EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'log out')]"))
            )
            confirm_logout.click()
            
            # Truy cập trang chủ để kiểm tra banner
            self.driver.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Lấy tất cả văn bản trong banner
            banner_text = cookie_banner.text
            
            # Kiểm tra không có chuỗi mặc định tiếng Anh không được dịch
            self.assertFalse(
                "default" in banner_text.lower() and "translation" in banner_text.lower(),
                "Banner có thể chứa văn bản chưa được dịch"
            )
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra độ đầy đủ của bản dịch: {str(e)}")
    
    def test_7_3_rtl_support(self):
        """Kịch bản 7.3: Kiểm tra hỗ trợ ngôn ngữ RTL (phải sang trái)."""
        # Thiết lập ngôn ngữ trình duyệt là tiếng Ả Rập
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--lang=ar")
        
        # Khởi tạo WebDriver với ngôn ngữ tiếng Ả Rập
        driver_rtl = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        
        try:
            # Truy cập trang web
            driver_rtl.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(driver_rtl, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra thuộc tính dir hoặc class RTL
            dir_attribute = cookie_banner.get_attribute("dir")
            rtl_class = "rtl" in cookie_banner.get_attribute("class").lower()
            
            # Kiểm tra hỗ trợ RTL
            self.assertTrue(
                dir_attribute == "rtl" or rtl_class,
                "Banner không hỗ trợ RTL cho ngôn ngữ tiếng Ả Rập"
            )
            
            # Kiểm tra CSS text-align
            text_align = driver_rtl.execute_script(
                "return window.getComputedStyle(arguments[0]).getPropertyValue('text-align');",
                cookie_banner
            )
            
            # Kiểm tra căn lề phải cho RTL
            self.assertIn(
                text_align,
                ["right", "start"],
                f"Văn bản không được căn lề phải cho RTL: {text_align}"
            )
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra hỗ trợ RTL: {str(e)}")
        finally:
            # Đóng trình duyệt
            if 'driver_rtl' in locals() and driver_rtl:
                driver_rtl.quit()

if __name__ == '__main__':
    unittest.main()
