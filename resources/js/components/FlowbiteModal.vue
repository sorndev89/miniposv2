<template>
    <dialog :id="modalStore.id" class="modal" :class="{'modal-open': modalStore.isOpen}" ref="flowbiteModalRef">
        <div class="modal-box p-6 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
             :class="{
                 'border-l-4': true,
                 'border-success': modalStore.alertType === 'success',
                 'border-error': modalStore.alertType === 'error',
                 'border-warning': modalStore.alertType === 'warning',
                 'border-info': modalStore.alertType === 'info' || modalStore.type === 'confirm',
             }">
            <h3 class="font-bold text-lg mb-4 flex items-center"
                :class="{
                    'text-success': modalStore.alertType === 'success',
                    'text-error': modalStore.alertType === 'error',
                    'text-warning': modalStore.alertType === 'warning',
                    'text-info': modalStore.alertType === 'info' || modalStore.type === 'confirm',
                }">
                <component :is="modalStore.icon" v-if="modalStore.icon" class="w-6 h-6 me-2" />
                {{ modalStore.title }}
            </h3>
            <p class="py-4 text-gray-700 dark:text-gray-300">{{ modalStore.message }}</p>
            <div class="modal-action">
                <button v-if="modalStore.type === 'confirm'" @click="modalStore.confirmAction()"
                    class="btn"
                    :class="{
                        'btn-success': modalStore.alertType === 'success',
                        'btn-error': modalStore.alertType === 'error',
                        'btn-warning': modalStore.alertType === 'warning',
                        'btn-info': modalStore.alertType === 'info' || modalStore.alertType === 'confirm',
                    }">
                    <CheckIcon class="w-5 h-5 me-2" />
                    {{ modalStore.confirmButtonText }}
                </button>
                <button @click="modalStore.closeModal()"
                    class="btn"
                    :class="{
                        'btn-outline': modalStore.type === 'confirm',
                        'btn-primary': modalStore.type === 'alert' && modalStore.alertType === 'info',
                        'btn-success': modalStore.type === 'alert' && modalStore.alertType === 'success',
                        'btn-error': modalStore.type === 'alert' && modalStore.alertType === 'error',
                        'btn-warning': modalStore.type === 'alert' && modalStore.alertType === 'warning',
                    }">
                    <XMarkIcon class="w-5 h-5 me-2" />
                    {{ modalStore.cancelButtonText }}
                </button>
            </div>
        </div>
        <!-- This is the backdrop, click to close -->
        <form method="dialog" class="modal-backdrop" @click="modalStore.closeModal()">
            <button>close</button>
        </form>
    </dialog>
</template>

<script setup>
import { useModalStore } from '../stores/modal';
import { onMounted, onUnmounted, watch, ref } from 'vue';
import { XMarkIcon, CheckIcon, ExclamationCircleIcon, InformationCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/20/solid';

// FlowbiteModal.vue
// Component ນີ້ແມ່ນ modal ທີ່ໃຊ້ສຳລັບການສະແດງຂໍ້ຄວາມແຈ້ງເຕືອນ ຫຼື ຂໍ້ຄວາມຢືນຢັນ.
// ມັນເຊື່ອມຕໍ່ກັບ useModalStore ເພື່ອຄວບຄຸມສະຖານະການເປີດ/ປິດ ແລະເນື້ອຫາຂອງ modal.

const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Modal Store ເພື່ອຈັດການສະຖານະ modal
const flowbiteModalRef = ref(null); // Reference ຫາ element <dialog> ໃນ template

// ເບິ່ງການປ່ຽນແປງຂອງ modalStore.isOpen ເພື່ອຄວບຄຸມ element <dialog> ດ້ວຍຕົນເອງ
watch(() => modalStore.isOpen, (newVal) => {
    if (newVal) {
        // ຖ້າ modal ເປີດ, ສະແດງ dialog
        flowbiteModalRef.value.showModal();
    } else {
        // ຖ້າ modal ປິດ, ປິດ dialog
        flowbiteModalRef.value.close();
    }
});

// ຟັງຊັນຈັດການການກົດປຸ່ມ (ເຊັ່ນ: Escape key)
const handleKeydown = (event) => {
    // ຖ້າກົດປຸ່ມ Escape ແລະ modal ເປີດຢູ່
    if (event.key === 'Escape' && modalStore.isOpen) {
        modalStore.closeModal(); // ປິດ modal
    }
};

// Hook ເມື່ອ Component ຖືກ Mounted
onMounted(() => {
    document.addEventListener('keydown', handleKeydown); // ເພີ່ມ Event Listener ສໍາລັບການກົດປຸ່ມ
});

// Hook ເມື່ອ Component ຖືກ Unmounted
onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown); // ລຶບ Event Listener ເພື່ອປ້ອງກັນ Memory Leak
});
</script>

<style scoped>
/* Scoped styles for FlowbiteModal.vue if needed */
</style>
