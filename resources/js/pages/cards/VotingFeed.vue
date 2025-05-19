<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            Vote on Cards
          </h1>
          <p class="mt-2 text-gray-400">Help determine the stats and abilities of newly submitted cards</p>
        </div>

        <!-- Weekend Notice -->
        <div v-if="isWeekend" class="bg-purple-900 border border-purple-700 text-purple-100 p-6 rounded-lg mb-6">
          <h2 class="text-xl font-semibold mb-2">Weekend Voting Period</h2>
          <p>
            It's the weekend! All cards submitted during the week are now available for voting.
            Cards will be minted with their final stats after the voting period ends.
          </p>
        </div>

        <!-- No Cards to Vote -->
        <div v-if="!card" class="bg-gray-800 rounded-lg p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl font-medium text-white mb-2">No More Cards to Vote On</h3>
          <p class="text-gray-400">You've voted on all available cards. Check back later for new submissions!</p>
        </div>

        <!-- Voting Card -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Card Preview -->
          <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
            <div class="aspect-w-3 aspect-h-4">
              <img 
                :src="`/storage/${card.image_path}`" 
                :alt="card.name"
                class="object-cover w-full h-full"
              >
            </div>
            <div class="p-4">
              <h2 class="text-xl font-bold text-white mb-2">{{ card.name }}</h2>
              <p class="text-gray-400" v-if="card.description">{{ card.description }}</p>
              <p class="text-gray-500 italic" v-else>No description provided</p>
            </div>
          </div>

          <!-- Voting Form -->
          <div class="bg-gray-800 rounded-lg p-6">
            <form @submit.prevent="submit">
              <!-- Stats Voting -->
              <div class="mb-6">
                <h3 class="text-lg font-medium text-white mb-4">Rate Card Stats</h3>
                
                <!-- Rarity -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Rarity (1-10)
                    <span class="text-gray-500 text-xs ml-1">Higher = More Rare</span>
                  </label>
                  <input 
                    type="number" 
                    v-model="form.recommended_rarity"
                    min="1"
                    max="10"
                    required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                  >
                </div>

                <!-- HP -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    HP (1-100)
                  </label>
                  <input 
                    type="number" 
                    v-model="form.recommended_hp"
                    min="1"
                    max="100"
                    required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                  >
                </div>

                <!-- Strength -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Strength (1-100)
                  </label>
                  <input 
                    type="number" 
                    v-model="form.recommended_strength"
                    min="1"
                    max="100"
                    required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                  >
                </div>

                <!-- Defense -->
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Defense (1-100)
                  </label>
                  <input 
                    type="number" 
                    v-model="form.recommended_defense"
                    min="1"
                    max="100"
                    required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                  >
                </div>
              </div>

              <!-- Abilities -->
              <div class="mb-6">
                <h3 class="text-lg font-medium text-white mb-4">Card Abilities</h3>

                <!-- Existing Abilities -->
                <div v-if="card.abilities?.length" class="mb-4">
                  <h4 class="text-sm font-medium text-gray-300 mb-2">Vote for Existing Abilities (max 3)</h4>
                  <div class="space-y-2">
                    <div 
                      v-for="ability in card.abilities" 
                      :key="ability.id"
                      class="flex items-center space-x-3 p-3 bg-gray-700 rounded-md"
                    >
                      <input 
                        type="checkbox"
                        :value="ability.id"
                        v-model="form.ability_votes"
                        :disabled="form.ability_votes.length >= 3 && !form.ability_votes.includes(ability.id)"
                        class="text-purple-500 focus:ring-purple-500 bg-gray-600 border-gray-500 rounded"
                      >
                      <div>
                        <div class="font-medium text-white">{{ ability.name }}</div>
                        <div class="text-sm text-gray-400">{{ ability.description }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Recommend New Abilities -->
                <div>
                  <h4 class="text-sm font-medium text-gray-300 mb-2">Recommend New Abilities (max 2)</h4>
                  <div class="space-y-4">
                    <template v-for="i in 2" :key="i">
                      <div v-if="i === 1 || form.ability_recommendations[i-2].name" class="space-y-2">
                        <input 
                          type="text"
                          v-model="form.ability_recommendations[i-1].name"
                          :placeholder="`Ability ${i} Name`"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                        >
                        <textarea
                          v-model="form.ability_recommendations[i-1].description"
                          :placeholder="`Ability ${i} Description`"
                          rows="2"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2"
                        ></textarea>
                      </div>
                    </template>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <button 
                type="submit"
                :disabled="form.processing"
                class="w-full px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-all focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50"
              >
                {{ form.processing ? 'Submitting...' : 'Submit Vote' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Voting Guidelines -->
        <div class="mt-8 bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-4">Voting Guidelines</h3>
          <ul class="text-gray-300 list-disc pl-5 space-y-2">
            <li>Rate each stat honestly based on the card's content</li>
            <li>Consider balance when voting - not every card should be maximum power</li>
            <li>Abilities should be creative but reasonable</li>
            <li>You can vote for up to 3 existing abilities</li>
            <li>You can recommend up to 2 new abilities</li>
            <li>Your votes help determine the final card stats</li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { defineComponent } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

export default defineComponent({
  components: {
    AppLayout,
    Link,
  },

  props: {
    card: {
      type: Object,
      default: null,
    },
    isWeekend: {
      type: Boolean,
      required: true,
    },
  },

  setup(props) {
    const form = useForm({
      recommended_rarity: 5,
      recommended_hp: 50,
      recommended_strength: 50,
      recommended_defense: 50,
      ability_recommendations: [
        { name: '', description: '' },
        { name: '', description: '' },
      ],
      ability_votes: [],
    });

    const submit = () => {
      if (!props.card) return;

      // Filter out empty ability recommendations
      const filteredRecommendations = form.ability_recommendations.filter(a => a.name.trim());
      form.ability_recommendations = filteredRecommendations;

      form.post(route('cards.vote', props.card.id), {
        preserveScroll: true,
        onSuccess: () => {
          form.reset();
        },
      });
    };

    return {
      form,
      submit,
    };
  },
});
</script>

<style scoped>
.aspect-w-3 {
  position: relative;
  padding-bottom: calc(4 / 3 * 100%);
}

.aspect-w-3 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>