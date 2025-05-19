<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            User Directory
          </h1>
          <p class="text-gray-400 mt-2">Browse and explore collections from other users</p>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 md:mb-8 flex flex-col sm:flex-row gap-3">
          <div class="flex-1 w-full">
            <form @submit.prevent="search" class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search class="h-5 w-5 text-gray-400" />
              </div>
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search users by name..." 
                class="block w-full bg-gray-800 border border-gray-700 rounded-md py-2 pl-10 pr-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
              <button 
                type="submit"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white"
              >
                <ArrowRight class="h-5 w-5" />
              </button>
            </form>
          </div>
          <div class="w-full sm:w-auto">
            <select 
              v-model="sortBy"
              @change="resort"
              class="w-full bg-gray-800 border border-gray-700 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="name">Sort by Name</option>
              <option value="collection">Sort by Collection Size</option>
              <option value="rarity">Sort by Avg. Rarity</option>
              <option value="newest">Sort by Newest</option>
            </select>
          </div>
        </div>

        <!-- Users Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
          <div 
            v-for="user in users.data" 
            :key="user.id"
            class="bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:shadow-purple-500/20 transition-all hover:translate-y-[-2px]"
          >
            <div class="p-4 md:p-6">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3">
                <div class="mb-2 sm:mb-0">
                  <InertiaLink 
                    :href="route('users.show', user.id)"
                    class="text-lg font-bold text-white hover:text-purple-400 transition-colors"
                  >
                    {{ user.name }}
                  </InertiaLink>
                  <div v-if="user.is_admin" class="text-xs font-semibold bg-purple-900/60 text-purple-300 border border-purple-700/50 rounded-full px-2 py-0.5 inline-block">
                    Admin
                  </div>
                </div>
                <InertiaLink 
                  v-if="canTradeWith(user)"
                  :href="route('trades.create', { recipient: user.id })" 
                  class="text-blue-400 hover:text-blue-300 flex items-center justify-center sm:justify-start"
                >
                  <SendHorizonal class="h-4 w-4 mr-1" />
                  Trade
                </InertiaLink>
              </div>
              
              <div class="space-y-2 text-sm mb-3 md:mb-4">
                <div class="grid grid-cols-2 gap-1 items-center">
                  <div class="text-gray-400">Collection:</div>
                  <div class="text-right text-white">{{ user.owned_cards_count || 0 }} cards</div>
                </div>
                
                <div v-if="user.avg_rarity" class="grid grid-cols-2 gap-1">
                  <div class="text-gray-400">Avg. Rarity:</div>
                  <div class="text-right">
                    <div class="flex items-center justify-end">
                      <Star class="h-3.5 w-3.5 text-yellow-400 mr-1" />
                      <span class="text-white">{{ user.avg_rarity }}/10</span>
                    </div>
                  </div>
                </div>
                
                <div class="grid grid-cols-2 gap-1">
                  <div class="text-gray-400">Member since:</div>
                  <div class="text-right text-white">{{ formatDate(user.created_at) }}</div>
                </div>
              </div>
              
              <div class="mt-3 md:mt-4 flex justify-end">
                <InertiaLink
                  :href="route('users.show', user.id)"
                  class="text-sm text-gray-300 hover:text-white flex items-center transition-colors"
                >
                  View Profile
                  <ChevronRight class="h-4 w-4 ml-1" />
                </InertiaLink>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6 md:mt-8">
          <Pagination :links="users.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link as InertiaLink, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { ArrowRight, ChevronRight, Search, SendHorizonal, Star } from 'lucide-vue-next';

const props = defineProps({
  users: Object,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref('name');

function search() {
  if (searchQuery.value) {
    router.get(route('users.index'), { 
      search: searchQuery.value 
    }, { 
      preserveState: true,
      replace: true
    });
  } else {
    router.get(route('users.index'), {}, { 
      preserveState: true,
      replace: true 
    });
  }
}

function resort() {
  router.reload({
    data: {
      sort: sortBy.value,
      search: searchQuery.value
    },
    only: ['users']
  });
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(date);
}

function canTradeWith(user) {
  const currentUser = usePage().props.auth?.user;
  return currentUser && currentUser.id !== user.id;
}
</script>