document.querySelectorAll('.tabs button').forEach(button => {
    button.addEventListener('click', () => {
        button.parentElement.querySelectorAll('button').forEach(item => item.classList.remove('active'));
        button.classList.add('active');
    });
});

document.querySelectorAll('.role-tabs label').forEach(label => {
    label.addEventListener('click', () => {
        document.querySelectorAll('.role-tabs label').forEach(item => item.classList.remove('active'));
        label.classList.add('active');
    });
});

document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.setAttribute('aria-label', visible ? 'Hiển thị mật khẩu' : 'Ẩn mật khẩu');
    });
});
