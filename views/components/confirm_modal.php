<?php
/**
 * Reusable Confirmation Modal Component - KTX ĐHQG-HCM
 * Component modal xác nhận dùng chung cho toàn hệ thống:
 * - Tự đóng gói CSS & JS (Self-contained), đảm bảo không bao giờ bị vỡ layout do cache CSS/JS.
 * - Hỗ trợ submit Form an toàn (kèm CSRF token) hoặc gọi Callback JavaScript linh hoạt.
 * - Tùy biến: Tiêu đề, Nội dung, Ghi chú cảnh báo nghiệp vụ, Icon, Màu sắc, Nút xác nhận.
 */
?>

<!-- MODAL MARKUP -->
<div id="reusableConfirmModal" class="modal-backdrop" style="display: none;" onclick="if(event.target === this) closeConfirmModal()">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalConfirmHeading">
        <div class="modal-header">
            <h3 class="modal-title">
                <i id="modalConfirmIcon" class="fa-solid fa-triangle-exclamation" style="color: #dc2626; margin-right: 8px;"></i>
                <span id="modalConfirmHeading">Xác nhận thao tác</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeConfirmModal()" aria-label="Đóng modal" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div id="modalConfirmMessage" style="margin-bottom: 12px; font-weight: 500; color: #1e293b; font-size: 0.95rem;">
                Bạn có chắc chắn muốn thực hiện hành động này?
            </div>
            <div id="modalConfirmNote" style="font-size: 0.825rem; color: #78350f; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 14px; line-height: 1.55; display: none;">
                <!-- Ghi chú cảnh báo nghiệp vụ -->
            </div>
        </div>

        <div class="modal-footer">
            <!-- Form ẩn để submit POST an toàn khi được truyền tham số action & inputs -->
            <form id="modalConfirmForm" method="POST" style="display: none;">
                <?= csrf_field() ?>
                <div id="modalConfirmHiddenInputs"></div>
            </form>

            <button type="button" class="btn btn-outline" onclick="closeConfirmModal()" style="border-radius: 6px; padding: 8px 18px; cursor: pointer;">
                Hủy bỏ
            </button>
            <button type="button" id="modalConfirmSubmitBtn" class="btn btn-danger" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; cursor: pointer;">
                <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> <span>Xác nhận</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL SCRIPT (GLOBAL FUNCTIONS) -->
<script>
(function() {
    let confirmModalCallback = null;

    /**
     * Mở modal xác nhận với các tùy chọn
     * @param {Object} options
     */
    window.openConfirmModal = function(options) {
        options = options || {};
        const modal = document.getElementById('reusableConfirmModal');
        if (!modal) return;

        const heading = document.getElementById('modalConfirmHeading');
        const icon = document.getElementById('modalConfirmIcon');
        const message = document.getElementById('modalConfirmMessage');
        const note = document.getElementById('modalConfirmNote');
        const submitBtn = document.getElementById('modalConfirmSubmitBtn');
        const form = document.getElementById('modalConfirmForm');
        const hiddenInputs = document.getElementById('modalConfirmHiddenInputs');

        // 1. Tiêu đề & Icon
        if (heading) heading.innerText = options.title || 'Xác nhận thao tác';
        if (icon) {
            icon.className = options.icon || 'fa-solid fa-triangle-exclamation';
            icon.style.color = options.iconColor || '#dc2626';
        }

        // 2. Nội dung thông điệp
        if (message) {
            message.innerHTML = options.message || 'Bạn có chắc chắn muốn thực hiện hành động này?';
        }

        // 3. Ghi chú cảnh báo
        if (note) {
            if (options.note) {
                note.innerHTML = options.note;
                note.style.display = 'block';
            } else {
                note.style.display = 'none';
            }
        }

        // 4. Định dạng nút bấm xác nhận
        if (submitBtn) {
            submitBtn.className = options.btnClass || 'btn btn-danger';
            submitBtn.disabled = false;
            const btnIcon = options.btnIcon !== undefined ? options.btnIcon : 'fa-solid fa-trash-can';
            const btnText = options.btnText || 'Xác nhận';
            submitBtn.innerHTML = (btnIcon ? `<i class="${btnIcon}" style="margin-right: 6px;"></i> ` : '') + `<span>${btnText}</span>`;
        }

        // 5. Cài đặt hành động (Form submit hoặc Callback)
        if (options.action && form && hiddenInputs) {
            form.action = options.action;
            form.method = options.method || 'POST';
            hiddenInputs.innerHTML = '';
            if (options.inputs) {
                for (const [key, val] of Object.entries(options.inputs)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = val;
                    hiddenInputs.appendChild(input);
                }
            }
            confirmModalCallback = function() {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Đang xử lý...';
                }
                form.submit();
            };
        } else if (typeof options.onConfirm === 'function') {
            confirmModalCallback = function() {
                options.onConfirm();
                closeConfirmModal();
            };
        } else {
            confirmModalCallback = null;
        }

        // 6. Gán sự kiện click cho nút xác nhận
        if (submitBtn) {
            submitBtn.onclick = function() {
                if (typeof confirmModalCallback === 'function') {
                    confirmModalCallback();
                } else {
                    closeConfirmModal();
                }
            };
        }

        // 7. Hiển thị modal
        modal.style.display = 'flex';
        // Cho một frame ngắn để animation transform chạy mượt
        requestAnimationFrame(function() {
            modal.classList.add('active');
        });
        document.body.style.overflow = 'hidden';
    };

    /**
     * Đóng modal xác nhận
     */
    window.closeConfirmModal = function() {
        const modal = document.getElementById('reusableConfirmModal');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 150);
    };

    // Đóng khi nhấn phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
})();
</script>
