<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ຈັດການຜູ້ໃຊ້</h1>
            <div class=" flex">
                <label class="input me-2">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g
                        stroke-linejoin="round"
                        stroke-linecap="round"
                        stroke-width="2.5"
                        fill="none"
                        stroke="currentColor"
                        >
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                        </g>
                    </svg>
                    <input type="search" v-model="searchQuery" required placeholder="ຄົ້ນຫາ..." />
                    </label>
                <button class="btn btn-dash btn-info" @click="openAddEditModal">
                    <PlusCircleIcon class="w-6 h-6 me-2" /> ເພີ່ມຜູ້ໃຊ້ໃໝ່
                </button>
            </div>
        </div>

        <!-- User List -->
        <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 mb-4">
            <div v-if="apiStore.isLoading('users')" class="flex justify-center items-center p-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-lg text-gray-600 dark:text-gray-400 ml-3">ກຳລັງໂຫຼດຜູ້ໃຊ້...</p>
            </div>
            <table v-else class="table">
                <thead>
                    <tr>
                        <th @click="handleSort('id')" class="cursor-pointer">
                            #
                            <span v-if="sortBy === 'id'" class="ms-1">
                                <ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/>
                                <ChevronDownIcon v-else class="w-4 h-4 inline"/>
                            </span>
                        </th>
                        <th>ຮູບໂປຣໄຟລ໌</th>
                        <th @click="handleSort('name')" class="cursor-pointer">
                            ຊື່
                             <span v-if="sortBy === 'name'" class="ms-1">
                                <ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/>
                                <ChevronDownIcon v-else class="w-4 h-4 inline"/>
                            </span>
                        </th>
                        <th @click="handleSort('email')" class="cursor-pointer">
                            ອີເມວ
                            <span v-if="sortBy === 'email'" class="ms-1">
                                <ChevronUpIcon v-if="sortDirection === 'asc'" class="w-4 h-4 inline"/>
                                <ChevronDownIcon v-else class="w-4 h-4 inline"/>
                            </span>
                        </th>
                        <th>ຕຳແໜ່ງ</th>
                        <th>ສະຖານະ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <th>{{ user.id }}</th>
                        <td>
                            <div class="avatar">
                                <div class="mask mask-squircle w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-xl font-bold text-gray-700 dark:text-gray-200">
                                    <img v-if="user.avatar_url" :src="apiStore.getFullImageUrl(user.avatar_url)" :alt="user.name" />
                                    <span v-else>{{ user.name?.charAt(0) || '?' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ user.role }}</td>
                        <td>
                            <span :class="{'badge badge-success': user.status === 'active', 'badge badge-error': user.status === 'inactive'}">
                                {{ user.status === 'active' ? 'ເຄື່ອນໄຫວ' : 'ບໍ່ເຄື່ອນໄຫວ' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button @click="editUser(user)" class="btn btn-sm btn-square btn-ghost text-blue-500 hover:text-blue-700">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <button @click="deleteUser(user.id)" class="btn btn-sm btn-square btn-ghost text-red-500 hover:text-red-700">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="users.length === 0 && !apiStore.isLoading('users')" class="text-center py-4 text-gray-500 dark:text-gray-400">
                ບໍ່ມີຂໍ້ມູນຜູ້ໃຊ້.
            </div>
        </div>

        <!-- Pagination Placeholder -->
        <Pagination
            :currentPage="pagination.currentPage"
            :totalPages="pagination.totalPages"
            :perPage="pagination.perPage"
            :total="pagination.total"
            @page-changed="handlePageChange"
            @per-page-changed="handlePerPageChange"
        />

        <!-- Add/Edit User Modal -->
        <dialog id="AddEditUser" class="modal" ref="addEditUserModal">
            <div class="modal-box p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ແບບຟອມຜູ້ໃຊ້</h3>
                <form @submit.prevent="saveUser">
                    <!-- Avatar Upload -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຮູບໂປຣໄຟລ໌</label>
                        <div
                            @click="triggerFileInput"
                            @dragover.prevent @dragenter.prevent @drop.prevent="handleFileDrop"
                            class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                        >
                            <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" accept="image/*">
                            <div v-if="avatarPreview" class="flex justify-center items-center">
                                <img :src="avatarPreview" class="w-32 h-32 object-cover rounded-full">
                            </div>
                            <div v-else class="text-gray-500">
                                <p>ລາກ & ວາງ ຮູບ ຫຼື ຄລິກເພື່ອເລືອກ</p>
                                <p class="text-xs mt-1">ຮອງຮັບໄຟລ໌ PNG, JPG (ສູງສຸດ 10MB)</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- User Name -->
                        <div>
                            <label for="user_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຊື່</label>
                            <input type="text" id="user_name" v-model="form.name" class="input input-bordered w-full" placeholder="ຊື່ຜູ້ໃຊ້" required>
                        </div>
                        <!-- User Email -->
                        <div>
                            <label for="user_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ອີເມວ</label>
                            <input type="email" id="user_email" v-model="form.email" class="input input-bordered w-full" placeholder="ອີເມວ" required>
                        </div>
                        <!-- User Password -->
                        <div>
                            <label for="user_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ລະຫັດຜ່ານ</label>
                            <input type="password" id="user_password" v-model="form.password" class="input input-bordered w-full" :required="!form.id">
                            <p v-if="form.id" class="text-xs text-gray-500 mt-1">ປ່ອຍຫວ່າງໄວ້ ຖ້າບໍ່ຕ້ອງການປ່ຽນລະຫັດຜ່ານ</p>
                        </div>
                        <!-- User Role -->
                        <div>
                            <label for="user_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ຕຳແໜ່ງ</label>
                            <select id="user_role" v-model="form.role" class="select select-bordered w-full" required>
                                <option disabled value="">ເລືອກຕຳແໜ່ງ</option>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <!-- User Status -->
                        <div>
                            <label for="user_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ສະຖານະ</label>
                            <select id="user_status" v-model="form.status" class="select select-bordered w-full" required>
                                <option value="active">ເຄື່ອນໄຫວ</option>
                                <option value="inactive">ບໍ່ເຄື່ອນໄຫວ</option>
                            </select>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-action mt-6">
                        <button type="submit" class="btn btn-primary" :disabled="apiStore.isLoading('usersSave')">
                             <span v-if="apiStore.isLoading('usersSave')" class="loading loading-spinner"></span>
                            <ArrowDownOnSquareIcon v-else class="w-5 h-5 me-2" /> ບັນທຶກ
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
import { ref, onMounted, watch } from 'vue';
import { PlusCircleIcon, PencilSquareIcon, TrashIcon, ArrowDownOnSquareIcon, XMarkIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import Pagination from '../../components/Pagination.vue';
import { useModalStore } from '../../stores/modal';
import { useApiStore } from '../../stores/api';

// Users.vue
// Component ນີ້ຈັດການການສະແດງຜົນ, ການເພີ່ມ, ການແກ້ໄຂ, ແລະການລຶບຜູ້ໃຊ້.
// ມັນໃຊ້ API Store ເພື່ອຕິດຕໍ່ກັບ Backend ແລະ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ.

// --- Search State ---
const searchQuery = ref(''); // ຄ່າການຄົ້ນຫາຜູ້ໃຊ້

// --- Sorting State ---
const sortBy = ref('id'); // ຖັນທີ່ໃຊ້ໃນການຈັດຮຽງ
const sortDirection = ref('asc'); // ທິດທາງການຈັດຮຽງ ('asc' ຫຼື 'desc')

// --- Form Data for the Modal ---
const form = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    role: '',
    status: 'active',
});

// --- Refs for Avatar Handling ---
const avatarFile = ref(null);    // ໄຟລ໌ Avatar ທີ່ເລືອກ
const avatarPreview = ref(null); // URL ຂອງ Avatar ເພື່ອກ່ອນສະແດງ
const fileInput = ref(null);     // Reference ຫາ input ໄຟລ໌

// --- User Data from API ---
const users = ref([]); // ລາຍການຜູ້ໃຊ້ທີ່ດຶງມາຈາກ API

// --- Pagination State ---
const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    perPage: 10,
    total: 0,
});

// --- Modal and Store References ---
const addEditUserModal = ref(null); // Reference ຫາ element dialog modal
const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Modal Store
const apiStore = useApiStore();     // ເລີ່ມຕົ້ນ API Store

// --- Sorting Methods ---
// ຟັງຊັນຈັດການການຈັດຮຽງຕາຕະລາງ
const handleSort = (column) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'; // ສະຫຼັບທິດທາງການຈັດຮຽງ
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc'; // ຕັ້ງຄ່າການຈັດຮຽງໃໝ່ເປັນຂຶ້ນ
    }
    fetchUsers(); // ດຶງຂໍ້ມູນຜູ້ໃຊ້ໃໝ່
};

// --- Avatar Handling Methods ---
// ຟັງຊັນເປີດ File Input ເມື່ອຄລິກພື້ນທີ່ຮູບພາບ
const triggerFileInput = () => {
    fileInput.value.click();
};

// ຟັງຊັນຈັດການການປ່ຽນແປງຂອງໄຟລ໌ (ເມື່ອເລືອກຮູບພາບ)
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        avatarFile.value = file;
        avatarPreview.value = URL.createObjectURL(file); // ສ້າງ preview ຮູບພາບ
    }
};

// ຟັງຊັນຈັດການການວາງໄຟລ໌ (Drag and Drop)
const handleFileDrop = (event) => {
    const file = event.dataTransfer.files[0];
     if (file && file.type.startsWith('image/')) {
        avatarFile.value = file;
        avatarPreview.value = URL.createObjectURL(file); // ສ້າງ preview ຮູບພາບ
    }
};

// ຟັງຊັນຣີເຊັດ Avatar
const resetAvatar = () => {
    avatarFile.value = null;
    avatarPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = ''; // ລຶບຄ່າໃນ input ໄຟລ໌
    }
};

// --- API and CRUD Methods ---
// ຟັງຊັນດຶງຂໍ້ມູນຜູ້ໃຊ້ຈາກ API
async function fetchUsers() {
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
        };

        const response = await apiStore.fetch('/users', params, 'users');
        
        users.value = response.data; // ອັບເດດລາຍການຜູ້ໃຊ້
        pagination.value.currentPage = response.current_page;
        pagination.value.totalPages = response.last_page;
        pagination.value.perPage = response.per_page;
        pagination.value.total = response.total;
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນຜູ້ໃຊ້');
    }
}

// Hook ເມື່ອ Component ຖືກ Mounted
onMounted(() => {
    fetchUsers(); // ດຶງຂໍ້ມູນຜູ້ໃຊ້ເມື່ອ Component ຖືກສ້າງຂຶ້ນ
});

// Debounced search (ຄົ້ນຫາແບບຊັກຊ້າເພື່ອຫຼຸດຜ່ອນການຮ້ອງຂໍ API)
let debounceTimer;
watch(searchQuery, () => {
    clearTimeout(debounceTimer); // ລຶບ timer ເກົ່າ
    debounceTimer = setTimeout(() => {
        pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດ
        fetchUsers(); // ດຶງຂໍ້ມູນຜູ້ໃຊ້ໃໝ່
    }, 500); // ຊັກຊ້າ 500ms
});

// ຟັງຊັນຈັດການການປ່ຽນໜ້າ
const handlePageChange = (page) => {
    pagination.value.currentPage = page;
    fetchUsers(); // ດຶງຂໍ້ມູນຜູ້ໃຊ້ໃໝ່
};

// ຟັງຊັນຈັດການການປ່ຽນຈຳນວນລາຍການຕໍ່ໜ້າ
const handlePerPageChange = (perPage) => {
    pagination.value.perPage = perPage;
    pagination.value.currentPage = 1; // ຣີເຊັດເປັນໜ້າທຳອິດ
    fetchUsers(); // ດຶງຂໍ້ມູນຜູ້ໃຊ້ໃໝ່
};

// ຟັງຊັນເປີດ Modal ເພີ່ມ/ແກ້ໄຂຜູ້ໃຊ້
const openAddEditModal = () => {
    form.value = { 
        id: null, 
        name: '', 
        email: '', 
        password: '', 
        role: '', 
        status: 'active', 
    };
    resetAvatar(); // ຣີເຊັດ Avatar
    addEditUserModal.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນປິດ Modal
const closeModal = () => {
    addEditUserModal.value.close();
};

// ຟັງຊັນບັນທຶກຜູ້ໃຊ້ (ເພີ່ມໃໝ່ ຫຼື ແກ້ໄຂ)
async function saveUser() {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('email', form.value.email);
    formData.append('role', form.value.role);
    formData.append('status', form.value.status);

    if (form.value.password) {
        formData.append('password', form.value.password);
    }

    if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
    }

    try {
        if (form.value.id) {
            // ສໍາລັບການອັບເດດ, ໃຊ້ POST ພ້ອມກັບ _method=PUT ສໍາລັບຄວາມເຂົ້າກັນໄດ້ກັບ FormData
            formData.append('_method', 'PUT');
            await apiStore.post(`/users/${form.value.id}`, formData, 'usersSave');
            modalStore.showSuccessAlert('ອັບເດດຜູ້ໃຊ້ສຳເລັດ!', 'ສຳເລັດ', null, 1000);
        } else {
            // ສໍາລັບການສ້າງໃໝ່, ໃຊ້ POST ແບບມາດຕະຖານ
            await apiStore.post('/users', formData, 'usersSave');
            modalStore.showSuccessAlert('ເພີ່ມຜູ້ໃຊ້ສຳເລັດ!', 'ສຳເລັດ', null, 1000);
        }
        closeModal();          // ປິດ Modal
        await fetchUsers();    // ໂຫຼດຂໍ້ມູນຜູ້ໃຊ້ຄືນໃໝ່
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການບັນທຶກຜູ້ໃຊ້');
    }
}

// ຟັງຊັນແກ້ໄຂຜູ້ໃຊ້ (ໂຫຼດຂໍ້ມູນຜູ້ໃຊ້ເຂົ້າໃນຟອມ ແລະເປີດ Modal)
const editUser = (user) => {
    form.value = { ...user, password: '' }; // ໂຫຼດຂໍ້ມູນຜູ້ໃຊ້ (ຍົກເວັ້ນລະຫັດຜ່ານ)
    resetAvatar(); // ຣີເຊັດ Avatar
    avatarPreview.value = apiStore.getFullImageUrl(user.avatar_url); // ສະແດງ Avatar ທີ່ມີຢູ່ແລ້ວ
    addEditUserModal.value.showModal(); // ສະແດງ Modal
};

// ຟັງຊັນລຶບຜູ້ໃຊ້
const deleteUser = (userId) => {
    modalStore.showConfirm(
        'ທ່ານຕ້ອງການລຶບຜູ້ໃຊ້ນີ້ແທ້ບໍ?',
        'ຢືນຢັນການລຶບ',
        async () => { // Callback ທີ່ຈະຖືກເອີ້ນເມື່ອຜູ້ໃຊ້ຢືນຢັນ
            try {
                await apiStore.destroy(`/users`, userId, 'usersDelete'); // ເອີ້ນ API ເພື່ອລຶບ
                modalStore.showSuccessAlert('ລຶບຜູ້ໃຊ້ສຳເລັດ!', 'ສຳເລັດ', null, 1000);
                await fetchUsers(); // ໂຫຼດຂໍ້ມູນຜູ້ໃຊ້ຄືນໃໝ່
            } catch (error) {
                modalStore.showErrorAlert(apiStore.error, 'ຂໍ້ຜິດພາດໃນການລຶບຜູ້ໃຊ້');
            }
        }
    );
};
</script>