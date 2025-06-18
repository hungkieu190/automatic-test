#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import unittest
import time
import requests
import json
import re
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

class TestSecurity(unittest.TestCase):
    """Test cases for security aspects of cookie consent plugin."""
    
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
    
    def test_6_1_csrf_protection(self):
        """Kịch bản 6.1: Kiểm tra bảo vệ CSRF."""
        # Đăng nhập vào WordPress admin
        self.driver.get(f"{self.BASE_URL}/wp-login.php")
        
        username_field = self.driver.find_element(By.ID, "user_login")
        password_field = self.driver.find_element(By.ID, "user_pass")
        submit_button = self.driver.find_element(By.ID, "wp-submit")
        
        username_field.send_keys("admin")  # Thay đổi thành tên người dùng của bạn
        password_field.send_keys("!M2y4n0d2s96")  # Thay đổi thành mật khẩu của bạn
        submit_button.click()
        
        # Truy cập trang cài đặt cookie
        self.driver.get(f"{self.BASE_URL}/wp-admin/admin.php?page=phys-cookie-consent")
        
        try:
            # Đợi cho trang tải xong
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "cookie-consent-settings"))
            )
            
            # Lấy nonce từ form
            page_source = self.driver.page_source
            nonce_match = re.search(r'name="cookie_consent_nonce" value="([^"]+)"', page_source)
            
            if nonce_match:
                nonce = nonce_match.group(1)
                
                # Kiểm tra nonce có tồn tại
                self.assertIsNotNone(nonce, "Nonce không tồn tại trong form")
                self.assertTrue(len(nonce) > 0, "Nonce không hợp lệ")
                
                # Gửi yêu cầu AJAX với nonce hợp lệ
                ajax_url = f"{self.BASE_URL}/wp-admin/admin-ajax.php"
                
                # Chuẩn bị dữ liệu POST
                data = {
                    'action': 'phys_save_cookie_consent_settings',
                    'cookie_consent_nonce': nonce,
                    'cookie_consent_enabled': 'yes'
                }
                
                # Lấy cookie từ trình duyệt
                cookies = {}
                for cookie in self.driver.get_cookies():
                    cookies[cookie['name']] = cookie['value']
                
                # Gửi yêu cầu POST với nonce hợp lệ
                response = requests.post(ajax_url, data=data, cookies=cookies)
                
                # Kiểm tra phản hồi thành công
                self.assertEqual(response.status_code, 200, "Yêu cầu với nonce hợp lệ không thành công")
                
                # Gửi yêu cầu AJAX với nonce không hợp lệ
                data['cookie_consent_nonce'] = 'invalid_nonce'
                
                # Gửi yêu cầu POST với nonce không hợp lệ
                response = requests.post(ajax_url, data=data, cookies=cookies)
                
                # Kiểm tra phản hồi lỗi
                self.assertEqual(response.status_code, 200, "Yêu cầu với nonce không hợp lệ không trả về phản hồi")
                
                # Kiểm tra nội dung phản hồi chứa thông báo lỗi
                response_json = response.json()
                self.assertFalse(response_json.get('success', True), "Yêu cầu với nonce không hợp lệ không bị từ chối")
                self.assertIn('message', response_json, "Phản hồi không chứa thông báo lỗi")
                self.assertIn('nonce', response_json.get('message', '').lower(), "Thông báo lỗi không đề cập đến nonce")
                
            else:
                self.fail("Không tìm thấy nonce trong form")
                
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra bảo vệ CSRF: {str(e)}")
    
    def test_6_2_xss_protection(self):
        """Kịch bản 6.2: Kiểm tra bảo vệ XSS."""
        # Đăng nhập vào WordPress admin
        self.driver.get(f"{self.BASE_URL}/wp-login.php")
        
        username_field = self.driver.find_element(By.ID, "user_login")
        password_field = self.driver.find_element(By.ID, "user_pass")
        submit_button = self.driver.find_element(By.ID, "wp-submit")
        
        username_field.send_keys("admin")  # Thay đổi thành tên người dùng của bạn
        password_field.send_keys("!M2y4n0d2s96")  # Thay đổi thành mật khẩu của bạn
        submit_button.click()
        
        # Truy cập trang cài đặt cookie
        self.driver.get(f"{self.BASE_URL}/wp-admin/admin.php?page=phys-cookie-consent")
        
        try:
            # Đợi cho trang tải xong
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "cookie-consent-settings"))
            )
            
            # Tìm trường nhập liệu cho tiêu đề banner
            banner_title_field = self.driver.find_element(By.NAME, "cookie_banner_title")
            
            # Xóa nội dung hiện tại
            banner_title_field.clear()
            
            # Nhập payload XSS
            xss_payload = '<script>alert("XSS")</script>'
            banner_title_field.send_keys(xss_payload)
            
            # Lưu cài đặt
            save_button = self.driver.find_element(By.ID, "save-cookie-settings")
            save_button.click()
            
            # Đợi cho trang tải lại
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "cookie-consent-settings"))
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
            
            # Lấy HTML của banner
            banner_html = cookie_banner.get_attribute('innerHTML')
            
            # Kiểm tra payload XSS đã bị lọc
            self.assertNotIn('<script>', banner_html, "XSS payload không bị lọc")
            self.assertNotIn('alert("XSS")', banner_html, "XSS payload không bị lọc")
            
            # Kiểm tra không có script được thực thi
            alert_present = self.driver.execute_script("""
                return window.alert !== undefined && window.alert.toString().indexOf('native code') === -1;
            """)
            self.assertFalse(alert_present, "XSS payload đã được thực thi")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra bảo vệ XSS: {str(e)}")
    
    def test_6_3_cookie_security(self):
        """Kịch bản 6.3: Kiểm tra bảo mật cookie."""
        # Truy cập trang web
        self.driver.get(self.BASE_URL)
        
        try:
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Nhấn nút chấp nhận
            accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
            accept_button.click()
            
            # Đợi cho banner biến mất
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Lấy cookie đồng ý
            cookies = self.driver.get_cookies()
            consent_cookie = next((cookie for cookie in cookies if cookie['name'] == 'physcookie-consent'), None)
            
            # Kiểm tra cookie tồn tại
            self.assertIsNotNone(consent_cookie, "Cookie đồng ý không tồn tại")
            
            # Kiểm tra thuộc tính bảo mật của cookie
            self.assertTrue(consent_cookie.get('secure', False) or 'localhost' in self.BASE_URL, 
                          "Cookie không được đặt là secure (trừ khi sử dụng localhost)")
            
            self.assertTrue(consent_cookie.get('httpOnly', False), 
                          "Cookie không được đặt là httpOnly")
            
            # Kiểm tra SameSite
            same_site = consent_cookie.get('sameSite', '')
            self.assertIn(same_site, ['Strict', 'Lax'], 
                        f"Cookie không được đặt SameSite là Strict hoặc Lax: {same_site}")
            
            # Kiểm tra thời gian hết hạn
            self.assertIn('expiry', consent_cookie, "Cookie không có thời gian hết hạn")
            
            # Kiểm tra thời gian hết hạn không quá 1 năm
            current_time = int(time.time())
            max_expiry = current_time + 365 * 24 * 60 * 60  # 1 năm
            self.assertLessEqual(consent_cookie['expiry'], max_expiry, 
                               "Cookie có thời gian hết hạn quá 1 năm")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra bảo mật cookie: {str(e)}")
    
    def test_6_4_unauthorized_access(self):
        """Kịch bản 6.4: Kiểm tra truy cập trái phép."""
        # Kiểm tra truy cập vào trang admin khi chưa đăng nhập
        self.driver.get(f"{self.BASE_URL}/wp-admin/admin.php?page=phys-cookie-consent")
        
        # Kiểm tra chuyển hướng đến trang đăng nhập
        current_url = self.driver.current_url
        self.assertIn("wp-login.php", current_url, 
                    "Không chuyển hướng đến trang đăng nhập khi truy cập trang admin")
        
        # Kiểm tra gọi AJAX khi chưa đăng nhập
        ajax_url = f"{self.BASE_URL}/wp-admin/admin-ajax.php"
        
        # Chuẩn bị dữ liệu POST
        data = {
            'action': 'phys_save_cookie_consent_settings',
            'cookie_consent_enabled': 'yes'
        }
        
        # Gửi yêu cầu POST
        response = requests.post(ajax_url, data=data)
        
        # Kiểm tra phản hồi
        self.assertEqual(response.status_code, 200, "Yêu cầu AJAX không trả về phản hồi")
        
        # Kiểm tra nội dung phản hồi
        try:
            response_json = response.json()
            self.assertFalse(response_json.get('success', True), 
                           "Yêu cầu AJAX không bị từ chối khi chưa đăng nhập")
        except json.JSONDecodeError:
            # Nếu phản hồi không phải JSON, kiểm tra có chứa thông báo lỗi
            self.assertIn('error', response.text.lower(), 
                        "Phản hồi không chứa thông báo lỗi khi chưa đăng nhập")

if __name__ == '__main__':
    unittest.main()
