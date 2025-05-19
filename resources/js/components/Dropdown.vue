<template>
  <div class="relative">
    <!-- Trigger -->
    <div @click="open = !open">
      <slot name="trigger"></slot>
    </div>
    
    <!-- Dropdown content -->
    <div v-show="open"
         class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
         @click="open = false"
    >
      <div class="rounded-md bg-gray-800 border border-gray-700 shadow-xs divide-y divide-gray-700">
        <slot name="content"></slot>
      </div>
    </div>
    
    <!-- Backdrop -->
    <div v-show="open"
         class="fixed inset-0 z-40"
         @click="open = false">
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  setup() {
    const open = ref(false);

    const closeOnEscape = (e) => {
      if (e.key === 'Escape' && open.value) {
        open.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener('keydown', closeOnEscape);
    });

    onUnmounted(() => {
      document.removeEventListener('keydown', closeOnEscape);
    });

    return {
      open,
    };
  },
};
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.2s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>