<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center">
        <!-- Background overlay -->
        <div 
          class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" 
          @click="$emit('close')"
        ></div>

        <!-- Modal panel -->
        <Transition
          enter-active-class="duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div 
            v-if="show"
            class="relative w-full max-w-lg mx-4 sm:mx-auto bg-gray-800 rounded-lg shadow-xl overflow-hidden z-50"
          >
            <!-- Close button -->
            <button 
              type="button" 
              class="absolute right-4 top-4 text-gray-400 hover:text-gray-300 focus:outline-none"
              @click="$emit('close')"
            >
              <span class="sr-only">Close</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Modal content -->
            <slot></slot>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    show: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['close'],

  watch: {
    show(value) {
      if (value) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    },
  },

  mounted() {
    document.addEventListener('keydown', this.handleEscape);
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleEscape);
    document.body.style.overflow = '';
  },

  methods: {
    handleEscape(e) {
      if (e.key === 'Escape' && this.show) {
        this.$emit('close');
      }
    },
  },
});
</script>