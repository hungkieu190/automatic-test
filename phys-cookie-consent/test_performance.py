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
from webdriver_manager.chrome import ChromeDriverManager

class TestPerformance(unittest.TestCase):
    """Test cases for performance impact of cookie consent plugin."""
    
    BASE_URL = "https://demowp.online/"  # Thay đổi URL này theo cấu hình của bạn
    
    def setUp(self):
        """Set up test environment before each test."""
        # Thiết lập Chrome với chế độ ẩn danh
        chrome_options = Options()
        chrome_options.add_argument("--incognito")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--headless")  # Chạy ở chế độ headless để giảm tải
        
        # Khởi tạo WebDriver
        self.driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
        self.driver.implicitly_wait(10)
        
        # Xóa tất cả cookie trước mỗi test
        self.driver.delete_all_cookies()
    
    def tearDown(self):
        """Clean up after each test."""
        if self.driver:
            self.driver.quit()
    
    def test_5_1_page_load_time(self):
        """Kịch bản 5.1: Kiểm tra thời gian tải trang."""
        # Đo thời gian tải trang với plugin bật
        start_time = time.time()
        self.driver.get(self.BASE_URL)
        
        # Đợi cho trang tải xong
        WebDriverWait(self.driver, 30).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        
        # Đợi cho banner hiển thị
        WebDriverWait(self.driver, 10).until(
            EC.visibility_of_element_located((By.ID, "cookie-banner"))
        )
        
        # Tính thời gian tải trang
        load_time_with_plugin = time.time() - start_time
        
        # Lưu trữ tài nguyên được tải
        resources_with_plugin = self.driver.execute_script("""
            var resources = window.performance.getEntriesByType('resource');
            return resources.length;
        """)
        
        # Xóa cookie và tắt plugin (giả định)
        # Trong thực tế, bạn cần tắt plugin trong WordPress admin
        # Đây chỉ là mô phỏng bằng cách chặn tài nguyên của plugin
        self.driver.execute_script("""
            // Chặn tài nguyên của plugin
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (url.includes('cookie-consent.js') || url.includes('modal.css')) {
                    return Promise.reject('Resource blocked for testing');
                }
                return originalFetch(url, options);
            };
            
            var originalXHR = window.XMLHttpRequest.prototype.open;
            window.XMLHttpRequest.prototype.open = function(method, url) {
                if (url.includes('cookie-consent.js') || url.includes('modal.css')) {
                    throw new Error('Resource blocked for testing');
                }
                return originalXHR.apply(this, arguments);
            };
        """)
        
        # Xóa tất cả cookie
        self.driver.delete_all_cookies()
        
        # Đo thời gian tải trang không có plugin
        start_time = time.time()
        self.driver.get(self.BASE_URL)
        
        # Đợi cho trang tải xong
        WebDriverWait(self.driver, 30).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        
        # Tính thời gian tải trang
        load_time_without_plugin = time.time() - start_time
        
        # Lưu trữ tài nguyên được tải
        resources_without_plugin = self.driver.execute_script("""
            var resources = window.performance.getEntriesByType('resource');
            return resources.length;
        """)
        
        # So sánh thời gian tải trang
        time_difference = load_time_with_plugin - load_time_without_plugin
        
        # Kiểm tra thời gian tải trang không tăng quá 20%
        self.assertLessEqual(time_difference / load_time_without_plugin, 0.2, 
                           f"Plugin làm tăng thời gian tải trang quá nhiều: {time_difference:.2f}s ({(time_difference / load_time_without_plugin * 100):.2f}%)")
        
        # Kiểm tra số lượng tài nguyên được tải không tăng quá nhiều
        resource_difference = resources_with_plugin - resources_without_plugin
        self.assertLessEqual(resource_difference, 5, 
                           f"Plugin tải quá nhiều tài nguyên: {resource_difference} tài nguyên bổ sung")
        
        # In thông tin hiệu suất
        print(f"\nThời gian tải trang với plugin: {load_time_with_plugin:.2f}s")
        print(f"Thời gian tải trang không có plugin: {load_time_without_plugin:.2f}s")
        print(f"Chênh lệch: {time_difference:.2f}s ({(time_difference / load_time_without_plugin * 100):.2f}%)")
        print(f"Số tài nguyên với plugin: {resources_with_plugin}")
        print(f"Số tài nguyên không có plugin: {resources_without_plugin}")
        print(f"Chênh lệch tài nguyên: {resource_difference}")
    
    def test_5_2_high_traffic_simulation(self):
        """Kịch bản 5.2: Kiểm tra với lưu lượng truy cập cao."""
        # Lưu ý: Test này giả định rằng bạn có quyền truy cập vào máy chủ
        # và có thể thực hiện các yêu cầu đồng thời. Trong thực tế, bạn cần
        # sử dụng công cụ như JMeter hoặc Locust để mô phỏng lưu lượng truy cập cao.
        
        # Số lượng yêu cầu đồng thời
        num_requests = 10
        
        # Tạo danh sách các URL cần kiểm tra
        urls = [
            f"{self.BASE_URL}",  # Trang chủ
            f"{self.BASE_URL}/wp-admin/admin-ajax.php?action=cookie_consent_settings",  # AJAX endpoint
        ]
        
        for url in urls:
            # Tạo danh sách thời gian phản hồi
            response_times = []
            
            # Thực hiện các yêu cầu đồng thời
            for i in range(num_requests):
                start_time = time.time()
                
                try:
                    # Thực hiện yêu cầu
                    response = requests.get(url, timeout=10)
                    
                    # Tính thời gian phản hồi
                    response_time = time.time() - start_time
                    response_times.append(response_time)
                    
                    # Kiểm tra mã trạng thái
                    self.assertIn(response.status_code, [200, 302, 304], 
                                f"Yêu cầu {i+1} đến {url} trả về mã lỗi: {response.status_code}")
                    
                except requests.exceptions.RequestException as e:
                    self.fail(f"Yêu cầu {i+1} đến {url} gặp lỗi: {str(e)}")
            
            # Tính thời gian phản hồi trung bình
            avg_response_time = sum(response_times) / len(response_times)
            
            # Kiểm tra thời gian phản hồi trung bình không quá 2 giây
            self.assertLessEqual(avg_response_time, 2.0, 
                               f"Thời gian phản hồi trung bình quá cao: {avg_response_time:.2f}s")
            
            # In thông tin hiệu suất
            print(f"\nURL: {url}")
            print(f"Số lượng yêu cầu: {num_requests}")
            print(f"Thời gian phản hồi trung bình: {avg_response_time:.2f}s")
            print(f"Thời gian phản hồi tối đa: {max(response_times):.2f}s")
            print(f"Thời gian phản hồi tối thiểu: {min(response_times):.2f}s")

if __name__ == '__main__':
    unittest.main()
