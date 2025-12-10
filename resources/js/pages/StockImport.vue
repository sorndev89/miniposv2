<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ນໍາເຂົ້າສິນຄ້າ</h1>
            <div class="flex items-center">
                 <label class="input me-2">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></g></svg>
                    <input type="search" v-model="searchQuery" placeholder="ຄົ້ນຫາຊື່ສິນຄ້າ..." />
                </label>
                <button class="btn btn-dash btn-success" @click="openImportModal">
                    <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                    ເພີ່ມການນຳເຂົ້າ
                </button>
            </div>
        </div>

        <!-- Imports List -->
        <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mb-4">
            <div v-if="apiStore.isLoading('imports')" class="flex justify-center items-center p-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-lg text-gray-600 dark:text-gray-400 ml-3">ກຳລັງໂຫຼດຂໍ້ມູນ...</p>
            </div>
            <table v-else class="table">
                <thead>
                    <tr>
                        <th @click="handleSort('id')" class="cursor-pointer"># <span v-if="sortBy === 'id'"><ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/><ChevronDownIcon v-else class="w-4 h-4 inline"/></span></th>
                        <th @click="handleSort('import_date')" class="cursor-pointer">ວັນທີ່ນຳເຂົ້າ <span v-if="sortBy === 'import_date'"><ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/><ChevronDownIcon v-else class="w-4 h-4 inline"/></span></th>
                        <th>ສິນຄ້າ</th>
                        <th>ຈຳນວນ</th>
                        <th>ລາຄາທຶນ</th>
                        <th>ລວມເງິນ</th>
                        <th>ຜູ້ນຳເຂົ້າ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in imports" :key="item.id">
                        <th>{{ item.id }}</th>
                        <td>{{ apiStore.formatDate(item.import_date) }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                        <img :src="apiStore.getFullImageUrl(item.product?.image_url)" :alt="item.product?.name" />
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold">{{ item.product?.name }}</div>
                                    <div class="text-sm opacity-50">{{ item.product?.category?.name || 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ item.quantity }} {{ item.product?.unit?.name || 'N/A' }}</td>
                        <td>₭{{ apiStore.formatNumber(item.cost_price) }}</td>
                        <td>₭{{ apiStore.formatNumber(item.total_cost) }}</td>
                        <td>{{ item.importer?.name || 'N/A' }}</td>
                         <td>
                            <div class="flex items-center space-x-2">
                                <button @click="editImport(item)" class="btn btn-sm btn-square btn-ghost text-blue-500"><PencilSquareIcon class="w-5 h-5" /></button>
                                <button @click="deleteImport(item.id)" class="btn btn-sm btn-square btn-ghost text-red-500"><TrashIcon class="w-5 h-5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
             <div v-if="imports.length === 0 && !apiStore.isLoading('imports')" class="text-center py-4 text-gray-500">
                ບໍ່ມີຂໍ້ມູນການນຳເຂົ້າ.
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
    </div>

    <!-- Import Modal -->
    <dialog id="ImportStockModal" class="modal" ref="importStockModalRef">
        <div class="modal-box p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ແບບຟອມນຳເຂົ້າສິນຄ້າ</h3>
            <form @submit.prevent="saveImport">
                <div class="grid grid-cols-1 gap-4">
                     <div>
                        <label for="product_select" class="label">ເລືອກສິນຄ້າ</label>
                        <select id="product_select" v-model="form.product_id" class="select select-bordered w-full" required :disabled="!!form.id">
                            <option disabled value="">ເລືອກສິນຄ້າ</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} ({{ product.unit.name }})</option>
                        </select>
                    </div>

                    <div>
                        <label for="import_quantity" class="label">ຈຳນວນທີ່ນຳເຂົ້າ</label>
                        <input type="number" id="import_quantity" v-model.number="form.quantity" class="input input-bordered w-full" placeholder="0" min="1" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="import_cost" class="label">ລາຄາຕົ້ນທຶນຕໍ່ໜ່ວຍ</label>
                            <Cleave v-model="form.cost_price" :options="cleaveOptions" class="input input-bordered w-full" required />
                        </div>
                        <div>
                            <label for="import_selling_price" class="label">ລາຄາຂາຍໃໝ່</label>
                           <Cleave v-model="form.selling_price" :options="cleaveOptions" class="input input-bordered w-full" required />
                        </div>
                    </div>
                    
                    <div>
                        <label for="import_description" class="label">ລາຍລະອຽດ (ຖ້າມີ)</label>
                        <textarea id="import_description" v-model="form.description" class="textarea textarea-bordered w-full" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-action mt-6">
                    <button type="submit" class="btn btn-primary" :disabled="apiStore.isLoading('importsSave')">
                        <span v-if="apiStore.isLoading('importsSave')" class="loading loading-spinner"></span>
                        <ArrowDownOnSquareIcon v-else class="w-5 h-5 me-2" /> ບັນທຶກ
                    </button>
                    <button type="button" class="btn" @click="closeModal"><XMarkIcon class="w-5 h-5 me-2" /> ຍົກເລີກ</button>
                </div>
            </form>
        </div>
    </dialog>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { ArrowDownTrayIcon, XMarkIcon, ArrowDownOnSquareIcon, ChevronUpIcon, ChevronDownIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/20/solid';
import Pagination from '../components/Pagination.vue';
import { useApiStore } from '../stores/api';
import { useModalStore } from '../stores/modal';
import Cleave from 'vue-cleave-component';

// StockImport.vue
// Component ນີ້ຈັດການການສະແດງຜົນ, ການເພີ່ມ, ການແກ້ໄຂ, ແລະການລຶບລາຍການນຳເຂົ້າສິນຄ້າ.
// ມັນໃຊ້ API Store ເພື່ອຕິດຕໍ່ກັບ Backend ແລະ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ.

// --- State Variables ---
const imports = ref([]); // ລາຍການນຳເຂົ້າສິນຄ້າທີ່ດຶງມາຈາກ API
const products = ref([]); // ລາຍການສິນຄ້າທັງໝົດສຳລັບເລືອກໃນຟອມນຳເຂົ້າ
const searchQuery = ref(''); // ຄ່າການຄົ້ນຫາ
const sortBy = ref('import_date'); // ຖັນທີ່ໃຊ້ໃນການຈັດຮຽງ
const sortDirection = ref('desc'); // ທິດທາງການຈັດຮຽງ ('asc' ຫຼື 'desc')

// ຂໍ້ມູນຟອມສຳລັບການເພີ່ມ/ແກ້ໄຂການນຳເຂົ້າ
const form = ref({
    id: null,
    product_id: '',
    quantity: 1,
    cost_price: 0,
    selling_price: 0,
    description: '',
});

// ສະຖານະການຈັດໜ້າ
const pagination = ref({ currentPage: 1, totalPages: 1, perPage: 10, total: 0 });

// References ຫາ Modal ແລະ Stores
const importStockModalRef = ref(null); // Reference ຫາ element dialog modal
const apiStore = useApiStore();         // ເລີ່ມຕົ້ນ API Store
const modalStore = useModalStore();     // ເລີ່ມຕົ້ນ Modal Store

// Cleave.js options for currency formatting (ຕົວເລືອກ Cleave.js ສຳລັບການຈັດຮູບແບບສະກຸນເງິນ)
const cleaveOptions = {
    numeral: true,
    numeralPositiveOnly: true,
    numeralThousandsGroupStyle: 'thousand',
    numeralDecimalScale: 2,
    prefix: '₭ ',
    rawValueTrimPrefix: true,
};

// --- Methods ---

// ຟັງຊັນດຶງຂໍ້ມູນການນຳເຂົ້າສິນຄ້າຈາກ API
const fetchStockImports = async () => {
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
        };
        const response = await apiStore.fetch('/stock-imports', params, 'imports'); // ເອີ້ນ API
        imports.value = response.data; // ອັບເດດລາຍການນຳເຂົ້າ
        Object.assign(pagination.value, { currentPage: response.current_page, totalPages: response.last_page, perPage: response.per_page, total: response.total }); // ອັບເດດ pagination
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນການນຳເຂົ້າສິນຄ້າ');
    }
};

