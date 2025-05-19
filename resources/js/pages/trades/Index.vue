<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            Card Trades
          </h1>
          <InertiaLink 
            :href="route('trades.create')" 
            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-all flex items-center"
          >
            <Plus class="mr-1 h-4 w-4" />
            Start New Trade
          </InertiaLink>
        </div>

        <!-- Trades you've initiated -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-white mb-4">Trades You've Initiated</h2>
          
          <div v-if="initiatedTrades.length > 0" class="bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Trading With
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Your Offer
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Their Offer
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                  <tr v-for="trade in initiatedTrades" :key="trade.id" class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-white">{{ trade.recipient.name }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex space-x-2">
                        <div v-for="item in getInitiatorItems(trade)" :key="item.id" class="w-8 h-8 bg-gray-700 rounded overflow-hidden relative group">
                          <img :src="'/storage/' + item.card.image_path" class="w-full h-full object-cover" :alt="item.card.name">
                          <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-xs text-white">#{{ item.serial_numbers.length > 1 ? item.serial_numbers.length : item.serial_numbers[0] }}</span>
                          </div>
                          <div class="absolute top-0 right-0 bg-black bg-opacity-70 px-1 py-0.5">
                            <span class="text-xs text-white">{{ item.serial_numbers.length }}x</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex space-x-2">
                        <div v-for="item in getRecipientItems(trade)" :key="item.id" class="w-8 h-8 bg-gray-700 rounded overflow-hidden relative group">
                          <img :src="'/storage/' + item.card.image_path" class="w-full h-full object-cover" :alt="item.card.name">
                          <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-xs text-white">#{{ item.serial_numbers.length > 1 ? item.serial_numbers.length : item.serial_numbers[0] }}</span>
                          </div>
                          <div class="absolute top-0 right-0 bg-black bg-opacity-70 px-1 py-0.5">
                            <span class="text-xs text-white">{{ item.serial_numbers.length }}x</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <TradeStatusBadge :status="trade.status" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                      {{ formatDate(trade.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <div class="flex justify-end space-x-3">
                        <InertiaLink :href="route('trades.show', trade.id)" class="text-blue-400 hover:text-blue-300">
                          View
                        </InertiaLink>
                        <button 
                          v-if="trade.status === 'pending'" 
                          @click="cancelTrade(trade)" 
                          class="text-red-400 hover:text-red-300 flex items-center"
                        >
                          <X class="mr-1 h-4 w-4" />
                          Cancel
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div v-else class="bg-gray-800 rounded-lg p-6 text-center text-gray-400">
            You haven't initiated any trades yet
          </div>
        </div>

        <!-- Trades received -->
        <div>
          <h2 class="text-xl font-bold text-white mb-4">Trades Received</h2>
          
          <div v-if="receivedTrades.length > 0" class="bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      From
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      You Give
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      You Get
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                  <tr v-for="trade in receivedTrades" :key="trade.id" class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-white">{{ trade.initiator.name }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex space-x-2">
                        <div v-for="item in getRecipientItems(trade)" :key="item.id" class="w-8 h-8 bg-gray-700 rounded overflow-hidden relative group">
                          <img :src="'/storage/' + item.card.image_path" class="w-full h-full object-cover" :alt="item.card.name">
                          <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-xs text-white">#{{ item.serial_numbers.length > 1 ? item.serial_numbers.length : item.serial_numbers[0] }}</span>
                          </div>
                          <div class="absolute top-0 right-0 bg-black bg-opacity-70 px-1 py-0.5">
                            <span class="text-xs text-white">{{ item.serial_numbers.length }}x</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex space-x-2">
                        <div v-for="item in getInitiatorItems(trade)" :key="item.id" class="w-8 h-8 bg-gray-700 rounded overflow-hidden relative group">
                          <img :src="'/storage/' + item.card.image_path" class="w-full h-full object-cover" :alt="item.card.name">
                          <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-xs text-white">#{{ item.serial_numbers.length > 1 ? item.serial_numbers.length : item.serial_numbers[0] }}</span>
                          </div>
                          <div class="absolute top-0 right-0 bg-black bg-opacity-70 px-1 py-0.5">
                            <span class="text-xs text-white">{{ item.serial_numbers.length }}x</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <TradeStatusBadge :status="trade.status" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                      {{ formatDate(trade.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <div class="flex justify-end space-x-3">
                        <InertiaLink :href="route('trades.show', trade.id)" class="text-blue-400 hover:text-blue-300">
                          View
                        </InertiaLink>
                        <template v-if="trade.status === 'pending'">
                          <button @click="acceptTrade(trade)" class="text-green-400 hover:text-green-300 flex items-center">
                            <CheckCircle class="mr-1 h-4 w-4" />
                            Accept
                          </button>
                          <button @click="declineTrade(trade)" class="text-red-400 hover:text-red-300 flex items-center">
                            <XCircle class="mr-1 h-4 w-4" />
                            Decline
                          </button>
                        </template>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div v-else class="bg-gray-800 rounded-lg p-6 text-center text-gray-400">
            You haven't received any trades
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Link as InertiaLink, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TradeStatusBadge from '@/components/TradeStatusBadge.vue';
import { CheckCircle, Plus, X, XCircle } from 'lucide-vue-next';

export default defineComponent({
  components: {
    AppLayout,
    CheckCircle,
    InertiaLink,
    Plus,
    TradeStatusBadge,
    X,
    XCircle,
  },
  
  props: {
    initiatedTrades: {
      type: Array,
      required: true,
    },
    receivedTrades: {
      type: Array,
      required: true,
    },
  },
  
  setup() {
    const getInitiatorItems = (trade) => {
      return trade.items.filter(item => item.user_id === trade.initiator_id);
    };
    
    const getRecipientItems = (trade) => {
      return trade.items.filter(item => item.user_id === trade.recipient_id);
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      }).format(date);
    };
    
    const acceptTrade = (trade) => {
      if (confirm('Are you sure you want to accept this trade? Your cards will be exchanged.')) {
        router.post(route('trades.accept', trade.id));
      }
    };
    
    const declineTrade = (trade) => {
      if (confirm('Are you sure you want to decline this trade?')) {
        router.post(route('trades.decline', trade.id));
      }
    };
    
    const cancelTrade = (trade) => {
      if (confirm('Are you sure you want to cancel this trade?')) {
        router.post(route('trades.cancel', trade.id));
      }
    };
    
    return {
      getInitiatorItems,
      getRecipientItems,
      formatDate,
      acceptTrade,
      declineTrade,
      cancelTrade,
    };
  },
});
</script>