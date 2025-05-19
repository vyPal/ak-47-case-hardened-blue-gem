<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 mb-8">
          Admin Dashboard
        </h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Total Users -->
          <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-300">Total Users</h3>
              <div class="bg-purple-600 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
            <p class="mt-4 text-3xl font-bold text-white">{{ stats.totalUsers }}</p>
          </div>

          <!-- Total Cards -->
          <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-300">Total Cards</h3>
              <div class="bg-pink-600 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <p class="mt-4 text-3xl font-bold text-white">{{ stats.totalCards }}</p>
          </div>

          <!-- This Week's Uploads -->
          <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-300">This Week's Uploads</h3>
              <div class="bg-green-600 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="mt-4 text-3xl font-bold text-white">{{ stats.cardsUploadedThisWeek }}</p>
          </div>

          <!-- Cards in Voting -->
          <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-300">Cards in Voting</h3>
              <div class="bg-indigo-600 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
            <p class="mt-4 text-3xl font-bold text-white">{{ stats.cardsByStatus.voting }}</p>
          </div>
        </div>

        <!-- Cards by Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Status Distribution -->
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">Card Status Distribution</h2>
            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">Draft</span>
                  <span class="text-gray-400">{{ stats.cardsByStatus.draft }}</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                  <div class="bg-gray-400 rounded-full h-2" :style="{ width: `${(stats.cardsByStatus.draft / stats.totalCards) * 100}%` }"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">In Voting</span>
                  <span class="text-gray-400">{{ stats.cardsByStatus.voting }}</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                  <div class="bg-purple-500 rounded-full h-2" :style="{ width: `${(stats.cardsByStatus.voting / stats.totalCards) * 100}%` }"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">Minted</span>
                  <span class="text-gray-400">{{ stats.cardsByStatus.minted }}</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                  <div class="bg-green-500 rounded-full h-2" :style="{ width: `${(stats.cardsByStatus.minted / stats.totalCards) * 100}%` }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Most Active Users -->
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-white">Most Active Users</h2>
              <Link :href="route('admin.users')" class="text-sm text-purple-400 hover:text-purple-300">View All</Link>
            </div>
            <div class="space-y-4">
              <div v-for="user in mostActiveUsers" :key="user.id" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="text-white font-medium">{{ user.name }}</div>
                  <span v-if="user.is_admin" class="ml-2 px-2 py-1 text-xs bg-purple-900 text-purple-300 rounded-full">Admin</span>
                </div>
                <div class="text-gray-400">{{ user.cards_count }} cards</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <!-- Mint Cards Action -->
          <button
            @click="showMintConfirmation = true"
            class="bg-gradient-to-br from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 p-4 rounded-lg flex items-center justify-center shadow-lg"
            :class="{ 'opacity-50 cursor-not-allowed': !isWeekend }"
            :disabled="!isWeekend"
          >
            <div class="text-center">
              <div class="bg-white/20 rounded-full p-3 mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <div class="text-white font-medium">Mint Cards</div>
              <div v-if="!isWeekend" class="text-white/60 text-sm mt-1">Available on weekends</div>
            </div>
          </button>

          <!-- Manage Users -->
          <Link
            :href="route('admin.users')"
            class="bg-gradient-to-br from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 p-4 rounded-lg flex items-center justify-center shadow-lg"
          >
            <div class="text-center">
              <div class="bg-white/20 rounded-full p-3 mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="text-white font-medium">Manage Users</div>
            </div>
          </Link>

          <!-- System Settings -->
          <Link
            :href="route('admin.settings')"
            class="bg-gradient-to-br from-green-600 to-teal-600 hover:from-green-500 hover:to-teal-500 p-4 rounded-lg flex items-center justify-center shadow-lg"
          >
            <div class="text-center">
              <div class="bg-white/20 rounded-full p-3 mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="text-white font-medium">System Settings</div>
            </div>
          </Link>
        </div>

        <!-- Most Rare Cards -->
        <div class="bg-gray-800 rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Most Rare Cards</h2>
            <Link :href="route('admin.cards')" class="text-sm text-purple-400 hover:text-purple-300">View All Cards</Link>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div v-for="card in mostRareCards" :key="card.id" class="bg-gray-700 rounded-lg overflow-hidden">
              <img :src="`/storage/${card.image_path}`" :alt="card.name" class="w-full aspect-4/3 object-cover">
              <div class="p-4">
                <h3 class="font-medium text-white truncate">{{ card.name }}</h3>
                <div class="mt-2 flex items-center justify-between text-sm">
                  <span class="text-gray-400">Rarity</span>
                  <span class="text-purple-400 font-medium">{{ card.rarity }}/10</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-sm">
                  <span class="text-gray-400">Creator</span>
                  <span class="text-gray-300">{{ card.user.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mint Confirmation Modal -->
    <Modal :show="showMintConfirmation" @close="showMintConfirmation = false">
      <div class="p-6">
        <h2 class="text-xl font-bold text-white mb-4">Mint Cards Confirmation</h2>
        <p class="text-gray-300 mb-6">
          Are you sure you want to mint all cards in voting? This action will:
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Calculate final stats based on votes</li>
            <li>Select top abilities for each card</li>
            <li>Generate card copies based on rarity</li>
            <li>Distribute cards to users</li>
          </ul>
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="showMintConfirmation = false"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="mintCards"
            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-colors"
          >
            Start Minting
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script>
import { defineComponent, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';

export default defineComponent({
  components: {
    AppLayout,
    Link,
    Modal,
  },

  props: {
    stats: {
      type: Object,
      required: true,
    },
    mostActiveUsers: {
      type: Array,
      required: true,
    },
    mostRareCards: {
      type: Array,
      required: true,
    },
    isWeekend: {
      type: Boolean,
      required: true,
    },
  },

  setup() {
    const showMintConfirmation = ref(false);

    const mintCards = () => {
      router.post(route('admin.mint-cards'), {}, {
        onSuccess: () => {
          showMintConfirmation.value = false;
        },
      });
    };

    return {
      showMintConfirmation,
      mintCards,
    };
  },
});
</script>

<style scoped>
.aspect-4\/3 {
  aspect-ratio: 4/3;
}
</style>
