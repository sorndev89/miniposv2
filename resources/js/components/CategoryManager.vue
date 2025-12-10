<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">ໝວດໝູ່</h2>
            <!-- ປຸ່ມເພີ່ມໝວດໝູ່ໃໝ່ -->
            <button class="btn btn-dash btn-info" @click="openAddEditModal">
                <PlusCircleIcon class="w-5 h-5 me-2" /> ເພີ່ມໝວດໝູ່ໃໝ່
            </button>
        </div>

        <!-- ລາຍການໝວດໝູ່ -->
        <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mb-4">
            <!-- ສະແດງ Loading Indicator ເມື່ອກຳລັງດຶງຂໍ້ມູນ -->
            <div v-if="apiStore.isLoading('categories')" class="flex justify-center items-center p-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-lg text-gray-600 dark:text-gray-400 ml-3">ກຳລັງໂຫຼດໝວດໝູ່...</p>
            </div>
            <!-- ສະແດງຕາຕະລາງເມື່ອໂຫຼດສຳເລັດ -->
            <table v-else class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ຊື່ໝວດໝູ່</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ສະແດງແຖວສຳລັບແຕ່ລະໝວດໝູ່ -->
                    <tr v-for="(category, index) in categories" :key="category.id">
                        <th>{{ index + 1 }}</th>
                        <td>{{ category.name }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <!-- ປຸ່ມແກ້ໄຂໝວດໝູ່ -->
                                <button @click="editCategory(category)" class="btn btn-sm btn-square btn-ghost text-blue-500 hover:text-blue-700">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <!-- ປຸ່ມລຶບໝວດໝູ່ -->
                                <button @click="deleteCategory(category.id)" class="btn btn-sm btn-square btn-ghost text-red-500 hover:text-red-700">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- ສະແດງຂໍ້ຄວາມເມື່ອບໍ່ມີຂໍ້ມູນ ແລະບໍ່ໄດ້ຢູ່ໃນສະຖານະ loading -->
            <div v-if="categories.length === 0 && !apiStore.isLoading('categories')" class="text-center py-4 text-gray-500 dark:text-gray-400">
                ບໍ່ມີຂໍ້ມູນໝວດໝູ່.
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

        <!-- Modal ເພີ່ມ/ແກ້ໄຂໝວດໝູ່ -->
        <dialog id="AddEditCategory" class="modal" ref="addEditCategoryModal">
            <div class="modal-box p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ແບບຟອມໝວດໝູ່</h3>
                <form @submit.prevent="saveCategory">
                    <!-- ຊື່ໝວດໝູ່ -->
                    <div>
                        <label for="category_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຊື່ໝວດໝູ່</label>
                        <input type="text" id="category_name" v-model="form.name" class="input input-bordered w-full" placeholder="ຊື່ໝວດໝູ່" required>
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

// CategoryManager.vue
// Component ນີ້ຈັດການການສະແດງຜົນ, ການເພີ່ມ, ການແກ້ໄຂ, ແລະການລຶບໝວດໝູ່ສິນຄ້າ.
// ມັນໃຊ້ API Store ເພື່ອຕິດຕໍ່ກັບ Backend ແລະ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ.

// ຂໍ້ມູນຟອມແບບ Reactive ສໍາລັບ Modal (ໃຊ້ສໍາລັບການເພີ່ມ ແລະແກ້ໄຂໝວດໝູ່)
const form = ref({
    id: null, // ID ຂອງໝວດໝູ່ (ເປັນ null ສໍາລັບໝວດໝູ່ໃໝ່, ມີຄ່າສໍາລັບການແກ້ໄຂ)
    name: '', // ຊື່ໝວດໝູ່
});

// ຂໍ້ມູນໝວດໝູ່ຈາກ API (ລາຍການໝວດໝູ່ທີ່ຈະສະແດງໃນຕາຕະລາງ)
const categories = ref([]);

// Pagination State (ສະຖານະຂອງການຈັດໜ້າ)
const pagination = ref({
    currentPage: 1,   // ໜ້າປັດຈຸບັນ
    totalPages: 1,    // ຈຳນວນໜ້າທັງໝົດ
    perPage: 10,      // ຈຳນວນລາຍການຕໍ່ໜ້າ
    total: 0,         // ຈຳນວນລາຍການທັງໝົດ
});

// Reference ຫາອົງປະກອບ Modal Dialog (ໃຊ້ສໍາລັບການຄວບຄຸມ Modal ໂດຍກົງ)
const addEditCategoryModal = ref(null);
const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Store ຈັດການ Modal
const apiStore = useApiStore();     // ເລີ່ມຕົ້ນ Store ຈັດການ API

// ຟັງຊັນດຶງຂໍ້ມູນໝວດໝູ່ຈາກ API
async function fetchCategories() {
    try {
        // ເອີ້ນ API ເພື່ອດຶງຂໍ້ມູນໝວດໝູ່, ໂດຍໃຊ້ 'categories' ເປັນ key ສໍາລັບສະຖານະ loading
        const response = await apiStore.fetch('/categories', {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
        }, 'categories');
        
        // ອັບເດດຂໍ້ມູນໝວດໝູ່ ແລະ ສະຖານະ Pagination
        categories.value = response.data;
        pagination.value.currentPage = response.current_page;
        pagination.value.totalPages = response.last_page;
        pagination.value.perPage = response.per_page;
        pagination.value.total = response.total;

    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການດຶງຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນໝວດໝູ່');
    }
}

// ເອີ້ນຟັງຊັນດຶງຂໍ້ມູນເມື່ອ Component ຖືກ mounted (ເມື່ອ Component ຖືກສ້າງຂຶ້ນໃນ DOM)
onMounted(() => {
    fetchCategories();
});

// ຟັງຊັນຈັດການການປ່ຽນໜ້າ
const handlePageChange = (page) => {
    pagination.value.currentPage = page;
    fetchCategories(); // ດຶງຂໍ້ມູນໃໝ່ຕາມໜ້າທີ່ປ່ຽນ
};

// ຟັງຊັນຈັດການການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const handlePerPageChange = (perPage) => {
    pagination.value.perPage = perPage;
    pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດເມື່ອປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
    fetchCategories(); // ດຶງຂໍ້ມູນໃໝ່
};

