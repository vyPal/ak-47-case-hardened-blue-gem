<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Back button -->
        <div class="mb-4 sm:mb-6">
          <InertiaLink :href="route('users.index')" class="flex items-center text-blue-400 hover:text-blue-300">
            <ArrowLeft class="mr-1 h-4 w-4" />
            <span class="whitespace-nowrap">Back to User Directory</span>
          </InertiaLink>
        </div>

        <!-- User Profile Header -->
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg mb-4 sm:mb-6">
          <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div class="mb-4 sm:mb-0">
                <div class="flex flex-wrap items-center">
                  <h1 class="text-xl sm:text-2xl font-bold text-white mr-2">{{ profileUser.name }}</h1>
                  <div v-if="profileUser.is_admin" class="text-xs font-semibold bg-purple-900/60 text-purple-300 border border-purple-700/50 rounded-full px-2 py-0.5 inline-block">
                    Admin
                  </div>
                </div>
                <p class="text-sm text-gray-400 mt-1">Member since {{ profileUser.created_at }}</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <InertiaLink
                  v-if="canTrade"
                  :href="route('trades.create', { recipient: profileUser.id })"
                  class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-md hover:from-blue-600 hover:to-blue-700 transition-all flex items-center justify-center"
                >
                  <SendHorizonal class="mr-1 h-4 w-4" />
                  <span class="whitespace-nowrap">Trade with {{ truncatedName }}</span>
                </InertiaLink>
              </div>
            </div>
          </div>
        </div>

        <!-- Collection Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6 mb-4 sm:mb-6">
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6 flex flex-col">
            <h3 class="text-gray-400 text-sm mb-1">Total Cards</h3>
            <div class="text-xl sm:text-2xl font-bold text-white">{{ collection.total_cards }}</div>
            <div class="mt-auto pt-2 text-sm text-gray-500">Cards in collection</div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6 flex flex-col">
            <h3 class="text-gray-400 text-sm mb-1">Average Rarity</h3>
            <div class="text-xl sm:text-2xl font-bold text-white flex items-center">
              <Star class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-400 mr-1" />
              {{ collection.avg_rarity }}/10
            </div>
            <div class="mt-auto pt-2 text-sm text-gray-500">Higher is rarer</div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6 flex flex-col sm:col-span-2 md:col-span-1">
            <h3 class="text-gray-400 text-sm mb-1">Rarity Distribution</h3>
            <div class="h-6 sm:h-8 w-full bg-gray-700 rounded-md overflow-hidden flex">
              <div 
                v-for="(count, index) in collection.rarity_distribution" 
                :key="index"
                :style="{ width: `${getRarityBarWidth(index)}%` }"
                :class="getRarityBarColor(index)"
                class="h-full"
                :title="`Rarity ${index + 1}: ${count} cards`"
              ></div>
            </div>
            <div class="mt-auto pt-2 text-sm text-gray-500 flex justify-between">
              <span>Common (1)</span>
              <span>Rare (10)</span>
            </div>
          </div>
        </div>

        <!-- Card Collection -->
        <div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 gap-2 sm:gap-0">
            <h2 class="text-lg sm:text-xl font-bold text-white">Card Collection</h2>
            
            <!-- Filters -->
            <div class="flex items-center">
              <select 
                v-model="sortBy"
                @change="sortCards"
                class="w-full sm:w-auto bg-gray-800 border border-gray-700 rounded-md py-1.5 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="name">Sort by Name</option>
                <option value="rarity-desc">Sort by Rarity (High to Low)</option>
                <option value="rarity-asc">Sort by Rarity (Low to High)</option>
                <option value="quantity">Sort by Quantity</option>
              </select>
            </div>
          </div>

          <div v-if="collection.cards.length === 0" class="bg-gray-800 rounded-lg p-4 sm:p-6 text-center text-gray-400">
            This user doesn't have any cards yet.
          </div>
          
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            <div 
              v-for="ownership in sortedCards" 
              :key="ownership.id"
              class="bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-purple-500/20 transition-all"
            >
              <!-- Card Image -->
              <div class="relative h-36 sm:h-40 md:h-48 overflow-hidden">
                <img 
                  :src="`/storage/${ownership.card.image_path}`"
                  :alt="ownership.card.name"
                  class="w-full h-full object-cover"
                />
                <div class="absolute top-0 right-0 bg-black bg-opacity-75 px-2 py-1">
                  <div class="flex items-center">
                    <Star class="h-3.5 w-3.5 text-yellow-400 mr-1" />
                    <span class="text-sm font-bold text-white">{{ ownership.card.rarity }}/10</span>
                  </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                  <div class="text-white font-bold text-sm">{{ ownership.card.name }}</div>
                </div>
              </div>
              
              <!-- Card Details -->
              <div class="p-3 sm:p-4">
                <div class="grid grid-cols-3 gap-1 sm:gap-2 text-xs sm:text-sm mb-2 sm:mb-3">
                  <div class="bg-gray-700 rounded p-1 sm:p-2 text-center">
                    <div class="text-red-400">HP</div>
                    <div class="font-bold text-white">{{ ownership.card.hp }}</div>
                  </div>
                  <div class="bg-gray-700 rounded p-1 sm:p-2 text-center">
                    <div class="text-blue-400">STR</div>
                    <div class="font-bold text-white">{{ ownership.card.strength }}</div>
                  </div>
                  <div class="bg-gray-700 rounded p-1 sm:p-2 text-center">
                    <div class="text-green-400">DEF</div>
                    <div class="font-bold text-white">{{ ownership.card.defense }}</div>
                  </div>
                </div>
                
                <div class="flex justify-between items-center">
                  <div>
                    <div class="text-sm text-gray-400">
                      {{ ownership.quantity }} card{{ ownership.quantity !== 1 ? 's' : '' }}
                    </div>
                    <div class="text-xs text-gray-500 flex flex-wrap gap-1 mt-1">
                      <span 
                        v-for="serial in ownership.serial_numbers.slice(0, 2)" 
                        :key="serial"
                        class="bg-gray-700 px-1 sm:px-1.5 py-0.5 rounded text-[10px] sm:text-xs"
                      >
                        #{{ serial }}
                      </span>
                      <span v-if="ownership.serial_numbers.length > 2" class="bg-gray-700 px-1 sm:px-1.5 py-0.5 rounded text-[10px] sm:text-xs">
                        +{{ ownership.serial_numbers.length - 2 }}
                      </span>
                    </div>
                  </div>
                  
                  <div v-if="ownership.card.created_by" class="px-2 py-0.5 text-xs bg-purple-900/60 text-purple-300 border border-purple-700/50 rounded">
                    Created
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { Link as InertiaLink } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, SendHorizonal, Star } from 'lucide-vue-next';

