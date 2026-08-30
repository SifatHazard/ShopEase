document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (el) {
        const msg = el.getAttribute('data-confirm') || 'Are you sure?';
        if (!confirm(msg)) e.preventDefault();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity .5s';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 3500);
    }
});

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('qty-input')) {
        const row = e.target.closest('tr');
        if (!row) return;
        const price = parseFloat(row.dataset.price);
        const qty = parseInt(e.target.value) || 1;
        const totalCell = row.querySelector('.line-total');
        if (totalCell) {
            totalCell.textContent = 'Tk ' + (price * qty).toFixed(2);
        }
    }
});

document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.id === 'registerForm') {
        const pass = form.querySelector('#password');
        const confirm = form.querySelector('#confirm_password');
        if (pass && confirm && pass.value !== confirm.value) {
            e.preventDefault();
            alert('Passwords do not match.');
        }
    }
});