// ຟັງຊັນເປີດ Modal ເພີ່ມ/ແກ້ໄຂໝວດໝູ່
const openAddEditModal = () => {
    form.value = { id: null, name: '' }; // ຣີເຊັດຟອມສຳລັບເພີ່ມໝວດໝູ່ໃໝ່
    addEditCategoryModal.value.showModal(); // ເປີດ Modal
};

// ຟັງຊັນປິດ Modal
const closeModal = () => {
    addEditCategoryModal.value.close(); // ປິດ Modal
};

// ຟັງຊັນບັນທຶກໝວດໝູ່ (ສຳລັບເພີ່ມໃໝ່ ຫຼື ອັບເດດ)
async function saveCategory() {
    try {
        if (form.value.id) {
            // ຖ້າມີ ID, ເປັນການອັບເດດໝວດໝູ່ທີ່ມີຢູ່ແລ້ວ
            await apiStore.put(`/categories/${form.value.id}`, form.value, 'categoriesSave');
            modalStore.showSuccessAlert('ອັບເດດໝວດໝູ່ສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
        } else {
            // ຖ້າບໍ່ມີ ID, ເປັນການເພີ່ມໝວດໝູ່ໃໝ່
            await apiStore.post('/categories', form.value, 'categoriesSave');
            modalStore.showSuccessAlert('ເພີ່ມໝວດໝູ່ສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
        }
        closeModal();          // ປິດ Modal ຟອມຫຼັງຈາກບັນທຶກສຳເລັດ
        await fetchCategories(); // ໂຫຼດລາຍການໝວດໝູ່ຄືນໃໝ່ເພື່ອສະແດງການປ່ຽນແປງ
    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການບັນທຶກຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການບັນທຶກໝວດໝູ່');
    }
}

// ຟັງຊັນແກ້ໄຂໝວດໝູ່ (ເປີດ Modal ດ້ວຍຂໍ້ມູນໝວດໝູ່ທີ່ຈະແກ້ໄຂ)
const editCategory = (category) => {
    form.value = { ...category }; // ໂຫຼດຂໍ້ມູນໝວດໝູ່ເຂົ້າໃນຟອມ
    addEditCategoryModal.value.showModal(); // ເປີດ Modal
};

// ຟັງຊັນລຶບໝວດໝູ່
const deleteCategory = (categoryId) => {
    modalStore.showConfirm(
        'ທ່ານຕ້ອງການລຶບໝວດໝູ່ນີ້ແທ້ບໍ?', // ຂໍ້ຄວາມຢືນຢັນ
        'ຢືນຢັນການລຶບ',              // ຫົວຂໍ້ Modal
        async () => {                    // Callback ທີ່ຈະຖືກເອີ້ນເມື່ອຜູ້ໃຊ້ຢືນຢັນ
            try {
                await apiStore.destroy(`/categories`, categoryId, 'categoriesDelete'); // ເອີ້ນ API ເພື່ອລຶບ
                modalStore.showSuccessAlert('ລຶບໝວດໝູ່ສຳເລັດ!', 'ສຳເລັດ', null, 1000); // ສະແດງ Modal ແຈ້ງເຕືອນສຳເລັດ
                await fetchCategories(); // ໂຫຼດລາຍການໝວດໝູ່ຄືນໃໝ່
            } catch (error) {
                // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຖ້າການລຶບຂໍ້ມູນລົ້ມເຫຼວ
                modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການລຶບໝວດໝູ່');
            }
        }
    );
};
</script>
<style scoped>
/* Scoped styles for CategoryManager.vue */
/* ປັດຈຸບັນ, styles ສ່ວນໃຫຍ່ແມ່ນຈັດການໂດຍ Tailwind CSS ແລະ DaisyUI */
</style>