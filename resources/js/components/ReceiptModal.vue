<template>
    <dialog id="receiptModal" class="modal" ref="receiptModalRef">
        <div class="modal-box p-6 print-container" id="receipt-content-modal">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 text-center">MiniPOS - ໃບບິນ</h3>
            
            <div class="mb-4 text-xs">
                <p><strong>ວັນທີ:</strong> {{ apiStore.formatDate(receiptData.order_date) }}</p>
                <p><strong>ລະຫັດບິນ:</strong> {{ receiptData.order_code }}</p>
                <p><strong>ຜູ້ຂາຍ:</strong> {{ receiptData.seller?.name || 'N/A' }}</p>
            </div>

            <div class="border-t border-b border-dashed border-gray-400 py-2 mb-4">
                <p class="font-bold mb-2">ລາຍການສິນຄ້າ:</p>
                <div v-for="item in receiptData.items" :key="item.product_id" class="flex justify-between py-1 text-xs">
                    <span>{{ item.product?.name }} x {{ item.quantity }}</span>
                    <span>₭{{ apiStore.formatNumber(item.price_at_sale * item.quantity) }}</span>
                </div>
            </div>

            <div class="mb-4 text-xs">
                <div class="flex justify-between font-bold mb-1">
                    <span>ລວມທັງໝົດ:</span>
                    <span>₭{{ apiStore.formatNumber(receiptData.total_amount) }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>ເງິນທີ່ຮັບ:</span>
                    <span>₭{{ apiStore.formatNumber(receiptData.amount_received) }}</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>ເງິນທອນ:</span>
                    <span>₭{{ apiStore.formatNumber(receiptData.change_amount) }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-gray-400 pt-4 mt-4 text-xs text-center">
                <p>ຂອບໃຈທີ່ໃຊ້ບໍລິການ!</p>
                <p>*** ບໍ່ສາມາດປ່ຽນຄືນໄດ້ ***</p>
            </div>

            <div class="modal-action justify-center mt-6">
                <button class="btn btn-primary" @click="printReceipt">ພິມໃບບິນ</button>
                <button class="btn" @click="closeModal">ປິດ</button>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { defineProps, ref, watch } from 'vue';
import { useApiStore } from '../stores/api';

// ReceiptModal.vue
// Component ນີ້ແມ່ນ Modal ສຳລັບສະແດງໃບບິນ (receipt) ຂອງການສັ່ງຊື້.
// ມັນຮັບຂໍ້ມູນການສັ່ງຊື້ຜ່ານ props ແລະມີຟັງຊັນສຳລັບພິມໃບບິນ.

const apiStore = useApiStore(); // ນຳໃຊ້ Store ສໍາລັບການຈັດການ API

// ກຳນົດ Props ທີ່ Component ນີ້ຈະໄດ້ຮັບຈາກ Parent Component
const props = defineProps({
    orderData: {
        type: Object,
        default: () => ({}) // ຂໍ້ມູນການສັ່ງຊື້ທີ່ຈະສະແດງໃນໃບບິນ
    }
});

const receiptModalRef = ref(null); // Reference ຫາ element <dialog> ຂອງ Modal
const receiptData = ref({});       // ຕົວປ່ຽນ Reactive ເພື່ອເກັບຂໍ້ມູນໃບບິນ

// Watcher ເພື່ອກວດສອບການປ່ຽນແປງຂອງ props.orderData
watch(() => props.orderData, (newVal) => {
    // ຖ້າມີຂໍ້ມູນໃໝ່ຖືກສົ່ງມາ ແລະບໍ່ແມ່ນ object ວ່າງເປົ່າ
    if (Object.keys(newVal).length > 0) {
        receiptData.value = newVal; // ອັບເດດຂໍ້ມູນໃບບິນ
        showModal(); // ສະແດງ Modal
    }
}, { immediate: true }); // ເຮັດວຽກທັນທີເມື່ອ component ຖືກ mounted

// ຟັງຊັນເປີດ Modal
const showModal = () => {
    receiptModalRef.value.showModal();
};

// ຟັງຊັນປິດ Modal
const closeModal = () => {
    receiptModalRef.value.close();
};

// ຟັງຊັນສຳລັບພິມໃບບິນ
const printReceipt = () => {
    const printContent = document.getElementById('receipt-content-modal'); // ດຶງເອົາ content ທີ່ຈະພິມ
    const printWindow = window.open('', '_blank'); // ເປີດໜ້າຕ່າງໃໝ່ສຳລັບການພິມ
    
    if (printWindow) {
        printWindow.document.write('<html><head><title>ໃບບິນ</title>');
        // ນຳເຂົ້າ CSS ຫຼັກຂອງແອັບ
        printWindow.document.write('<link href="/build/assets/app.css" rel="stylesheet">');
        printWindow.document.write('<style>');
        // Styles ສໍາລັບການພິມໃບບິນ (ຕັ້ງຄ່າຂະໜາດ, font, ການຈັດຮູບແບບຕ່າງໆ)
        printWindow.document.write(`
            @page {
                size: 80mm auto; /* ກຳນົດຄວາມກວ້າງໃບບິນ 80mm, ຄວາມສູງອັດຕະໂນມັດ */
                margin: 0;
            }
            body {
                font-family: 'Noto Sans Lao Looped', sans-serif;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: white;
                color: black;
            }
            .print-container {
                width: 78mm; /* ນ້ອຍກວ່າ 80mm ເລັກນ້ອຍເພື່ອຄິດໄລ່ຂອບຂອງເຄື່ອງພິມ */
                margin: 0 auto;
                padding: 5mm; /* ເພີ່ມ padding ບາງສ່ວນສຳລັບເນື້ອຫາ */
                box-shadow: none;
                background-color: white;
                color: black;
                font-size: 10px;
                line-height: 1.4;
            }
            h3 {
                font-size: 16px; /* ຂະໜາດໃຫຍ່ຂຶ້ນເລັກນ້ອຍສຳລັບຫົວຂໍ້ຫຼັກ */
                margin-bottom: 10px;
                text-align: center;
                font-weight: bold;
            }
            p {
                margin: 0;
                padding: 0;
            }
            strong {
                font-weight: bold;
            }
            .mb-4 { margin-bottom: 8px; }
            .mb-2 { margin-bottom: 4px; }
            .mb-1 { margin-bottom: 2px; }
            .text-xs { font-size: 10px; }
            .flex { display: flex; }
            .justify-between { justify-content: space-between; }
            .font-bold { font-weight: bold; }
            .border-t { border-top: 1px dashed #ccc; padding-top: 5px; border-bottom: 0px; border-left: 0px; border-right: 0px; }
            .border-b { border-bottom: 1px dashed #ccc; padding-bottom: 5px; }
            .border-dashed { border-style: dashed; }
            .py-2 { padding-top: 5px; padding-bottom: 5px; }
            .py-1 { padding-top: 2px; padding-bottom: 2px; }
            .pt-4 { padding-top: 10px; }
            .mt-4 { margin-top: 10px; }
            .text-center { text-align: center; }
            .modal-action { display: none; } /* ເຊື່ອງປຸ່ມຕ່າງໆໃນເວລາພິມ */
        `);
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent.outerHTML); // ເພີ່ມເນື້ອຫາໃບບິນ
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus(); // ໂຟກັສໃສ່ໜ້າຕ່າງພິມ
        printWindow.print(); // ເອີ້ນຄຳສັ່ງພິມ
        printWindow.onafterprint = () => {
            closeModal(); // ປິດ Modal ຫຼັງຈາກປ່ອງຢ້ຽມພິມຖືກປິດ
        };
        // ພະຍາຍາມປິດໜ້າຕ່າງພິມຫຼັງຈາກຊັກຊ້າເລັກນ້ອຍ
        setTimeout(() => {
            printWindow.close();
        }, 100); 
    } else {
        alert('ບໍ່ສາມາດເປີດໜ້າພິມໄດ້. ກະລຸນາກວດສອບການຕັ້ງຄ່າ Pop-up Blocker.');
    }
};

</script>

<style scoped>
/* Styling for the modal content, mimicking receipt */

@media print {
    body {
        margin: 0;
        padding: 0;
    }
    .modal-box {
        width: auto !important;
        height: auto !important;
        overflow: visible !important;
        max-width: none !important;
        box-shadow: none !important;
        background-color: white !important;
        color: black !important;
    }
    .modal-action {
        display: none !important; /* Hide action buttons when printing */
    }
}
</style>
