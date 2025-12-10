<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">ຫົວໜ່ວຍ</h2>
            <!-- ປຸ່ມເພີ່ມຫົວໜ່ວຍໃໝ່ -->
            <button class="btn btn-dash btn-info" @click="openAddEditModal">
                <PlusCircleIcon class="w-5 h-5 me-2" /> ເພີ່ມຫົວໜ່ວຍໃໝ່
            </button>
        </div>

        <!-- ລາຍການຫົວໜ່ວຍ -->
        <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mb-4">
            <!-- ສະແດງ Loading Indicator ເມື່ອກຳລັງດຶງຂໍ້ມູນ -->
            <div v-if="apiStore.isLoading('units')" class="flex justify-center items-center p-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-lg text-gray-600 dark:text-gray-400 ml-3">ກຳລັງໂຫຼດຫົວໜ່ວຍ...</p>
            </div>
            <!-- ສະແດງຕາຕະລາງເມື່ອໂຫຼດສຳເລັດ -->
            <table v-else class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ຊື່ຫົວໜ່ວຍ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ສະແດງແຖວສຳລັບແຕ່ລະຫົວໜ່ວຍ -->
                    <tr v-for="(unit, index) in units" :key="unit.id">
                        <th>{{ index + 1 }}</th>
                        <td>{{ unit.name }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <!-- ປຸ່ມແກ້ໄຂຫົວໜ່ວຍ -->
                                <button @click="editUnit(unit)" class="btn btn-sm btn-square btn-ghost text-blue-500 hover:text-blue-700">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <!-- ປຸ່ມລຶບຫົວໜ່ວຍ -->
                                <button @click="deleteUnit(unit.id)" class="btn btn-sm btn-square btn-ghost text-red-500 hover:text-red-700">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- ສະແດງຂໍ້ຄວາມເມື່ອບໍ່ມີຂໍ້ມູນ ແລະບໍ່ໄດ້ຢູ່ໃນສະຖານະ loading -->
            <div v-if="units.length === 0 && !apiStore.isLoading('units')" class="text-center py-4 text-gray-500 dark:text-gray-400">
                ບໍ່ມີຂໍ້ມູນຫົວໜ່ວຍ.
            </div>
        </div>

        <!-- ບ່ອນວາງ Pagination -->
        <Pagination
            :currentPage="pagination.currentPage"
            :totalPages="pagination.totalPages"
            :perPage="pagination.perPage"
            :total="pagination.total"
            @page-changed="handlePageChange"
            @per-page-changed="handlePerPageChange"
        />

        <!-- Modal ເພີ່ມ/ແກ້ໄຂຫົວໜ່ວຍ -->
        <dialog id="AddEditUnit" class="modal" ref="addEditUnitModal">
            <div class="modal-box p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ແບບຟອມຫົວໜ່ວຍ</h3>
                <form @submit.prevent="saveUnit">
                    <!-- ຊື່ຫົວໜ່ວຍ -->
                    <div>
                        <label for="unit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຊື່ຫົວໜ່ວຍ</label>
                        <input type="text" id="unit_name" v-model="form.name" class="input input-bordered w-full" placeholder="ຊື່ຫົວໜ່ວຍ" required>
                    </div>

                    <!-- ປຸ່ມສຳລັບ Modal -->
                    <div class="modal-action mt-6">
                        <button type="submit" class="btn btn-primary">
                            <ArrowDownOnSquareIcon class="w-5 h-5 me-2" /> ບັນທຶກ
                        </button>
                        <button type="button" class="btn" @click="closeModal">
                            <XMarkIcon class="w-5 h-5 me-2" /> ຍົກເລີກ
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { PlusCircleIcon, PencilSquareIcon, TrashIcon, ArrowDownOnSquareIcon, XMarkIcon } from '@heroicons/vue/20/solid';
import Pagination from './Pagination.vue';
import { useModalStore } from '../stores/modal';
import { useApiStore } from '../stores/api';

// UnitManager.vue
// Component ນີ້ຈັດການການສະແດງຜົນ, ການເພີ່ມ, ການແກ້ໄຂ, ແລະການລຶບຫົວໜ່ວຍສິນຄ້າ.
// ມັນໃຊ້ API Store ເພື່ອຕິດຕໍ່ກັບ Backend ແລະ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ.

// ຂໍ້ມູນຟອມແບບ Reactive ສໍາລັບ Modal (ໃຊ້ສໍາລັບການເພີ່ມ ແລະແກ້ໄຂຫົວໜ່ວຍ)
const form = ref({
    id: null, // ID ຂອງຫົວໜ່ວຍ (ເປັນ null ສໍາລັບຫົວໜ່ວຍໃໝ່, ມີຄ່າສໍາລັບການແກ້ໄຂ)
    name: '', // ຊື່ຫົວໜ່ວຍ
});

// ຂໍ້ມູນຫົວໜ່ວຍຈາກ API (ລາຍການຫົວໜ່ວຍທີ່ຈະສະແດງໃນຕາຕະລາງ)
const units = ref([]);

// Pagination State (ສະຖານະຂອງການຈັດໜ້າ)
const pagination = ref({
    currentPage: 1,   // ໜ້າປັດຈຸບັນ
    totalPages: 1,    // ຈຳນວນໜ້າທັງໝົດ
    perPage: 10,      // ຈຳນວນລາຍການຕໍ່ໜ້າ
    total: 0,         // ຈຳນວນລາຍການທັງໝົດ
});

// Reference ຫາອົງປະກອບ Modal Dialog (ໃຊ້ສໍາລັບການຄວບຄຸມ Modal ໂດຍກົງ)
const addEditUnitModal = ref(null);
const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Store ຈັດການ Modal
const apiStore = useApiStore();     // ເລີ່ມຕົ້ນ Store ຈັດການ API

// ຟັງຊັນດຶງຂໍ້ມູນຫົວໜ່ວຍຈາກ API
async function fetchUnits() {
    try {
        // ເອີ້ນ API ເພື່ອດຶງຂໍ້ມູນຫົວໜ່ວຍ, ໂດຍໃຊ້ 'units' ເປັນ key ສໍາລັບສະຖານະ loading
        const response = await apiStore.fetch('/units', {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
        }, 'units');
        
        // ອັບເດດຂໍ້ມູນຫົວໜ່ວຍ ແລະ ສະຖານະ Pagination
        units.value = response.data;
        pagination.value.currentPage = response.current_page;
        pagination.value.totalPages = response.last_page;
        pagination.value.perPage = response.per_page;
        pagination.value.total = response.total;

    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການດຶງຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນຫົວໜ່ວຍ');
    }
}

// ເອີ້ນຟັງຊັນດຶງຂໍ້ມູນເມື່ອ Component ຖືກ mounted (ເມື່ອ Component ຖືກສ້າງຂຶ້ນໃນ DOM)
onMounted(() => {
    fetchUnits();
});

// ຟັງຊັນຈັດການການປ່ຽນໜ້າ
const handlePageChange = (page) => {
    pagination.value.currentPage = page;
    fetchUnits(); // ດຶງຂໍ້ມູນໃໝ່ຕາມໜ້າທີ່ປ່ຽນ
};

// ຟັງຊັນຈັດການການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const handlePerPageChange = (perPage) => {
    pagination.value.perPage = perPage;
    pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດເມື່ອປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
    fetchUnits(); // ດຶງຂໍ້ມູນໃໝ່
};

