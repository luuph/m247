Khi push code lên cần đổi tên .git
find app/code -type d -name ".git" -exec mv {} {}_bak \;

Sau khi pull code hoặc chuyển branch cần đổi lại git (Nếu cần sử dụng git cuả module con)
find app/code -type d -name ".git_bak" -exec sh -c 'mv "$0" "${0%_bak}"' {} \;