const props = defineProps({
  profileUser: Object,
  collection: Object,
  canTrade: Boolean,
});

const sortBy = ref('name');
const isMobile = ref(window.innerWidth < 640);
const truncatedName = computed(() => {
  return isMobile.value ? props.profileUser.name.substring(0, 6) + '...' : props.profileUser.name;
});

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
const sortedCards = computed(() => {
  if (!props.collection.cards) return [];
  
  const cards = [...props.collection.cards];
  
  switch (sortBy.value) {
    case 'name':
      return cards.sort((a, b) => a.card.name.localeCompare(b.card.name));
    case 'rarity-desc':
      return cards.sort((a, b) => (b.card.rarity || 0) - (a.card.rarity || 0));
    case 'rarity-asc':
      return cards.sort((a, b) => (a.card.rarity || 0) - (b.card.rarity || 0));
    case 'quantity':
      return cards.sort((a, b) => b.quantity - a.quantity);
    default:
      return cards;
  }
});

function sortCards() {
  // Just using the computed property, no need for extra logic here
}

function getRarityBarWidth(rarityIndex) {
  const total = props.collection.total_cards || 0;
  if (total === 0) return 0;
  
  const count = props.collection.rarity_distribution[rarityIndex];
  return (count / total) * 100;
}

function getRarityBarColor(rarityIndex) {
  const colors = [
    'bg-green-600',  // 1 - Common
    'bg-green-500',
    'bg-blue-600',
    'bg-blue-500',
    'bg-blue-400',
    'bg-purple-600',
    'bg-purple-500',
    'bg-pink-600',
    'bg-pink-500',
    'bg-red-500',    // 10 - Rare
  ];
  
  return colors[rarityIndex] || 'bg-gray-600';
}
</script>