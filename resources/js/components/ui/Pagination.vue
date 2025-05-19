<template>
  <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1">
    <!-- Mobile-friendly pagination with fewer links -->
    <div v-for="(link, i) in visibleLinks" :key="i" class="flex items-center">
      <div
        v-if="link.url === null"
        :class="{ 'text-gray-400': !link.active }"
        class="px-2 sm:px-3 py-1 text-xs sm:text-sm select-none"
        v-html="link.label"
      />
      <InertiaLink
        v-else
        :href="link.url"
        :class="{ 'bg-purple-700': link.active }"
        class="px-2 sm:px-3 py-1 rounded-md text-xs sm:text-sm bg-gray-700 text-white hover:bg-purple-600 transition-colors"
        v-html="link.label"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link as InertiaLink } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});

const isMobile = ref(window.innerWidth < 640);

// Handle window resize
const handleResize = () => {
  isMobile.value = window.innerWidth < 640;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
});

// Create a mobile-friendly subset of pagination links
const visibleLinks = computed(() => {
  // Always show first, last, prev, next, and current pages
  if (!props.links || props.links.length <= 7) return props.links;
  
  // For very small screens, show minimal pagination
  if (isMobile.value) {
    return props.links.filter(link => {
      // Extract the label text without HTML
      const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
      const num = parseInt(label);
      
      // Always include Previous, Next, First, Last and Current page
      return (
        link.label.includes('&laquo;') || // Previous
        link.label.includes('&raquo;') || // Next
        link.active || // Current page
        num === 1 || // First page
        (isNaN(num) && label !== '...') // Non-numeric labels except ellipsis
      );
    });
  }
  
  return props.links;
});
</script>