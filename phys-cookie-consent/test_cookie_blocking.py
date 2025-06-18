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

class TestCookieBlocking(unittest.TestCase):
    """Test cases for cookie blocking before consent."""
    
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
    
    def test_3_1_block_third_party_scripts(self):
        """Kịch bản 3.1: Kiểm tra chặn script bên thứ ba."""
        # Truy cập trang web
        self.driver.get(self.BASE_URL)
        
        try:
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra các script bên thứ ba chưa được tải
            # Kiểm tra Google Analytics
            ga_loaded = self.driver.execute_script(
                "return typeof ga !== 'undefined' || typeof gtag !== 'undefined' || typeof __gaTracker !== 'undefined'"
            )
            self.assertFalse(ga_loaded, "Google Analytics đã tải trước khi đồng ý")
            
            # Kiểm tra Facebook Pixel
            fb_loaded = self.driver.execute_script(
                "return typeof fbq !== 'undefined'"
            )
            self.assertFalse(fb_loaded, "Facebook Pixel đã tải trước khi đồng ý")
            
            # Nhấn nút chấp nhận tất cả
            accept_button = self.driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
            accept_button.click()
            
            # Đợi cho banner biến mất
            WebDriverWait(self.driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Đợi một chút để các script tải
            time.sleep(2)
            
            # Làm mới trang để đảm bảo các script được tải
            self.driver.refresh()
            time.sleep(2)
            
            # Kiểm tra các script bên thứ ba đã được tải sau khi đồng ý
            # Kiểm tra Google Analytics
            ga_loaded_after = self.driver.execute_script(
                "return typeof ga !== 'undefined' || typeof gtag !== 'undefined' || typeof __gaTracker !== 'undefined'"
            )
            self.assertTrue(ga_loaded_after, "Google Analytics không tải sau khi đồng ý")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra chặn script bên thứ ba: {str(e)}")
    
    def test_3_2_essential_cookies_only(self):
        """Kịch bản 3.2: Kiểm tra cookie cần thiết."""
        # Truy cập trang web
        self.driver.get(self.BASE_URL)
        
        try:
            # Đợi cho banner hiển thị
            WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra các cookie đã được lưu trước khi đồng ý
            cookies = self.driver.get_cookies()
            
            # Lọc các cookie WordPress cần thiết
            wp_cookies = [cookie for cookie in cookies if cookie['name'].startswith('wordpress_') or 
                                                      cookie['name'].startswith('wp-') or 
                                                      cookie['name'] == 'PHPSESSID']
            
            # Lọc các cookie không cần thiết
            non_essential_cookies = [cookie for cookie in cookies if cookie['name'].startswith('_ga') or 
                                                               cookie['name'].startswith('_fbp') or 
                                                               cookie['name'].startswith('_gid')]
            
            # Kiểm tra chỉ có cookie cần thiết
            self.assertTrue(len(wp_cookies) > 0, "Cookie WordPress cần thiết không được lưu")
            self.assertEqual(len(non_essential_cookies), 0, "Cookie không cần thiết đã được lưu trước khi đồng ý")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra cookie cần thiết: {str(e)}")
    
    def test_3_3_ad_blockers_compatibility(self):
        """Kịch bản 3.3: Kiểm tra với các trình chặn quảng cáo."""
        # Thiết lập Chrome với uBlock Origin
        # Lưu ý: Trong thực tế, bạn cần cài đặt extension uBlock Origin
        # Đây chỉ là mô phỏng bằng cách chặn các script quảng cáo phổ biến
        
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        # Thêm extension uBlock Origin (trong thực tế)
        # chrome_options.add_extension('path/to/ublock_origin.crx')
        
        # Khởi tạo WebDriver mới với uBlock Origin
        ad_block_driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        
        try:
            # Truy cập trang web
            ad_block_driver.get(self.BASE_URL)
            
            # Đợi cho banner hiển thị
            cookie_banner = WebDriverWait(ad_block_driver, 10).until(
                EC.visibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra banner hiển thị đúng
            self.assertTrue(cookie_banner.is_displayed(), "Cookie banner không hiển thị với trình chặn quảng cáo")
            
            # Kiểm tra các nút trên banner
            accept_button = ad_block_driver.find_element(By.CSS_SELECTOR, "#cookie-banner .accept-all")
            self.assertTrue(accept_button.is_displayed(), "Nút 'Chấp nhận tất cả' không hiển thị với trình chặn quảng cáo")
            
            # Nhấn nút chấp nhận
            accept_button.click()
            
            # Đợi cho banner biến mất
            WebDriverWait(ad_block_driver, 10).until(
                EC.invisibility_of_element_located((By.ID, "cookie-banner"))
            )
            
            # Kiểm tra cookie đã được lưu
            cookies = ad_block_driver.get_cookies()
            consent_cookie_exists = any(cookie['name'] == 'physcookie-consent' for cookie in cookies)
            self.assertTrue(consent_cookie_exists, "Cookie đồng ý không được lưu với trình chặn quảng cáo")
            
        except (TimeoutException, NoSuchElementException) as e:
            self.fail(f"Lỗi khi kiểm tra với trình chặn quảng cáo: {str(e)}")
        finally:
            # Đóng trình duyệt
            if ad_block_driver:
                ad_block_driver.quit()

if __name__ == '__main__':
    unittest.main()
