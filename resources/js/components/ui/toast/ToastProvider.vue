<script setup lang="ts">
import { useToast } from '.';
import Toast from './Toast.vue';

const { toasts, dismissToast } = useToast();
</script>

<template>
  <div v-if="toasts.length > 0" class="fixed inset-0 pointer-events-none flex flex-col items-end justify-end p-4 z-50">
    <transition-group name="toast">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :id="toast.id"
        :message="toast.message"
        :variant="toast.variant"
        @dismiss="dismissToast"
      />
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateY(100%);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
