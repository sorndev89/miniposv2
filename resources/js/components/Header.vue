<template>
    <!-- ແຖບນໍາທາງດ້ານເທິງ (Header) ຂອງລະບົບ -->
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <!-- ປຸ່ມສະຫຼັບ Sidebar (ສະແດງສະເພາະໃນໜ້າຈໍຂະໜາດນ້ອຍ) -->
                    <button @click="uiStore.toggleSidebar()" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    <!-- ໂລໂກ້/ຊື່ແອັບ -->
                    <router-link to="/" class="flex ms-2 md:me-24">
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">MiniPOS</span>
                    </router-link>
                </div>
                <div class="flex items-center">
              
                    <!-- ພື້ນທີ່ສຳລັບເມນູ dropdown ຂອງຜູ້ໃຊ້ -->
                    <div ref="dropdownRef" class="relative flex items-center ms-3">
                        <!-- ປຸ່ມ Icon ຜູ້ໃຊ້ສຳລັບສະຫຼັບ dropdown -->
                        <button type="button" @click="isDropdownOpen = !isDropdownOpen" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false">
                            <span class="sr-only">Open user menu</span>
                            <UserCircleIcon class="w-8 h-8 text-gray-400" />
                        </button>
                        <!-- ເມນູ dropdown ຜູ້ໃຊ້ -->
                        <div v-show="isDropdownOpen" class="absolute right-0 mt-5 top-full z-50 w-48 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600">
                            <div class="px-4 py-3">
                                <span class="block text-sm text-gray-900 dark:text-white">ຊື່ຜູ້ໃຊ້</span>
                                <span class="block text-sm font-medium text-gray-500 truncate dark:text-gray-400">email@flowbite.com</span>
                            </div>
                            <ul class="py-1" aria-labelledby="user-menu-button">
                                <li>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">ແຜງຄວບຄຸມ</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">ການຕັ້ງຄ່າ</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">ຫາເງິນ</a>
                                </li>
                                <li>
                                    <!-- ປຸ່ມອອກຈາກລະບົບ -->
                                    <button @click="logout" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">ອອກຈາກລະບົບ</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'; // ນຳເຂົ້າ Store ສຳລັບການຈັດການການຢືນຢັນຕົວຕົນ
import { useRouter } from 'vue-router'; // ນຳເຂົ້າ useRouter ເພື່ອໃຊ້ສຳລັບການນຳທາງ
import { useUiStore } from '../stores/ui'; // ນຳເຂົ້າ UI Store ສຳລັບການຈັດການ UI
import { ref, onMounted, onUnmounted } from 'vue'; // ນຳເຂົ້າຟັງຊັນ Vue ທີ່ຈຳເປັນ (ref, onMounted, onUnmounted)
import { UserCircleIcon } from '@heroicons/vue/20/solid'; // ນຳເຂົ້າ Icon ຮູບວົງມົນຜູ້ໃຊ້

// Header.vue
// Component ນີ້ບັນຈຸແຖບນໍາທາງຫຼັກ ແລະ ຟັງຊັນການອອກຈາກລະບົບ.
// ປັດຈຸບັນມັນມີປຸ່ມສຳລັບສະຫຼັບການເບິ່ງເຫັນຂອງ sidebar ໂດຍໃຊ້ UI store.

const authStore = useAuthStore(); // ໃຊ້ Auth Store
const uiStore = useUiStore(); // ໃຊ້ UI Store
const router = useRouter(); // ໃຊ້ Router

const isDropdownOpen = ref(false); // ຕົວປ່ຽນ reactive ເພື່ອຄວບຄຸມການເບິ່ງເຫັນຂອງ dropdown
const dropdownRef = ref(null); // Reference ເຖິງ dropdown element ໃນ template

// ຟັງຊັນສຳລັບການອອກຈາກລະບົບ
const logout = async () => {
    await authStore.logout();
    // ການກະທຳ authStore.logout ໄດ້ຈັດການການປ່ຽນເສັ້ນທາງໄປໜ້າ login ແລ້ວ
};

// ຈັດການການຄລິກນອກພື້ນທີ່ dropdown ເພື່ອປິດມັນ
const handleClickOutside = (event) => {
    // ຖ້າ dropdownRef ມີຄ່າ ແລະ event ບໍ່ໄດ້ເກີດຂຶ້ນພາຍໃນ dropdownRef
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false; // ປິດ dropdown
    }
};

// Hook ເມື່ອ Component ຖືກ Mounted (ເມື່ອ Component ຖືກສ້າງຂຶ້ນໃນ DOM)
onMounted(() => {
    document.addEventListener('click', handleClickOutside); // ເພີ່ມ Event Listener ໃສ່ document ທັງໝົດ
});

// Hook ເມື່ອ Component ຖືກ Unmounted (ເມື່ອ Component ຖືກທໍາລາຍຈາກ DOM)
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside); // ລຶບ Event Listener ເພື່ອປ້ອງກັນ Memory Leak
});
</script>

<style scoped>
/* Scoped styles for Header.vue if needed */
</style>