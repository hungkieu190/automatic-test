#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import unittest
import time
import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.firefox.service import Service as FirefoxService
from selenium.webdriver.edge.service import Service as EdgeService
from selenium.webdriver.safari.service import Service as SafariService
from webdriver_manager.chrome import ChromeDriverManager
from webdriver_manager.firefox import GeckoDriverManager
from webdriver_manager.microsoft import EdgeChromiumDriverManager

class TestCompatibility(unittest.TestCase):
    """Test cases for compatibility with different environments."""
    
    BASE_URL = "https://demowp.online/"  # Thay đổi URL này theo cấu hình của bạn
    
    def setUp(self):
        """Set up test environment before each test."""
        pass
    
    def tearDown(self):
        """Clean up after each test."""
        if hasattr(self, 'driver') and self.driver:
            self.driver.quit()
    
    def test_4_1_theme_compatibility(self):
        """Kịch bản 4.1: Kiểm tra với các theme khác nhau."""
        # Lưu ý: Test này giả định rằng bạn có quyền truy cập vào WordPress admin
        # và có thể thay đổi theme. Trong thực tế, bạn cần thực hiện thao tác này thủ công
        # hoặc sử dụng WordPress REST API để thay đổi theme.
        
        # Danh sách các theme cần kiểm tra
        themes = ['twentytwentythree', 'twentytwentytwo', 'astra']
        
        for theme in themes:
            # Thiết lập Chrome với chế độ ẩn danh
            chrome_options = Options()
            chrome_options.add_argument("--incognito")
            chrome_options.add_argument("--window-size=1920,1080")
            
            # Khởi tạo WebDriver
            self.driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
            self.driver.implicitly_wait(10)
            
            try:
                # Đăng nhập vào WordPress admin (cần thay đổi thông tin đăng nhập)
                self.driver.get(f"{self.BASE_URL}/wp-login.php")
                
                username_field = self.driver.find_element(By.ID, "user_login")
                password_field = self.driver.find_element(By.ID, "user_pass")
                submit_button = self.driver.find_element(By.ID, "wp-submit")
                
                username_field.send_keys("admin")
                password_field.send_keys("!M2y4n0d2s96")
                submit_button.click()
                
                # Chuyển đến trang Appearance > Themes
                self.driver.get(f"{self.BASE_URL}/wp-admin/themes.php")
                
                # Kích hoạt theme
                theme_link = self.driver.find_element(By.XPATH, f"//div[contains(@class, 'theme') and contains(@data-slug, '{theme}')]")
                theme_link.click()
                
                # Nhấn nút Activate
                activate_button = WebDriverWait(self.driver, 10).until(
                    EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Activate')]"))
                )
                activate_button.click()
                
                # Đợi cho theme được kích hoạt
                WebDriverWait(self.driver, 10).until(
                    EC.presence_of_element_located((By.XPATH, f"//div[contains(@class, 'active') and contains(@data-slug, '{theme}')]"))
                )
                
                # Đăng xuất
                self.driver.get(f"{self.BASE_URL}/wp-login.php?action=logout")
                confirm_logout = WebDriverWait(self.driver, 10).until(
                    EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'log out')]"))
                )
                confirm_logout.click()
                
                # Truy cập trang chủ để kiểm tra cookie banner
                self.driver.get(self.BASE_URL)
                
                # Đợi cho banner hiển thị
                cookie_banner = WebDriverWait(self.driver, 10).until(
                    EC.visibility_of_element_located((By.ID, "cookie-banner"))
                )
                
                # Kiểm tra banner hiển thị đúng
                self.assertTrue(cookie_banner.is_displayed(), f"Cookie banner không hiển thị với theme {theme}")
                
                # Kiểm tra các nút trên banner
                accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
                self.assertTrue(accept_button.is_displayed(), f"Nút 'Chấp nhận tất cả' không hiển thị với theme {theme}")
                
                # Đóng trình duyệt sau mỗi lần kiểm tra
                self.driver.quit()
                
            except (TimeoutException, NoSuchElementException) as e:
                self.fail(f"Lỗi khi kiểm tra theme {theme}: {str(e)}")
                if self.driver:
                    self.driver.quit()
    
    def test_4_2_plugin_compatibility(self):
        """Kịch bản 4.2: Kiểm tra với các plugin khác."""
        # Lưu ý: Test này giả định rằng các plugin đã được cài đặt
        # Trong thực tế, bạn cần cài đặt và kích hoạt các plugin trước khi chạy test
        
        # Thiết lập Chrome với chế độ ẩn danh
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        
        # Khởi tạo WebDriver
        self.driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        self.driver.implicitly_wait(10)
        
        try:
            # Truy cập trang web
            self.driver.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra banner hiển thị đúng
            self.assertTrue(cookie_banner.is_displayed(), "Cookie banner không hiển thị với các plugin khác")
            
            # Kiểm tra JavaScript của plugin đã tải
            js_loaded = self.driver.execute_script("return typeof physCookieConsent !== 'undefined'")
            self.assertTrue(js_loaded, "JavaScript của plugin không tải với các plugin khác")
            
            # Nhấn nút chấp nhận
            accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
            accept_button.click()
            
            # Đợi cho banner biến mất
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra cookie đã được lưu
            cookies = self.driver.get_cookies()
            consent_cookie_exists = any(cookie['name'] == 'physcookie-consent' for cookie in cookies)
            self.assertTrue(consent_cookie_exists, "Cookie đồng ý không được lưu với các plugin khác")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra với các plugin khác: {str(e)}")
    
    def test_4_3_browser_compatibility(self):
        """Kịch bản 4.3: Kiểm tra trên các trình duyệt."""
        # Danh sách các trình duyệt cần kiểm tra
        browsers = ['chrome', 'firefox', 'edge']
        
        for browser_name in browsers:
            try:
                # Khởi tạo trình duyệt tương ứng
                if browser_name == 'chrome':
                    chrome_options = Options()
                    chrome_options.add_argument("--incognito")
                    chrome_options.add_argument("--window-size=1920,1080")
                    self.driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
                elif browser_name == 'firefox':
                    firefox_options = webdriver.FirefoxOptions()
                    firefox_options.add_argument("-private")
                    self.driver = webdriver.Firefox(service=FirefoxService(GeckoDriverManager().install()), options=firefox_options)
                elif browser_name == 'edge':
                    edge_options = webdriver.EdgeOptions()
                    edge_options.add_argument("--inprivate")
                    self.driver = webdriver.Edge(service=EdgeService(EdgeChromiumDriverManager().install()), options=edge_options)
                
                self.driver.implicitly_wait(10)
                
                # Truy cập trang web
                self.driver.get(self.BASE_URL)
                
                # Đợi cho banner hiển thị
                cookie_banner = WebDriverWait(self.driver, 10).until(
                    EC.visibility_of_element_located((By.ID, "cookie-banner"))
                )
                
                # Kiểm tra banner hiển thị đúng
                self.assertTrue(cookie_banner.is_displayed(), f"Cookie banner không hiển thị trên {browser_name}")
                
                # Kiểm tra các nút trên banner
                accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
                self.assertTrue(accept_button.is_displayed(), f"Nút 'Chấp nhận tất cả' không hiển thị trên {browser_name}")
                
                # Nhấn nút chấp nhận
                accept_button.click()
                
                # Đợi cho banner biến mất
                WebDriverWait(self.driver, 10).until(
                    EC.invisibility_of_element_located((By.ID, "cookie-banner"))
                )
                
                # Kiểm tra cookie đã được lưu
                cookies = self.driver.get_cookies()
                consent_cookie_exists = any(cookie['name'] == 'physcookie-consent' for cookie in cookies)
                self.assertTrue(consent_cookie_exists, f"Cookie đồng ý không được lưu trên {browser_name}")
                
                # Đóng trình duyệt sau mỗi lần kiểm tra
                self.driver.quit()
                
            except Exception as e:
                self.fail(f"Lỗi khi kiểm tra trên trình duyệt {browser_name}: {str(e)}")
                if hasattr(self, 'driver') and self.driver:
                    self.driver.quit()

if __name__ == '__main__':
    unittest.main()
