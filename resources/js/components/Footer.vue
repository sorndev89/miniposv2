<template> 
    <footer class="bg-white rounded-lg shadow m-4 dark:bg-gray-800" v-if="route.name!='Pos'"> 
        <div class="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between">
          <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2026 <a href="#" class="hover:underline">MiniPOS™</a> By SornDev. All Rights Reserved.
          </span>
          <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-0">
              <li>
                  <a href="#" class="hover:underline me-4 md:me-6">ກ່ຽວກັບ</a>
              </li>
              <li>
                  <a href="#" class="hover:underline me-4 md:me-6">ນະໂຍບາຍຄວາມເປັນສ່ວນຕົວ</a>
              </li>
              <li>
                  <a href="#" class="hover:underline me-4 md:me-6">ໃບອະນຸຍາດ</a>
              </li>
              <li>
                  <a href="#" class="hover:underline">ຕິດຕໍ່</a>
              </li>
          </ul>
        </div>
    </footer>
</template>

<script setup>
import { useRoute } from 'vue-router'; // ນຳເຂົ້າ useRoute ເພື່ອເຂົ້າເຖິງຂໍ້ມູນ Route ປັດຈຸບັນ

const route = useRoute(); // ໃຊ້ useRoute ເພື່ອໃຫ້ສາມາດກວດສອບຊື່ route ປັດຈຸບັນໄດ້

// Footer.vue
// Component ນີ້ສະໜອງ footer ທົ່ວໄປສຳລັບແອັບພລິເຄຊັນ.
// ມັນໃຊ້ styling ຂອງ Flowbite footer ແລະຈະບໍ່ສະແດງໃນໜ້າ 'Pos'.
</script>

<style scoped>
/* Scoped styles for Footer.vue if needed */
</style>
