#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import unittest
import os
import sys
import time
import argparse
from datetime import datetime
import HtmlTestRunner

# Monkey patch to fix the missing _count_relevant_tb_levels method
# Sửa: thêm self vào tham số đầu tiên
def _count_relevant_tb_levels(self, tb):
    length = 0
    while tb:
        length += 1
        tb = tb.tb_next
    return length

# Add the missing method to the HtmlTestResult class
HtmlTestRunner.result.HtmlTestResult._count_relevant_tb_levels = _count_relevant_tb_levels

# Thêm thư mục hiện tại vào đường dẫn để import các module test
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

# Import các module test
from test_display import TestCookieConsentDisplay
from test_consent_functionality import TestCookieConsentFunctionality
from test_cookie_blocking import TestCookieBlocking
from test_compatibility import TestCompatibility
from test_performance import TestPerformance
from test_security import TestSecurity
from test_multilingual import TestMultilingual
from test_edge_cases import TestEdgeCases

def run_tests(test_categories=None, output_dir=None, verbose=False):
    """
    Chạy các test case theo danh mục được chỉ định.
    
    Args:
        test_categories: Danh sách các danh mục test cần chạy. Nếu None, chạy tất cả.
        output_dir: Thư mục đầu ra cho báo cáo HTML. Nếu None, sử dụng thư mục mặc định.
        verbose: Nếu True, hiển thị thông tin chi tiết trong quá trình chạy test.
    """
    # Tạo thư mục báo cáo nếu chưa tồn tại
    if output_dir is None:
        output_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'reports')
    
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    # Tạo tên file báo cáo với timestamp
    timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    report_file = f'cookie_consent_test_report_{timestamp}'
    
    # Danh sách tất cả các test case
    all_test_cases = {
        'display': TestCookieConsentDisplay,
        'functionality': TestCookieConsentFunctionality,
        'blocking': TestCookieBlocking,
        'compatibility': TestCompatibility,
        'performance': TestPerformance,
        'security': TestSecurity,
        'multilingual': TestMultilingual,
        'edge_cases': TestEdgeCases
    }
    
    # Nếu không chỉ định danh mục, chạy tất cả
    if test_categories is None or 'all' in test_categories:
        test_categories = all_test_cases.keys()
    
    # Tạo test suite
    test_suite = unittest.TestSuite()
    
    # Thêm các test case vào suite
    for category in test_categories:
        if category in all_test_cases:
            test_class = all_test_cases[category]
            tests = unittest.defaultTestLoader.loadTestsFromTestCase(test_class)
            test_suite.addTests(tests)
            if verbose:
                print(f"Đã thêm {tests.countTestCases()} test case từ danh mục {category}")
        else:
            print(f"Cảnh báo: Danh mục test '{category}' không tồn tại")
    
    # Chạy test với HtmlTestRunner
    runner = HtmlTestRunner.HTMLTestRunner(
        output=output_dir,
        report_name=report_file,
        combine_reports=True,
        report_title="Báo cáo kiểm thử Cookie Consent Plugin"
    )
    
    print(f"\nBắt đầu chạy {test_suite.countTestCases()} test case...")
    start_time = time.time()
    
    # Chạy test
    result = runner.run(test_suite)
    
    # Hiển thị tóm tắt kết quả
    duration = time.time() - start_time
    print(f"\nĐã hoàn thành {result.testsRun} test case trong {duration:.2f} giây")
    print(f"Thành công: {result.testsRun - len(result.failures) - len(result.errors)}")
    print(f"Thất bại: {len(result.failures)}")
    print(f"Lỗi: {len(result.errors)}")
    
    # Hiển thị đường dẫn đến báo cáo
    report_path = os.path.join(output_dir, f"{report_file}.html")
    print(f"\nBáo cáo chi tiết: {os.path.abspath(report_path)}")
    
    return result

if __name__ == '__main__':
    # Phân tích tham số dòng lệnh
    parser = argparse.ArgumentParser(description='Chạy kiểm thử tự động cho Cookie Consent Plugin')
    
    parser.add_argument('--categories', '-c', nargs='+', default=['all'],
                        help='Danh mục test cần chạy (display, functionality, blocking, compatibility, performance, security, multilingual, edge_cases hoặc all)')
    
    parser.add_argument('--output', '-o', default=None,
                        help='Thư mục đầu ra cho báo cáo HTML')
    
    parser.add_argument('--verbose', '-v', action='store_true',
                        help='Hiển thị thông tin chi tiết trong quá trình chạy test')
    
    args = parser.parse_args()
    
    # Chạy test
    run_tests(args.categories, args.output, args.verbose)

# Các lệnh chạy test:
# Chạy tất cả các test:
# python run_all_tests.py
#
# Chạy test theo danh mục cụ thể:
# python run_all_tests.py --categories display functionality
# python run_all_tests.py -c display functionality
#
# Chạy test với output directory tùy chỉnh:
# python run_all_tests.py --output ./custom_reports
# python run_all_tests.py -o ./custom_reports
#
# Chạy test với chế độ verbose (hiển thị chi tiết):
# python run_all_tests.py --verbose
# python run_all_tests.py -v
#
# Kết hợp các tùy chọn:
# python run_all_tests.py -c display functionality -o ./custom_reports -v
#
# Danh sách các danh mục test có thể chạy:
# - display: Kiểm tra hiển thị của cookie consent
# - functionality: Kiểm tra chức năng của cookie consent
# - blocking: Kiểm tra chức năng chặn cookie
# - compatibility: Kiểm tra tính tương thích
# - performance: Kiểm tra hiệu suất
# - security: Kiểm tra bảo mật
# - multilingual: Kiểm tra đa ngôn ngữ
# - edge_cases: Kiểm tra các trường hợp đặc biệt
# - all: Chạy tất cả các test
