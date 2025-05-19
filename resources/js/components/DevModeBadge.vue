<template>
  <span 
    class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-gradient-to-r from-amber-500/20 to-orange-500/20 text-amber-300 border border-amber-500/30"
    :title="tooltip"
  >
    <slot>Dev Mode</slot>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 ml-1" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
    </svg>
  </span>
</template>

<script>
export default {
  props: {
    tooltip: {
      type: String,
      default: 'This feature is only visible in development mode'
    }
  }
}
</script>