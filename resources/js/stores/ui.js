// resources/js/stores/ui.js
import { defineStore } from 'pinia';
export const useUiStore = defineStore('ui', {
    // ເປັນຫຍັງຕ້ອງໃຊ້ Pinia ຢູ່ບ່ອນນີ້?
    // Pinia ຖືກໃຊ້ເພື່ອຈັດການສະຖານະຂອງ UI ທົ່ວໂລກ, ເຊັ່ນວ່າ sidebar ເປີດຢູ່ ຫຼື ປິດ.
    // ມັນຊ່ວຍໃຫ້ອົງປະກອບຕ່າງໆສາມາດອ່ານແລະປ່ຽນແປງສະຖານະ UI ໄດ້ງ່າຍ,
    // ຮັບປະກັນຄວາມສອດຄ່ອງໃນການໂຕ້ຕອບຂອງຜູ້ໃຊ້.
    state: () => ({
        isSidebarOpen: window.innerWidth >= 640, // Sidebar ເປີດໂດຍຄ່າເລີ່ມຕົ້ນໃນໜ້າຈໍຂະໜາດ sm (640px) ຂຶ້ນໄປ
    }),
    actions: {
        // ສະຫຼັບສະຖານະການເປີດ/ປິດຂອງ Sidebar
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        // ເປີດ Sidebar
        openSidebar() {
            this.isSidebarOpen = true;
        },
        // ປິດ Sidebar
        closeSidebar() {
            this.isSidebarOpen = false;
        },
    },
});
