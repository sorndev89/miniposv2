<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ຈັດການສິນຄ້າ</h1>
            <div class="flex items-center">
                <label class="input me-2">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></g></svg>
                    <input type="search" v-model="searchQuery" placeholder="ຄົ້ນຫາ..." />
                </label>
                <button class="btn btn-dash btn-info" @click="openAddEditModal">
                    <PlusCircleIcon class="w-6 h-6 me-2" /> ເພີ່ມສິນຄ້າໃໝ່
                </button>
            </div>
        </div>

        <!-- Product List -->
        <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mb-4">
            <div v-if="apiStore.isLoading('products')" class="flex justify-center items-center p-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-lg text-gray-600 dark:text-gray-400 ml-3">ກຳລັງໂຫຼດຂໍ້ມູນສິນຄ້າ...</p>
            </div>
            <table v-else class="table">
                <thead>
                    <tr>
                        <th @click="handleSort('id')" class="cursor-pointer"># <span v-if="sortBy === 'id'"><ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/><ChevronDownIcon v-else class="w-4 h-4 inline"/></span></th>
                        <th>ຮູບ</th>
                        <th @click="handleSort('name')" class="cursor-pointer">ຊື່ສິນຄ້າ <span v-if="sortBy === 'name'"><ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/><ChevronDownIcon v-else class="w-4 h-4 inline"/></span></th>
                        <th>ໝວດໝູ່</th>
                        <th>ຈຳນວນ</th>
                        <th @click="handleSort('selling_price')" class="cursor-pointer">ລາຄາຂາຍ <span v-if="sortBy === 'selling_price'"><ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/><ChevronDownIcon v-else class="w-4 h-4 inline"/></span></th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id">
                        <th>{{ product.id }}</th>
                        <td>
                            <div class="avatar">
                                <div class="mask mask-squircle w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-xl font-bold text-gray-700 dark:text-gray-200">
                                    <img v-if="product.image_url" :src="apiStore.getFullImageUrl(product.image_url)" :alt="product.name" />
                                    <span v-else>{{ product.name?.charAt(0) || '?' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.category?.name || 'N/A' }}</td>
                        <td>{{ product.stock_quantity }} {{ product.unit?.name || 'N/A' }}</td>
                        <td>₭ {{  apiStore.formatNumber(product.selling_price) }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button @click="editProduct(product)" class="btn btn-sm btn-square btn-ghost text-blue-500"><PencilSquareIcon class="w-5 h-5" /></button>
                                <button @click="deleteProduct(product.id)" class="btn btn-sm btn-square btn-ghost text-red-500"><TrashIcon class="w-5 h-5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
             <div v-if="products.length === 0 && !apiStore.isLoading('products')" class="text-center py-4 text-gray-500">
                ບໍ່ມີຂໍ້ມູນສິນຄ້າ.
            </div>
        </div>

        <!-- Pagination -->
        <Pagination
            :currentPage="pagination.currentPage"
            :totalPages="pagination.totalPages"
            :perPage="pagination.perPage"
            :total="pagination.total"
            @page-changed="handlePageChange"
            @per-page-changed="handlePerPageChange"
        />

        <!-- Add/Edit Modal -->
        <dialog id="AddEditProduct" class="modal" ref="addEditProductModal">
            <div class="modal-box p-6">
                <h3 class="font-bold text-lg mb-4">ແບບຟອມສິນຄ້າ</h3>
                <form @submit.prevent="saveProduct">
                     <!-- Image Upload -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຮູບສິນຄ້າ</label>
                        <div class="flex justify-center">
                        <div @click="triggerFileInput" @dragover.prevent @dragenter.prevent @drop.prevent="handleFileDrop" class="border-2 border-dashed rounded-lg p-2 flex justify-center  cursor-pointer hover:border-blue-500 w-40 h-40">
                            <input type="file" ref="imageInput" @change="handleFileChange" class="hidden" accept="image/*">
                            <div v-if="imagePreview" class="flex justify-center items-center"><img :src="imagePreview" class="w-32 h-32 object-cover rounded-md"></div>
                            <div v-else class="text-gray-500"><p>ລາກ & ວາງ ຫຼື ຄລິກເພື່ອເລືອກຮູບ</p><p class="text-xs mt-1">PNG, JPG (ສູງສຸດ 10MB)</p></div>
                        </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label for="product_name" class="label">ຊື່ສິນຄ້າ</label><input type="text" v-model="form.name" class="input input-bordered w-full" required></div>
                        <div><label for="category" class="label">ໝວດໝູ່</label><select v-model="form.category_id" class="select select-bordered w-full" required><option disabled value="">ເລືອກໝວດໝູ່</option><option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option></select></div>
                        <div><label for="unit" class="label">ຫົວໜ່ວຍ</label><select v-model="form.unit_id" class="select select-bordered w-full" required><option disabled value="">ເລືອກຫົວໜ່ວຍ</option><option v-for="un in units" :key="un.id" :value="un.id">{{ un.name }}</option></select></div>
                        
                        <div>
                            <label for="price" class="label">ລາຄາຂາຍ</label>
                            <Cleave v-model.number="form.selling_price" :options="cleaveOptions" class="input input-bordered w-full" min="0" required />
                        </div>
                  
                    </div>
                    <div class="mt-4"><label for="description" class="label">ລາຍລະອຽດ</label><textarea v-model="form.description" class="textarea textarea-bordered w-full" rows="3"></textarea></div>

                    <div class="modal-action mt-6">
                        <button type="submit" class="btn btn-primary" :disabled="apiStore.isLoading('productsSave')"><span v-if="apiStore.isLoading('productsSave')" class="loading loading-spinner"></span><ArrowDownOnSquareIcon v-else class="w-5 h-5 me-2" />ບັນທຶກ</button>
                        <button type="button" class="btn" @click="closeModal"><XMarkIcon class="w-5 h-5 me-2" />ຍົກເລີກ</button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { PlusCircleIcon, PencilSquareIcon, TrashIcon, ArrowDownOnSquareIcon, XMarkIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import Pagination from '../components/Pagination.vue';
import { useModalStore } from '../stores/modal';
import { useApiStore } from '../stores/api';
import Cleave from 'vue-cleave-component';

// Product.vue
// Component ນີ້ຈັດການການສະແດງຜົນ, ການເພີ່ມ, ການແກ້ໄຂ, ແລະການລຶບສິນຄ້າ.
// ມັນໃຊ້ API Store ເພື່ອຕິດຕໍ່ກັບ Backend ແລະ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ.

// --- State Variables ---
const products = ref([]); // ລາຍການສິນຄ້າທັງໝົດທີ່ດຶງມາຈາກ API
const categories = ref([]); // ລາຍການໝວດໝູ່ສິນຄ້າສຳລັບ dropdown
const units = ref([]); // ລາຍການຫົວໜ່ວຍສິນຄ້າສຳລັບ dropdown
const searchQuery = ref(''); // ຄ່າການຄົ້ນຫາສິນຄ້າ
const sortBy = ref('id'); // ຖັນທີ່ໃຊ້ໃນການຈັດຮຽງ
const sortDirection = ref('desc'); // ທິດທາງການຈັດຮຽງ ('asc' ຫຼື 'desc')

// ຂໍ້ມູນຟອມສຳລັບການເພີ່ມ/ແກ້ໄຂສິນຄ້າ
const form = ref({
    id: null, name: '', description: '', category_id: '', unit_id: '',
    selling_price: 0,
});

const imageFile = ref(null);    // ໄຟລ໌ຮູບພາບທີ່ເລືອກ
const imagePreview = ref(null); // URL ຂອງຮູບພາບເພື່ອກ່ອນສະແດງ
const imageInput = ref(null);   // Reference ຫາ input ໄຟລ໌

// ສະຖານະການຈັດໜ້າ
const pagination = ref({ currentPage: 1, totalPages: 1, perPage: 10, total: 0 });

// References ຫາ Modal ແລະ Stores
const addEditProductModal = ref(null); // Reference ຫາ element dialog modal
const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Modal Store
const apiStore = useApiStore();     // ເລີ່ມຕົ້ນ API Store

// Cleave.js options for currency formatting (ຕົວເລືອກ Cleave.js ສຳລັບການຈັດຮູບແບບສະກຸນເງິນ)
const cleaveOptions = {
    numeral: true,                  // ເປີດໃຊ້ງານການຈັດຮູບແບບຕົວເລກ
    numeralPositiveOnly: true,      // ຮັບສະເພາະຕົວເລກບວກ
    numeralThousandsGroupStyle: 'thousand', // ຈັດກຸ່ມຕົວເລກຫຼັກພັນ
    numeralDecimalScale: 2,         // ຈຳນວນຕົວເລກຫຼັງຈຸດທົດສະນິຍົມ
    prefix: '₭ ',                   // ເພີ່ມເຄື່ອງໝາຍ "₭ " ເປັນຄໍານໍາຫນ້າ
    rawValueTrimPrefix: true,       // ຕັດຄໍານໍາຫນ້າອອກຈາກຄ່າດິບ
};

// --- Methods ---

// ຟັງຊັນດຶງຂໍ້ມູນສິນຄ້າຈາກ API
const fetchProducts = async () => {
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
        };
        const response = await apiStore.fetch('/products', params, 'products'); // ເອີ້ນ API
        products.value = response.data; // ອັບເດດລາຍການສິນຄ້າ
        Object.assign(pagination.value, { currentPage: response.current_page, totalPages: response.last_page, perPage: response.per_page, total: response.total }); // ອັບເດດ pagination
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນສິນຄ້າ');
    }
};

