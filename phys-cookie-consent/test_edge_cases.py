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

class TestEdgeCases(unittest.TestCase):
    """Test cases for edge cases and special scenarios."""
    
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
    
    def test_8_1_javascript_disabled(self):
        """Kịch bản 8.1: Kiểm tra khi JavaScript bị tắt."""
        # Lưu ý: Selenium không hỗ trợ tắt JavaScript trực tiếp
        # Chúng ta có thể sử dụng HtmlUnit hoặc PhantomJS, nhưng đây là một giải pháp thay thế
        
        # Thiết lập Chrome với chế độ ẩn danh và tắt JavaScript
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_experimental_option("prefs", {"profile.managed_default_content_settings.javascript": 2})
        
        # Khởi tạo WebDriver
        driver_no_js = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        
        try:
            # Truy cập trang web
            driver_no_js.get(self.BASE_URL)
            
            # Đợi cho trang tải xong
            WebDriverWait(driver_no_js, 10).until(
                EC.presence_of_element_located((By.TAG_NAME, "body"))
            )
            
            # Kiểm tra nội dung trang
            body_text = driver_no_js.find_element(By.TAG_NAME, "body").text
            
            # Kiểm tra không có lỗi JavaScript
            self.assertNotIn("javascript error", body_text.lower(), "Trang hiển thị lỗi JavaScript")
            self.assertNotIn("undefined", body_text.lower(), "Trang hiển thị lỗi undefined")
            
            # Kiểm tra trang vẫn hoạt động
            self.assertNotEqual(body_text.strip(), "", "Trang trống khi JavaScript bị tắt")
            
            # Kiểm tra thông báo noscript nếu có
            try:
                noscript_content = driver_no_js.find_element(By.TAG_NAME, "noscript").text
                self.assertNotEqual(noscript_content.strip(), "", "Thông báo noscript trống")
            except NoSuchElementException:
                # Nếu không có thẻ noscript, bỏ qua
                pass
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra với JavaScript bị tắt: {str(e)}")
        finally:
            # Đóng trình duyệt
            if 'driver_no_js' in locals() and driver_no_js:
                driver_no_js.quit()
    
    def test_8_2_geo_restrictions(self):
        """Kịch bản 8.2: Kiểm tra hạn chế địa lý."""
        # Lưu ý: Test này giả định rằng plugin có tính năng hạn chế địa lý
        # và có thể được cấu hình thông qua cài đặt
        
        # Đăng nhập vào WordPress admin
        self.driver.get(f"{self.BASE_URL}/wp-login.php")
        
        try:
            username_field = self.driver.find_element(By.ID, "user_login")
            password_field = self.driver.find_element(By.ID, "user_pass")
            submit_button = self.driver.find_element(By.ID, "wp-submit")
            
            username_field.send_keys("admin")  # Thay đổi thành tên người dùng của bạn
            password_field.send_keys("password")  # Thay đổi thành mật khẩu của bạn
            submit_button.click()
            
            # Truy cập trang cài đặt cookie
            self.driver.get(f"{self.BASE_URL}/wp-admin/admin.php?page=phys-cookie-consent")
            
            # Đợi cho trang tải xong
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "cookie-consent-settings"))
            )
            
            # Kiểm tra xem có cài đặt hạn chế địa lý không
            page_source = self.driver.page_source.lower()
            geo_setting_exists = "geo" in page_source or "country" in page_source or "region" in page_source
            
            if geo_setting_exists:
                # Nếu có cài đặt hạn chế địa lý, tiến hành kiểm tra
                
                # Tìm trường nhập liệu cho danh sách quốc gia
                try:
                    country_field = self.driver.find_element(By.CSS_SELECTOR, "input[name*='country'], select[name*='country']")
                    
                    # Nếu là select box
                    if country_field.tag_name == "select":
                        from selenium.webdriver.support.ui import Select
                        select = Select(country_field)
                        select.select_by_value("US")  # Chọn Hoa Kỳ
                    else:
                        # Nếu là input
                        country_field.clear()
                        country_field.send_keys("US")  # Nhập Hoa Kỳ
                    
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
                    
                    # Mô phỏng IP từ Hoa Kỳ (thông qua JavaScript)
                    # Lưu ý: Đây chỉ là mô phỏng, trong thực tế bạn cần sử dụng VPN hoặc proxy
                    self.driver.get(self.BASE_URL)
                    
                    # Mô phỏng IP từ Hoa Kỳ
                    self.driver.execute_script("""
                        window.navigator = Object.defineProperties(
                            window.navigator,
                            {
                                'language': {
                                    value: 'en-US'
                                }
                            }
                        );
                    """)
                    
                    # Làm mới trang
                    self.driver.refresh()
                    
                    # Đợi cho banner hiển thị
                    try:
                        cookie_banner = WebDriverWait(self.driver, 10).until(
                            EC.visibility_of_element_located((By.ID, "cookie-banner"))
                        )
                        
                        # Kiểm tra banner hiển thị cho IP Hoa Kỳ
                        self.assertTrue(cookie_banner.is_displayed(), "Banner không hiển thị cho IP Hoa Kỳ")
                        
                    except TimeoutException:
                        self.fail("Banner không hiển thị cho IP Hoa Kỳ")
                    
                    # Mô phỏng IP từ quốc gia khác (ví dụ: Việt Nam)
                    self.driver.execute_script("""
                        window.navigator = Object.defineProperties(
                            window.navigator,
                            {
                                'language': {
                                    value: 'vi-VN'
                                }
                            }
                        );
                    """)
                    
                    # Xóa cookie và làm mới trang
                    self.driver.delete_all_cookies()
                    self.driver.refresh()
                    
                    # Kiểm tra banner có hiển thị cho IP Việt Nam không
                    # Lưu ý: Kết quả phụ thuộc vào cấu hình của plugin
                    try:
                        cookie_banner = WebDriverWait(self.driver, 5).until(
                            EC.visibility_of_element_located((By.ID, "cookie-banner"))
                        )
                        
                        # Nếu banner hiển thị, ghi nhận kết quả
                        banner_displayed_for_vn = cookie_banner.is_displayed()
                    except TimeoutException:
                        # Nếu banner không hiển thị, ghi nhận kết quả
                        banner_displayed_for_vn = False
                    
                    # Ghi nhận kết quả mà không đánh giá đúng/sai
                    # vì phụ thuộc vào cấu hình của plugin
                    print(f"\nBanner hiển thị cho IP Việt Nam: {banner_displayed_for_vn}")
                    print("Lưu ý: Kết quả này phụ thuộc vào cấu hình hạn chế địa lý của plugin")
                    
                except NoSuchElementException:
                    self.skipTest("Không tìm thấy trường cài đặt quốc gia")
            else:
                self.skipTest("Plugin không có tính năng hạn chế địa lý")
                
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra hạn chế địa lý: {str(e)}")
    
    def test_8_3_cookie_expiration(self):
        """Kịch bản 8.3: Kiểm tra hết hạn cookie."""
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
            
            # Kiểm tra thời gian hết hạn
            self.assertIn('expiry', consent_cookie, "Cookie không có thời gian hết hạn")
            
            # Mô phỏng hết hạn cookie bằng cách đặt thời gian hết hạn trong quá khứ
            expired_cookie = {
                'name': consent_cookie['name'],
                'value': consent_cookie['value'],
                'path': '/',
                'expiry': int(time.time()) - 3600  # Hết hạn 1 giờ trước
            }
            
            # Xóa cookie hiện tại
            self.driver.delete_cookie(consent_cookie['name'])
            
            # Thêm cookie đã hết hạn
            self.driver.add_cookie(expired_cookie)
            
            # Làm mới trang
            self.driver.refresh()
            
            # Đợi cho banner hiển thị lại
            try:
                cookie_banner = WebDriverWait(self.driver, 10).until(
                    EC.visibility_of_element_located((By.ID, "cookie-banner"))
                )
                
                # Kiểm tra banner hiển thị lại sau khi cookie hết hạn
                self.assertTrue(cookie_banner.is_displayed(), "Banner không hiển thị lại sau khi cookie hết hạn")
                
            except TimeoutException:
                self.fail("Banner không hiển thị lại sau khi cookie hết hạn")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra hết hạn cookie: {str(e)}")
    
    def test_8_4_multiple_tabs(self):
        """Kịch bản 8.4: Kiểm tra đồng bộ giữa nhiều tab."""
        # Truy cập trang web
        self.driver.get(self.BASE_URL)
        
        try:
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Mở tab mới
            self.driver.execute_script("window.open('');")
            
            # Chuyển đến tab mới
            self.driver.switch_to.window(self.driver.window_handles[1])
            
            # Truy cập trang web trong tab mới
            self.driver.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị trong tab mới
            cookie_banner_tab2 = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Nhấn nút chấp nhận trong tab mới
            accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
            accept_button.click()
            
            # Đợi cho banner biến mất trong tab mới
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Chuyển về tab đầu tiên
            self.driver.switch_to.window(self.driver.window_handles[0])
            
            # Làm mới trang trong tab đầu tiên
            self.driver.refresh()
            
            # Kiểm tra banner không hiển thị trong tab đầu tiên
            try:
                WebDriverWait(self.driver, 5).until(
                    EC.visibility_of_element_located((By.ID, "cookie-banner"))
                )
                self.fail("Banner vẫn hiển thị trong tab đầu tiên sau khi đã chấp nhận trong tab khác")
            except TimeoutException:
                # Nếu không tìm thấy banner, test thành công
                pass
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra đồng bộ giữa nhiều tab: {str(e)}")

if __name__ == '__main__':
    unittest.main()