// ຟັງຊັນເປີດ Modal ເພີ່ມ/ແກ້ໄຂຫົວໜ່ວຍ
const openAddEditModal = () => {
    form.value = { id: null, name: '' }; // ຣີເຊັດຟອມສຳລັບເພີ່ມຫົວໜ່ວຍໃໝ່
    addEditUnitModal.value.showModal(); // ເປີດ Modal
};

// ຟັງຊັນປິດ Modal
const closeModal = () => {
    addEditUnitModal.value.close(); // ປິດ Modal
};

// ຟັງຊັນບັນທຶກຫົວໜ່ວຍ (ສຳລັບເພີ່ມໃໝ່ ຫຼື ອັບເດດ)
async function saveUnit() {
    try {
        if (form.value.id) {
            // ຖ້າມີ ID, ເປັນການອັບເດດຫົວໜ່ວຍທີ່ມີຢູ່ແລ້ວ
            await apiStore.put(`/units/${form.value.id}`, form.value, 'unitsSave');
            modalStore.showSuccessAlert('ອັບເດດຫົວໜ່ວຍສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
        } else {
            // ຖ້າບໍ່ມີ ID, ເປັນການເພີ່ມຫົວໜ່ວຍໃໝ່
            await apiStore.post('/units', form.value, 'unitsSave');
            modalStore.showSuccessAlert('ເພີ່ມຫົວໜ່ວຍສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
        }
        closeModal();          // ປິດ Modal ຟອມຫຼັງຈາກບັນທຶກສຳເລັດ
        await fetchUnits(); // ໂຫຼດລາຍການຫົວໜ່ວຍຄືນໃໝ່ເພື່ອສະແດງການປ່ຽນແປງ
    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການບັນທຶກຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການບັນທຶກຫົວໜ່ວຍ');
    }
}

// ຟັງຊັນແກ້ໄຂຫົວໜ່ວຍ (ເປີດ Modal ດ້ວຍຂໍ້ມູນຫົວໜ່ວຍທີ່ຈະແກ້ໄຂ)
const editUnit = (unit) => {
    form.value = { ...unit }; // ໂຫຼດຂໍ້ມູນຫົວໜ່ວຍເຂົ້າໃນຟອມ
    addEditUnitModal.value.showModal(); // ເປີດ Modal
};

// ຟັງຊັນລຶບຫົວໜ່ວຍ
const deleteUnit = (unitId) => {
    modalStore.showConfirm(
        'ທ່ານຕ້ອງການລຶບຫົວໜ່ວຍນີ້ແທ້ບໍ?', // ຂໍ້ຄວາມຢືນຢັນ
        'ຢືນຢັນການລຶບ',              // ຫົວຂໍ້ Modal
        async () => {                    // Callback ທີ່ຈະຖືກເອີ້ນເມື່ອຜູ້ໃຊ້ຢືນຢັນ
            try {
                await apiStore.destroy(`/units`, unitId, 'unitsDelete'); // ເອີ້ນ API ເພື່ອລຶບ
                modalStore.showSuccessAlert('ລຶບຫົວໜ່ວຍສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
                await fetchUnits(); // ໂຫຼດລາຍການຫົວໜ່ວຍຄືນໃໝ່
            } catch (error) {
                // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການລຶບຂໍ້ມູນລົ້ມເຫຼວ
                modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການລຶບຫົວໜ່ວຍ');
            }
        }
    );
};
</script>
<style scoped>
/* Scoped styles for UnitManager.vue */
/* ປັດຈຸບັນ, styles ສ່ວນໃຫຍ່ແມ່ນຈັດການໂດຍ Tailwind CSS ແລະ DaisyUI */
</style>