// ຟັງຊັນດຶງຂໍ້ມູນເບື້ອງຕົ້ນ (ໝວດໝູ່ ແລະ ຫົວໜ່ວຍ)
const fetchInitialData = async () => {
    try {
        const [cats, uns] = await Promise.all([
            apiStore.fetch('/categories', {per_page: -1}, 'cats'), // ດຶງໝວດໝູ່ທັງໝົດ
            apiStore.fetch('/units', {per_page: -1}, 'units'),     // ດຶງຫົວໜ່ວຍທັງໝົດ
        ]);
        categories.value = cats.data || cats; // ອັບເດດລາຍການໝວດໝູ່
        units.value = uns.data || uns;         // ອັບເດດລາຍການຫົວໜ່ວຍ
    } catch (error) {
         modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນໝວດໝູ່/ຫົວໜ່ວຍ');
    }
};

// ຟັງຊັນຈັດການການຈັດຮຽງຕາຕະລາງ
const handleSort = (column) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'; // ສະຫຼັບທິດທາງການຈັດຮຽງ
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc'; // ຕັ້ງຄ່າການຈັດຮຽງໃໝ່ເປັນຂຶ້ນ
    }
    fetchProducts(); // ດຶງຂໍ້ມູນສິນຄ້າໃໝ່
};

