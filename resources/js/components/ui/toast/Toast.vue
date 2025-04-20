<script setup lang="ts">
import { computed } from 'vue';
import { toastVariants } from '.';
import { X } from 'lucide-vue-next';

const props = defineProps<{
  id: number;
  message: string;
  variant?: "default" | "destructive";
}>();

const emit = defineEmits(['dismiss']);

const variantClass = computed(() => toastVariants({ variant: props.variant }));

const dismiss = () => {
  emit('dismiss', props.id);
};
</script>

<template>
  <div class="absolute bottom-4 right-4 z-50 flex flex-col gap-2 w-full max-w-md">
    <div :class="variantClass" class="flex items-center justify-between">
      <div class="flex-1">{{ message }}</div>
      <button @click="dismiss" class="rounded-full p-1 hover:bg-accent">
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>
