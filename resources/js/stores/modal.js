// resources/js/stores/modal.js
import { defineStore } from 'pinia';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, QuestionMarkCircleIcon } from '@heroicons/vue/20/solid';

export const useModalStore = defineStore('modal', {
    // ເປັນຫຍັງຕ້ອງໃຊ້ Pinia ຢູ່ບ່ອນນີ້?
    // Pinia ຖືກໃຊ້ເພື່ອລວມສູນການຈັດການສະຖານະຂອງ modal ທົ່ວທັງແອັບພລິເຄເຄຊັນ.
    // ມັນອະນຸຍາດໃຫ້ອົງປະກອບຕ່າງໆສາມາດເປີດ, ປິດ, ແລະກຳນົດຄ່າ modals
    // ໂດຍການນຳໃຊ້ store ດຽວກັນ, ເຮັດໃຫ້ code ສະອາດຂຶ້ນ ແລະ ຈັດການໄດ້ງ່າຍຂຶ້ນ.
    state: () => ({
        isOpen: false,       // ສະຖານະການເປີດ/ປິດຂອງ modal
        id: 'flowbite-modal', // ID ເລີ່ມຕົ້ນສຳລັບ element modal
        type: 'alert',       // ປະເພດຂອງ modal: 'alert' ຫຼື 'confirm'
        alertType: 'info',   // ປະເພດການແຈ້ງເຕືອນ: 'success', 'error', 'warning', 'info' (ກ່ຽວຂ້ອງເມື່ອ type ເປັນ 'alert')
        title: '',           // ຫົວຂໍ້ຂອງ modal
        message: '',         // ຂໍ້ຄວາມຫຼັກຂອງ modal
        confirmButtonText: 'ຢືນຢັນ', // ຂໍ້ຄວາມປຸ່ມຢືນຢັນ
        cancelButtonText: 'ຍົກເລີກ', // ຂໍ້ຄວາມປຸ່ມຍົກເລີກ
        onConfirm: null,     // ຟັງຊັນ callback ສຳລັບການກະທຳຢືນຢັນ
        onCancel: null,      // ຟັງຊັນ callback ສຳລັບການກະທຳຍົກເລີກ/ປິດ
        icon: null,          // Heroicon component ທີ່ຈະສະແດງ
        timeoutId: null,     // ເກັບ ID ຂອງ setTimeout ສໍາລັບການປິດອັດຕະໂນມັດ
    }),

    actions: {
        // ເປີດ Modal ດ້ວຍຄ່າຕ່າງໆທີ່ກຳນົດເອງ
        openModal(options) {
            // ລຶບ timeout ທີ່ມີຢູ່ແລ້ວ (ຖ້າມີ)
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
                this.timeoutId = null;
            }

            this.isOpen = true;
            this.type = options.type || 'alert';
            this.alertType = options.alertType || 'info'; // ກຳນົດປະເພດການແຈ້ງເຕືອນ
            this.title = options.title || 'ການແຈ້ງເຕືອນ';
            this.message = options.message || '';
            this.confirmButtonText = options.confirmButtonText || 'ຢືນຢັນ';
            this.cancelButtonText = options.cancelButtonText || 'ປິດ';
            this.onConfirm = options.onConfirm || null;
            this.onCancel = options.onCancel || null;
            this.icon = options.icon || null;

            // Logic ການປິດອັດຕະໂນມັດ: ສະເພາະເມື່ອມີ autoCloseDelay ທີ່ຖືກກຳນົດ ແລະ ປະເພດແມ່ນ alert
            if (this.type === 'alert' && options.autoCloseDelay > 0) {
                this.timeoutId = setTimeout(() => {
                    this.closeModal();
                }, options.autoCloseDelay);
            }
        },

        // ປິດ Modal
        closeModal() {
            this.isOpen = false;
            this.onConfirm = null; // ລຶບ callbacks
            this.onCancel = null;
            this.icon = null; // ລຶບ icon
            this.alertType = 'info'; // ຣີເຊັດປະເພດການແຈ້ງເຕືອນ

            // ລຶບ timeout ຖ້າ modal ຖືກປິດດ້ວຍຕົນເອງ
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
                this.timeoutId = null;
            }
        },

        // ດໍາເນີນການຢືນຢັນ
        confirmAction() {
            if (this.onConfirm) {
                this.onConfirm();
            }
            this.closeModal();
        },

        // ວິທີການຊ່ວຍເຫຼືອ
        showAlert(message, title = 'ການແຈ້ງເຕືອນ', icon = InformationCircleIcon, alertType = 'info', autoCloseDelay = null) {
            this.openModal({ type: 'alert', title, message, icon, alertType, autoCloseDelay });
        },

        showSuccessAlert(message, title = 'ສຳເລັດ', icon = CheckCircleIcon, autoCloseDelay = null) {
            this.openModal({ type: 'alert', title, message, icon, alertType: 'success', autoCloseDelay });
        },

        showErrorAlert(message, title = 'ຂໍ້ຜິດພາດ', icon = XCircleIcon, autoCloseDelay = null) {
            this.openModal({ type: 'alert', title, message, icon, alertType: 'error', autoCloseDelay });
        },

        showWarningAlert(message, title = 'ຄຳເຕືອນ', icon = ExclamationTriangleIcon, autoCloseDelay = null) {
            this.openModal({ type: 'alert', title, message, icon, alertType: 'warning', autoCloseDelay });
        },

        showConfirm(message, title = 'ການຢືນຢັນ', onConfirmCallback = null, onCancelCallback = null, icon = QuestionMarkCircleIcon) {
            this.openModal({
                type: 'confirm',
                title,
                message,
                onConfirm: onConfirmCallback,
                onCancel: onCancelCallback,
                cancelButtonText: 'ຍົກເລີກ',
                icon,
                alertType: 'info', // Confirm modals ສາມາດໃຊ້ style 'info' ເລີ່ມຕົ້ນ
            });
        },
    },
});
