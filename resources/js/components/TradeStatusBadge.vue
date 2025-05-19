<template>
    <span :class="['inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold', statusClasses[status] || 'bg-gray-700 text-gray-300']">
        <component :is="statusIcon" v-if="statusIcon" class="mr-1 h-3 w-3" />
        {{ statusText }}
    </span>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircle, XCircle, Clock, RotateCcw } from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) => ['pending', 'accepted', 'declined', 'cancelled'].includes(value),
    },
});

const statusClasses = {
    pending: 'bg-yellow-900/60 text-yellow-300 border border-yellow-700/50',
    accepted: 'bg-green-900/60 text-green-300 border border-green-700/50',
    declined: 'bg-red-900/60 text-red-300 border border-red-700/50',
    cancelled: 'bg-gray-700/60 text-gray-300 border border-gray-600/50',
};

const statusText = computed(
    () =>
        ({
            pending: 'Pending',
            accepted: 'Accepted',
            declined: 'Declined',
            cancelled: 'Cancelled',
        })[props.status],
);

const statusIcon = computed(() => {
    const icons = {
        pending: Clock,
        accepted: CheckCircle,
        declined: XCircle,
        cancelled: RotateCcw,
    };

    return icons[props.status] || null;
});
</script>
