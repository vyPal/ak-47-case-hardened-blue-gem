<template>
  <span :class="[
    'px-2 py-1 text-xs font-semibold rounded-full',
    statusClasses[status] || 'bg-gray-700 text-gray-300'
  ]">
    {{ statusText }}
  </span>
</template>

<script>
import { computed } from 'vue';

export default {
  props: {
    status: {
      type: String,
      required: true,
      validator: value => ['draft', 'voting', 'minted'].includes(value)
    }
  },
  
  setup(props) {
    const statusClasses = {
      draft: 'bg-gray-700 text-gray-300',
      voting: 'bg-purple-900 text-purple-300',
      minted: 'bg-green-900 text-green-300'
    };
    
    const statusText = computed(() => ({
      draft: 'Draft',
      voting: 'In Voting',
      minted: 'Minted'
    }[props.status]));
    
    return {
      statusClasses,
      statusText
    };
  }
};
</script>