// ຟັງຊັນຈັດການການປ່ຽນໜ້າ
const handlePageChange = (page) => { pagination.value.currentPage = page; fetchProducts(); };
// ຟັງຊັນຈັດການການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const handlePerPageChange = (perPage) => { pagination.value.perPage = perPage; pagination.value.currentPage = 1; fetchProducts(); };

// ຟັງຊັນເປີດ Modal ເພີ່ມ/ແກ້ໄຂສິນຄ້າ
const openAddEditModal = () => {
    form.value = { id: null, name: '', description: '', category_id: '', unit_id: '', selling_price: 0 }; // ຣີເຊັດຟອມ
    resetImage();       // ຣີເຊັດຮູບພາບ
    addEditProductModal.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນປິດ Modal
const closeModal = () => addEditProductModal.value.close();

// ຟັງຊັນບັນທຶກສິນຄ້າ (ເພີ່ມໃໝ່ ຫຼື ແກ້ໄຂ)
const saveProduct = async () => {
    const formData = new FormData();
    // ເພີ່ມຂໍ້ມູນຟອມເຂົ້າໃນ FormData
    Object.keys(form.value).forEach(key => {
        if(form.value[key] !== null) formData.append(key, form.value[key]);
    });
    if (imageFile.value) formData.append('image', imageFile.value); // ເພີ່ມໄຟລ໌ຮູບພາບ

    try {
        if (form.value.id) {
            formData.append('_method', 'PUT'); // ສໍາລັບການອັບເດດດ້ວຍ FormData ໃນ Laravel
            await apiStore.post(`/products/${form.value.id}`, formData, 'productsSave'); // ເອີ້ນ API ອັບເດດ
            modalStore.showSuccessAlert('ສິນຄ້າໄດ້ຖືກແກ້ໄຂສຳເລັດ!','ສຳເລັດ',null,1000);
        } else {
            await apiStore.post('/products', formData, 'productsSave'); // ເອີ້ນ API ເພີ່ມໃໝ່
            modalStore.showSuccessAlert('ສິນຄ້າໄດ້ຖືກເພີ່ມສຳເລັດ!','ສຳເລັດ',null,1000);
        }
        closeModal();       // ປິດ Modal
        await fetchProducts(); // ໂຫຼດລາຍການສິນຄ້າຄືນໃໝ່
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ການບັນທຶກຂໍ້ມູນຜິດພາດ!');
    }
};

// ຟັງຊັນແກ້ໄຂສິນຄ້າ (ໂຫຼດຂໍ້ມູນສິນຄ້າເຂົ້າໃນຟອມ ແລະເປີດ Modal)
const editProduct = (product) => {
    form.value = { ...product }; // ໂຫຼດຂໍ້ມູນສິນຄ້າ
    resetImage();       // ຣີເຊັດຮູບພາບ
    imagePreview.value = apiStore.getFullImageUrl(product.image_url); // ສະແດງຮູບພາບທີ່ມີຢູ່ແລ້ວ
    addEditProductModal.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນລຶບສິນຄ້າ
const deleteProduct = (id) => {
    modalStore.showConfirm('ທ່ານຕ້ອງການລຶບສິນຄ້ານີ້ບໍ?', 'ຢືນຢັນການລຶບ', async () => {
        try {
            await apiStore.destroy('/products', id, 'productsDelete'); // ເອີ້ນ API ລຶບ
            modalStore.showSuccessAlert('ສິນຄ້າໄດ້ຖືກລຶບສຳເລັດ!','ສຳເລັດ',null,1000);
            await fetchProducts(); // ໂຫຼດລາຍການສິນຄ້າຄືນໃໝ່
        } catch (error) {
            modalStore.showErrorAlert(apiStore.error, 'ການລຶບຂໍ້ມູນຜິດພາດ!');
        }
    });
};

// ຟັງຊັນເປີດ File Input ເມື່ອຄລິກພື້ນທີ່ຮູບພາບ
const triggerFileInput = () => imageInput.value.click();
// ຟັງຊັນຈັດການການປ່ຽນແປງຂອງໄຟລ໌ (ເມື່ອເລືອກຮູບພາບ)
const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        imageFile.value = file;
        imagePreview.value = URL.createObjectURL(file); // ສ້າງ preview ຮູບພາບ
    }
};
// ຟັງຊັນຈັດການການວາງໄຟລ໌ (Drag and Drop)
const handleFileDrop = (e) => {
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        imageFile.value = file;
        imagePreview.value = URL.createObjectURL(file); // ສ້າງ preview ຮູບພາບ
    }
};
// ຟັງຊັນຣີເຊັດຮູບພາບ
const resetImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
    if (imageInput.value) imageInput.value.value = ''; // ລຶບຄ່າໃນ input ໄຟລ໌
};

// --- Lifecycle Hook ---
// ເອີ້ນໃຊ້ເມື່ອ Component ຖືກ Mounted
onMounted(() => {
    fetchProducts();    // ດຶງຂໍ້ມູນສິນຄ້າ
    fetchInitialData(); // ດຶງຂໍ້ມູນເບື້ອງຕົ້ນ (ໝວດໝູ່, ຫົວໜ່ວຍ)
});

// --- Watchers ---
let debounceTimer; // ຕົວປ່ຽນສຳລັບ debounce timer
// ຕິດຕາມການປ່ຽນແປງຂອງ searchQuery ເພື່ອເຮັດການຄົ້ນຫາແບບ debounce
watch(searchQuery, () => {
    clearTimeout(debounceTimer); // ລຶບ timer ເກົ່າ
    debounceTimer = setTimeout(() => {
        pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດ
        fetchProducts(); // ດຶງຂໍ້ມູນສິນຄ້າໃໝ່
    }, 500); // ຊັກຊ້າ 500ms
});
</script>

