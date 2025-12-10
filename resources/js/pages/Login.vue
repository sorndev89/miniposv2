<template>
   
        <div class="card w-full max-w-sm shadow-xl bg-base-100 dark:bg-gray-800 text-base-content dark:text-gray-200">
            <div class="card-body">
                <!-- Logo/Icon -->
                <div class="flex justify-center mb-6">
                    <ShoppingBagIcon class="w-30 h-30 text-primary dark:text-blue-400" />
                </div>
                <h5 class="card-title text-xl font-medium text-center text-gray-900 dark:text-white mb-6">ເຂົ້າສູ່ລະບົບ</h5>
                <form class="space-y-4" @submit.prevent="handleLogin">
                    <!-- Email Input -->
                    <div class="form-control">
                        <label for="email" class="label">
                            <span class="label-text text-gray-700 dark:text-gray-300">ອີເມວ</span>
                        </label>
                        <input type="email" name="email" id="email" v-model="email" placeholder="name@company.com"
                            class="input input-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
                    </div>

                    <!-- Password Input -->
                    <div class="form-control">
                        <label for="password" class="label">
                            <span class="label-text text-gray-700 dark:text-gray-300">ລະຫັດຜ່ານ</span>
                        </label>
                        <input type="password" name="password" id="password" v-model="password" placeholder="••••••••"
                            class="input input-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" v-model="rememberMe" class="checkbox checkbox-xs checkbox-primary" />
                            <span class="label-text text-gray-700 dark:text-gray-300">ຈື່ຂ້ອຍໄວ້</span>
                        </label>
                    </div>

                    <!-- Login Button -->
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full" :disabled="apiStore.isLoading('login')">
                            <span v-if="apiStore.isLoading('login')" class="loading loading-spinner"></span>
                            ເຂົ້າສູ່ລະບົບ
                        </button>
                    </div>

                    <!-- Error Message (Now handled by modalStore) -->
                </form>
            </div>
        </div>
    
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useApiStore } from '../stores/api';     // ນຳເຂົ້າ api store
import { useModalStore } from '../stores/modal'; // ນຳເຂົ້າ modal store
import { ShoppingBagIcon } from '@heroicons/vue/20/solid'; // ນຳເຂົ້າ Icon

// Login.vue
// ໜ້ານີ້ສະໜອງຟອມເຂົ້າສູ່ລະບົບແບບງ່າຍດາຍສຳລັບຜູ້ໃຊ້ເພື່ອຢືນຢັນຕົວຕົນ.

const authStore = useAuthStore();     // ເລີ່ມຕົ້ນ Auth Store
const apiStore = useApiStore();       // ເລີ່ມຕົ້ນ Api Store
const modalStore = useModalStore();   // ເລີ່ມຕົ້ນ Modal Store

const email = ref('admin@example.com');   // ຕົວປ່ຽນ reactive ສໍາລັບອີເມວຜູ້ໃຊ້
const password = ref('password');         // ຕົວປ່ຽນ reactive ສໍາລັບລະຫັດຜ່ານຜູ້ໃຊ້
const rememberMe = ref(false);            // ຕົວປ່ຽນ reactive ສໍາລັບຕົວເລືອກ "ຈື່ຂ້ອຍໄວ້"

// ຟັງຊັນຈັດການການເຂົ້າສູ່ລະບົບ
const handleLogin = async () => {
    try {
        apiStore.clearError(); // ລຶບລ້າງຂໍ້ຜິດພາດ API ກ່ອນໜ້າ (ຖ້າມີ)
        const success = await authStore.login({
            email: email.value,
            password: password.value,
            remember: rememberMe.value,
        });

        if (!success) {
            // ກໍລະນີນີ້ຄວນຖືກຈັດການໂດຍ authStore ໂດຍການ redirect,
            // ຫຼືຖິ້ມ error ທີ່ຖືກຈັບຂ້າງລຸ່ມນີ້.
            // ຖ້າ authStore.login ສົ່ງຄືນ false ໂດຍບໍ່ມີການຖິ້ມ error, ມັນໝາຍຄວາມວ່າການເຂົ້າສູ່ລະບົບລົ້ມເຫຼວ.
            modalStore.showErrorAlert('ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ', 'ການເຂົ້າສູ່ລະບົບລົ້ມເຫຼວ');
        }
    } catch (err) {
        // ຈັບຂໍ້ຜິດພາດການກວດສອບ ຫຼື ຂໍ້ຜິດພາດ API ອື່ນໆຈາກ authStore.login
        const errorMessage = apiStore.error || 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່.';
        modalStore.showErrorAlert(errorMessage, 'ການເຂົ້າສູ່ລະບົບລົ້ມເຫຼວ');
    }
};
</script>

<style scoped>
/* Scoped styles for Login.vue if needed */
</style>
