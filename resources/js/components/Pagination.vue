<template>
    <div class="flex justify-between items-center p-2">
        <!-- ສ່ວນສະແດງຈຳນວນລາຍການ ແລະ ປຸ່ມເລືອກ Items per page -->
        <div class="text-xs">ສະແດງ
            <select v-model="itemsPerPage" @change="emitChangePerPage" class="select select-xs w-16">
                <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
            </select>
            ລາຍການ. {{ (currentPage - 1) * perPage + 1 }} ຫາ {{ Math.min(currentPage * perPage, total) }} ຈາກ {{ total }} ລາຍການ
        </div>
        
        <!-- ສ່ວນຄວບຄຸມ Pagination -->
        <div class="join">
            <!-- ປຸ່ມໄປໜ້າກ່ອນໜ້າ (Previous Page) -->
            <button class="join-item btn btn-sm" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">‹</button>

            <!-- ສະແດງປຸ່ມຕົວເລກໜ້າແບບ Dynamic -->
            <template v-for="page in visiblePages" :key="page">
                <button v-if="page === '...'" class="join-item btn btn-sm btn-disabled">...</button>
                <button v-else class="join-item btn btn-sm" :class="{'btn-active': page === currentPage}" @click="goToPage(page)">{{ page }}</button>
            </template>

            <!-- ປຸ່ມໄປໜ້າຕໍ່ໄປ (Next Page) -->
            <button class="join-item btn btn-sm" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">›</button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

// ກຳນົດ Props ທີ່ Component ນີ້ຈະໄດ້ຮັບຈາກ Parent Component
const props = defineProps({
    currentPage: {
        type: Number,
        default: 1 // ໜ້າປັດຈຸບັນ
    },
    totalPages: {
        type: Number,
        default: 1 // ຈຳນວນໜ້າທັງໝົດ
    },
    perPage: {
        type: Number,
        default: 10 // ຈຳນວນລາຍການຕໍ່ໜ້າ
    },
    total: {
        type: Number,
        default: 0 // ຈຳນວນລາຍການທັງໝົດ
    },
    perPageOptions: {
        type: Array,
        default: () => [5,10, 20, 30, 50] // ຕົວເລືອກຈຳນວນລາຍການຕໍ່ໜ້າ
    },
    maxVisibleButtons: {
        type: Number,
        default: 5 // ຈຳນວນປຸ່ມໜ້າທີ່ສະແດງໃຫ້ເຫັນສູງສຸດ
    }
});

// ກຳນົດ Events ທີ່ Component ນີ້ຈະ Emit ໃຫ້ Parent Component ຟັງ
const emit = defineEmits(['update:currentPage', 'update:perPage', 'page-changed', 'per-page-changed']);

// ຕົວປ່ຽນ Reactive ເພື່ອຄວບຄຸມການເລືອກ Items per page ໃນ Dropdown
const itemsPerPage = ref(props.perPage);

// Computed Property ເພື່ອສ້າງລາຍການປຸ່ມໜ້າທີ່ສາມາດເບິ່ງເຫັນໄດ້ແບບ Dynamic
const visiblePages = computed(() => {
    const pages = [];
    // ກຳນົດໜ້າເລີ່ມຕົ້ນທີ່ຈະສະແດງ
    const startPage = Math.max(1, props.currentPage - Math.floor(props.maxVisibleButtons / 2));
    // ກຳນົດໜ້າສຸດທ້າຍທີ່ຈະສະແດງ
    const endPage = Math.min(props.totalPages, startPage + props.maxVisibleButtons - 1);

    // ເພີ່ມປຸ່ມໜ້າທຳອິດ ແລະ "..." ຖ້າຈຳເປັນ
    if (startPage > 1) {
        pages.push(1);
        if (startPage > 2) pages.push('...');
    }

    // ເພີ່ມປຸ່ມໜ້າຕ່າງໆພາຍໃນຊ່ວງທີ່ສາມາດເບິ່ງເຫັນໄດ້
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }

    // ເພີ່ມປຸ່ມໜ້າສຸດທ້າຍ ແລະ "..." ຖ້າຈຳເປັນ
    if (endPage < props.totalPages) {
        if (endPage < props.totalPages - 1) pages.push('...');
        pages.push(props.totalPages);
    }
    return pages;
});

// ຟັງຊັນສຳລັບການປ່ຽນໜ້າ
const goToPage = (page) => {
    // ກວດສອບວ່າໜ້າທີ່ເລືອກຖືກຕ້ອງ ແລະ ບໍ່ແມ່ນໜ້າປັດຈຸບັນ
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit('update:currentPage', page); // Emit event ເພື່ອອັບເດດ currentPage prop
        emit('page-changed', page);       // Emit event ພ້ອມເລກໜ້າທີ່ປ່ຽນແປງ
    }
};

// ຟັງຊັນສຳລັບການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const emitChangePerPage = () => {
    emit('update:perPage', itemsPerPage.value);      // Emit event ເພື່ອອັບເດດ perPage prop
    emit('per-page-changed', itemsPerPage.value);    // Emit event ພ້ອມຈຳນວນລາຍການຕໍ່ໜ້າທີ່ປ່ຽນແປງ
};

// Watcher ເພື່ອກວດສອບການປ່ຽນແປງຂອງ props.perPage ຈາກ Parent Component
// ແລະອັບເດດ itemsPerPage ພາຍໃນ Component ເພື່ອໃຫ້ Dropdown ສະແດງຄ່າທີ່ຖືກຕ້ອງ
watch(() => props.perPage, (newVal) => {
  itemsPerPage.value = newVal;
});
</script>

<style  scoped>
/* Scoped styles for Pagination.vue if needed */
/* ປັດຈຸບັນ, styles ສ່ວນໃຫຍ່ແມ່ນຈັດການໂດຍ Tailwind CSS ແລະ DaisyUI */
</style>