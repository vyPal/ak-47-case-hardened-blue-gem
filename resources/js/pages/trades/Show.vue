<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center">
          <InertiaLink :href="route('trades.index')" class="mr-4 flex items-center text-blue-400 hover:text-blue-300">
            <ArrowLeft class="mr-1 h-4 w-4" />
            Back to Trades
          </InertiaLink>
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            Trade Details
          </h1>
          <TradeStatusBadge :status="trade.status" class="ml-4" />
        </div>

        <!-- Trade information -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <div class="text-gray-400 text-sm mb-1">Trade with</div>
              <div class="text-white text-lg font-medium">{{ isInitiator ? trade.recipient.name : trade.initiator.name }}</div>
            </div>
            <div>
              <div class="text-gray-400 text-sm mb-1">Date</div>
              <div class="text-white text-lg font-medium">{{ formatDate(trade.created_at) }}</div>
            </div>
          </div>

          <!-- Message if present -->
          <div v-if="trade.message" class="mb-6">
            <div class="text-gray-400 text-sm mb-1">Message</div>
            <div class="bg-gray-700 p-4 rounded-lg text-white">
              {{ trade.message }}
            </div>
          </div>

          <!-- Trade actions -->
          <div 
            v-if="trade.status === 'pending'"
            class="flex justify-end space-x-3"
          >
            <template v-if="isRecipient">
              <button 
                @click="declineTrade" 
                class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-all flex items-center"
              >
                <XCircle class="mr-1 h-4 w-4" />
                Decline
              </button>
              <button 
                @click="confirmAcceptTrade" 
                class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-md hover:from-green-700 hover:to-green-600 transition-all flex items-center"
              >
                <CheckCircle class="mr-1 h-4 w-4" />
                Accept
              </button>
            </template>
            <template v-if="isInitiator">
              <button 
                @click="cancelTrade" 
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all flex items-center"
              >
                <XCircle class="mr-1 h-4 w-4" />
                Cancel
              </button>
            </template>
          </div>
        </div>

        <!-- Trade contents -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Initiator's offers -->
          <div class="bg-gray-800 rounded-lg overflow-hidden">
            <div class="bg-gray-700 p-4">
              <div class="flex items-center">
                <UserIcon class="mr-2 h-5 w-5 text-purple-400" />
                <h2 class="text-lg font-bold text-white">
                  {{ isInitiator ? 'Your Offer' : `${trade.initiator.name}'s Offer` }}
                </h2>
              </div>
            </div>

            <div class="p-4">
              <div v-if="initiatorItems.length === 0" class="text-gray-400 text-center p-4">
                No cards offered.
              </div>

              <div v-else class="space-y-3">
                <div 
                  v-for="item in initiatorItems" 
                  :key="item.id"
                  class="flex bg-gray-700 rounded-lg overflow-hidden"
                >
                  <div class="w-24 h-24">
                    <img 
                      :src="`/storage/${item.card.image_path}`" 
                      :alt="item.card.name" 
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div class="p-3 flex-grow">
                    <div class="text-white font-medium">{{ item.card.name }}</div>
                    <div class="text-gray-400 text-sm">Quantity: {{ item.serial_numbers.length }}</div>
                    <div class="text-gray-400 text-sm">
                      <div class="flex flex-wrap gap-1 mt-1">
                        <span 
                          v-for="serial in item.serial_numbers" 
                          :key="serial"
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-gray-200"
                        >
                          #{{ serial }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recipient's offers -->
          <div class="bg-gray-800 rounded-lg overflow-hidden">
            <div class="bg-gray-700 p-4">
              <div class="flex items-center">
                <UserIcon class="mr-2 h-5 w-5 text-blue-400" />
                <h2 class="text-lg font-bold text-white">
                  {{ isRecipient ? 'Your Offer' : `${trade.recipient.name}'s Offer` }}
                </h2>
              </div>
            </div>
            
            <div class="p-4">
              <div v-if="recipientItems.length === 0" class="text-gray-400 text-center p-4">
                No cards requested.
              </div>

              <div v-else class="space-y-3">
                <div 
                  v-for="item in recipientItems" 
                  :key="item.id"
                  class="flex bg-gray-700 rounded-lg overflow-hidden"
                >
                  <div class="w-24 h-24">
                    <img 
                      :src="`/storage/${item.card.image_path}`" 
                      :alt="item.card.name" 
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div class="p-3 flex-grow">
                    <div class="text-white font-medium">{{ item.card.name }}</div>
                    <div class="text-gray-400 text-sm">Quantity: {{ item.serial_numbers.length }}</div>
                    <div class="text-gray-400 text-sm">
                      <div class="flex flex-wrap gap-1 mt-1">
                        <span 
                          v-for="serial in item.serial_numbers" 
                          :key="serial"
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-gray-200"
                        >
                          #{{ serial }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Trade status info -->
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-bold text-white mb-4 flex items-center">
            <ClipboardList class="mr-2 h-5 w-5 text-pink-400" />
            Trade Status History
          </h3>
          
          <div class="space-y-3">
            <div class="flex items-center text-white">
              <div class="bg-gray-700 rounded-full p-1 mr-3">
                <Clock class="h-4 w-4 text-yellow-400" />
              </div>
              <div>
                <span class="font-medium">Trade created</span>
                <span class="text-gray-400 ml-2">{{ formatDate(trade.created_at) }}</span>
              </div>
            </div>

            <!-- Show this if the trade is no longer pending -->
            <div v-if="trade.status !== 'pending'" class="flex items-center text-white">
              <div class="bg-gray-700 rounded-full p-1 mr-3">
                <component :is="statusIcon" class="h-4 w-4" :class="statusIconColor" />
              </div>
              <div>
                <span class="font-medium">Trade {{ trade.status }}</span>
                <span class="text-gray-400 ml-2">{{ formatDate(trade.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Modal :show="confirmationOpen" @close="confirmationOpen = false" max-width="md">
      <div class="p-6">
        <h2 class="text-xl font-bold text-white mb-4">Confirm Trade</h2>
        <p class="text-gray-300 mb-4">
          Are you sure you want to accept this trade? This will transfer the cards between accounts and cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="confirmationOpen = false"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500 transition-all"
          >
            Cancel
          </button>
          <button
            @click="acceptTrade"
            class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-md hover:from-green-700 hover:to-green-600 transition-all"
          >
            Confirm Accept
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link as InertiaLink, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';
import TradeStatusBadge from '@/components/TradeStatusBadge.vue';
import { 
  ArrowLeft, 
  CheckCircle, 
  Clock,
  ClipboardList,
  UserIcon,
  XCircle 
} from 'lucide-vue-next';

const props = defineProps({
  trade: Object,
  initiatorItems: Array,
  recipientItems: Array,
  isInitiator: Boolean,
  isRecipient: Boolean,
});

const confirmationOpen = ref(false);

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
  }).format(date);
};

const statusIcon = computed(() => {
  const icons = {
    accepted: CheckCircle,
    declined: XCircle,
    cancelled: XCircle,
  };

  return icons[props.trade.status] || Clock;
});

const statusIconColor = computed(() => {
  const colors = {
    accepted: 'text-green-400',
    declined: 'text-red-400',
    cancelled: 'text-gray-400',
  };

  return colors[props.trade.status] || 'text-yellow-400';
});

function confirmAcceptTrade() {
  confirmationOpen.value = true;
}

function acceptTrade() {
  router.post(route('trades.accept', props.trade.id), {}, {
    onSuccess: () => {
      confirmationOpen.value = false;
    },
  });
}

function formatSerialNumber(serial) {
  return `#${serial}`;
}

function declineTrade() {
  if (confirm('Are you sure you want to decline this trade?')) {
    router.post(route('trades.decline', props.trade.id));
  }
}

function cancelTrade() {
  if (confirm('Are you sure you want to cancel this trade?')) {
    router.post(route('trades.cancel', props.trade.id));
  }
}
</script>