/**
 * Dormitory Management - Main JavaScript
 * KTX Đại học Quốc gia TP.HCM
 */

/**
 * Trình ẩn / hiện mật khẩu (Toggle Show/Hide Password)
 * @param {string} inputId - ID của ô input password
 * @param {HTMLElement} btn - Nút button kích hoạt sự kiện
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');

    // Tìm icon bên trong button
    const eyeIcon = btn.querySelector('.eye-icon') || btn;
    if (isPassword) {
        btn.setAttribute('aria-label', 'Ẩn mật khẩu');
        btn.setAttribute('title', 'Ẩn mật khẩu');
        btn.style.color = 'var(--primary, #1e40af)';
        eyeIcon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    } else {
        btn.setAttribute('aria-label', 'Hiện mật khẩu');
        btn.setAttribute('title', 'Hiện mật khẩu');
        btn.style.color = 'var(--text-muted, #64748b)';
        eyeIcon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }
}

