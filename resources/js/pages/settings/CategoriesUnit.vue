<template>
   
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">ຈັດການໝວດໝູ່ ແລະ ຫົວໜ່ວຍ</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Category Manager Component -->
            <div>
                <CategoryManager />
            </div>

            <!-- Unit Manager Component -->
            <div>
                <UnitManager />
            </div>
        </div>
  
</template>

<script setup>
import CategoryManager from '../../components/CategoryManager.vue'; // ນຳເຂົ້າ Component ສຳລັບຈັດການໝວດໝູ່
import UnitManager from '../../components/UnitManager.vue';     // ນຳເຂົ້າ Component ສຳລັບຈັດການຫົວໜ່ວຍ

// CategoriesUnit.vue
// ໜ້ານີ້ເຮັດໜ້າທີ່ເປັນ container ເພື່ອລວມ Component CategoryManager ແລະ UnitManager ເຂົ້າກັນ.
// ມັນຊ່ວຍໃຫ້ຜູ້ໃຊ້ສາມາດຈັດການທັງໝວດໝູ່ ແລະ ຫົວໜ່ວຍຂອງສິນຄ້າໄດ້ໃນໜ້າດຽວ.
</script>

<style scoped>
/* Scoped styles for CategoriesUnit.vue */
</style>