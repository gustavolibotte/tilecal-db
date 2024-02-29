current_timestamp=$(date '+%Y_%m_%d_%H_%M_%S')
# report_dir="reports"
# if [ ! -d "$report_dir" ]; then
#     echo "Creating ${report_dir}"
#     mkdir -p $report_dir
# fi
html_report_path="${current_timestamp}_dev_test_report.html"
json_report_path="${current_timestamp}_dev_test_report.json"
locust \
-H "https://tilecal-db-dev.app.cern.ch/" \
-t "10m" \
-r 1 \
-u 15 \
-f "api_load_test.py" \
--html "${report_path}" \
--headless \
--json > "${json_report_path}"
echo "Finished. Report at ${report_path}"