// ຟັງຊັນດຶງຂໍ້ມູນເບື້ອງຕົ້ນ (ລາຍການສິນຄ້າ)
const fetchInitialData = async () => {
    try {
        const prods = await apiStore.fetch('/products', { per_page: -1 }, 'prods_list'); // ດຶງສິນຄ້າທັງໝົດ
        products.value = prods.data || prods; // ອັບເດດລາຍການສິນຄ້າ
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງລາຍການສິນຄ້າ');
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
    fetchStockImports(); // ດຶງຂໍ້ມູນການນຳເຂົ້າໃໝ່
};

// ຟັງຊັນຈັດການການປ່ຽນໜ້າ
const handlePageChange = (page) => { pagination.value.currentPage = page; fetchStockImports(); };
// ຟັງຊັນຈັດການການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const handlePerPageChange = (perPage) => { pagination.value.perPage = perPage; pagination.value.currentPage = 1; fetchStockImports(); };

// ຟັງຊັນເປີດ Modal ນຳເຂົ້າສິນຄ້າ
const openImportModal = () => {
    form.value = { id: null, product_id: '', quantity: 1, cost_price: 0, selling_price: 0, description: '' }; // ຣີເຊັດຟອມ
    importStockModalRef.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນປິດ Modal
const closeModal = () => importStockModalRef.value.close();

// ຟັງຊັນບັນທຶກການນຳເຂົ້າ (ເພີ່ມໃໝ່ ຫຼື ແກ້ໄຂ)
const saveImport = async () => {
    const payload = { ...form.value };
    
    // ປ່ຽນ string ສະກຸນເງິນທີ່ຖືກຈັດຮູບແບບກັບຄືນເປັນຕົວເລກ
    payload.cost_price = Number(String(payload.cost_price).replace(/[^0-9.-]+/g,""));
    payload.selling_price = Number(String(payload.selling_price).replace(/[^0-9.-]+/g,""));

    try {
        if (form.value.id) {
            await apiStore.put(`/stock-imports/${form.value.id}`, payload, 'importsSave'); // ເອີ້ນ API ອັບເດດ
            modalStore.showSuccessAlert('ແກ້ໄຂການນຳເຂົ້າສຳເລັດ!', 'ສຳເລັດ', null, 1000);
        } else {
            await apiStore.post('/stock-imports', payload, 'importsSave'); // ເອີ້ນ API ເພີ່ມໃໝ່
            modalStore.showSuccessAlert('ນຳເຂົ້າສິນຄ້າສຳເລັດ!', 'ສຳເລັດ', null, 1000);
        }
        closeModal();           // ປິດ Modal
        await fetchStockImports(); // ໂຫຼດລາຍການນຳເຂົ້າຄືນໃໝ່
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ການບັນທຶກຂໍ້ມູນຜິດພາດ!');
    }
};

// ຟັງຊັນແກ້ໄຂການນຳເຂົ້າ (ໂຫຼດຂໍ້ມູນການນຳເຂົ້າເຂົ້າໃນຟອມ ແລະເປີດ Modal)
const editImport = (stockImport) => {
    form.value = { 
        id: stockImport.id,
        product_id: stockImport.product_id,
        quantity: stockImport.quantity,
        cost_price: stockImport.cost_price,
        selling_price: stockImport.product.selling_price, // ດຶງລາຄາຂາຍປັດຈຸບັນຈາກສິນຄ້າ
        description: stockImport.description || ''
     };
    importStockModalRef.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນລຶບການນຳເຂົ້າ
const deleteImport = (id) => {
    modalStore.showConfirm('ທ່ານຕ້ອງການລຶບບິນນຳເຂົ້ານີ້ບໍ?', 'ການດຳເນີນການນີ້ຈະສົ່ງຜົນໃຫ້ຈຳນວນສິນຄ້າໃນສະຕັອກຫຼຸດລົງຕາມຈຳນວນທີ່ນຳເຂົ້າ.', async () => {
        try {
            await apiStore.destroy('/stock-imports', id, 'importsDelete'); // ເອີ້ນ API ລຶບ
            modalStore.showSuccessAlert('ລຶບການນຳເຂົ້າສຳເລັດ!', 'ສຳເລັດ', null, 1000);
            await fetchStockImports(); // ໂຫຼດລາຍການນຳເຂົ້າຄືນໃໝ່
        } catch (error) {
            modalStore.showErrorAlert(apiStore.error, 'ການລຶບຂໍ້ມູນຜິດພາດ!');
        }
    });
};

// --- Lifecycle Hook ---
// ເອີ້ນໃຊ້ເມື່ອ Component ຖືກ Mounted
onMounted(() => {
    fetchStockImports();    // ດຶງຂໍ້ມູນການນຳເຂົ້າ
    fetchInitialData();     // ດຶງຂໍ້ມູນເບື້ອງຕົ້ນ (ລາຍການສິນຄ້າ)
});

// --- Watchers ---
let debounceTimer; // ຕົວປ່ຽນສຳລັບ debounce timer
// ຕິດຕາມການປ່ຽນແປງຂອງ searchQuery ເພື່ອເຮັດການຄົ້ນຫາແບບ debounce
watch(searchQuery, () => {
    clearTimeout(debounceTimer); // ລຶບ timer ເກົ່າ
    debounceTimer = setTimeout(() => {
        pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດ
        fetchStockImports(); // ດຶງຂໍ້ມູນການນຳເຂົ້າໃໝ່
    }, 500); // ຊັກຊ້າ 500ms
});

// ຕິດຕາມການປ່ຽນແປງຂອງ product_id ໃນຟອມ ເພື່ອອັບເດດລາຄາຂາຍອັດຕະໂນມັດ
watch(() => form.value.product_id, (newProductId) => {
    if (!form.value.id && newProductId) { // ສະເພາະການນຳເຂົ້າໃໝ່
        const selectedProduct = products.value.find(p => p.id === newProductId);
        if (selectedProduct) {
            form.value.selling_price = selectedProduct.selling_price; // ຕັ້ງລາຄາຂາຍຕາມລາຄາຂາຍຂອງສິນຄ້າ
        }
    }
});
</script>


