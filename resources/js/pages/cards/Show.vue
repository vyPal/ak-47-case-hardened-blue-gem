<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <Link :href="route('cards.index')" class="mr-4 text-gray-400 transition-colors hover:text-white"> Back to Cards </Link>
                        <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                            {{ card.name }}
                        </h1>
                    </div>
                    <div v-if="isOwner && card.status === 'draft'" class="flex items-center space-x-4">
                        <Link :href="route('cards.edit', card.id)" class="text-purple-400 transition-colors hover:text-purple-300"> Edit </Link>
                        <button
                            @click="submitForVoting"
                            class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600"
                        >
                            Submit for Voting
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <!-- Card Image and Basic Info -->
                    <div class="md:col-span-1">
                        <div class="overflow-hidden rounded-lg bg-gray-800 shadow-lg">
                            <!-- Card Image -->
                            <div class="aspect-w-3 aspect-h-4">
                                <img :src="`/storage/${card.image_path}`" :alt="card.name" class="h-full w-full object-cover" />
                            </div>

                            <!-- Card Status -->
                            <div class="border-t border-gray-700 p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-gray-400">Status</span>
                                    <StatusBadge :status="card.status" />
                                </div>
                                <div v-if="card.status === 'minted'" class="mb-4 flex items-center justify-between">
                                    <span class="text-gray-400">Total minted</span>
                                    <span class="text-gray-400">{{ card.minted_count }}</span>
                                </div>

                                <!-- Creation Info -->
                                <div class="text-sm text-gray-400">
                                    <div class="mb-2 flex justify-between">
                                        <span>Created by</span>
                                        <span class="text-white">{{ card.user.name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Upload date</span>
                                        <span class="text-white">{{ formatDate(card.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Details -->
                    <div class="md:col-span-2">
                        <!-- Description -->
                        <div class="mb-6 rounded-lg bg-gray-800 p-6">
                            <h2 class="mb-4 text-xl font-semibold text-white">Description</h2>
                            <p class="text-gray-300" v-if="card.description">{{ card.description }}</p>
                            <p class="text-gray-500 italic" v-else>No description provided</p>
                        </div>

                        <!-- Stats (if minted) -->
                        <div v-if="card.status === 'minted'" class="mb-6 rounded-lg bg-gray-800 p-6">
                            <h2 class="mb-4 text-xl font-semibold text-white">Card Stats</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-gray-700 p-4">
                                    <div class="mb-1 text-sm text-gray-400">Rarity</div>
                                    <div class="text-2xl font-bold text-white">{{ card.rarity }}/10</div>
                                </div>
                                <div class="rounded-lg bg-gray-700 p-4">
                                    <div class="mb-1 text-sm text-gray-400">HP</div>
                                    <div class="text-2xl font-bold text-white">{{ card.hp }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-700 p-4">
                                    <div class="mb-1 text-sm text-gray-400">Strength</div>
                                    <div class="text-2xl font-bold text-white">{{ card.strength }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-700 p-4">
                                    <div class="mb-1 text-sm text-gray-400">Defense</div>
                                    <div class="text-2xl font-bold text-white">{{ card.defense }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Abilities (if minted) -->
                        <div v-if="card.status === 'minted' && card.abilities?.length" class="mb-6 rounded-lg bg-gray-800 p-6">
                            <h2 class="mb-4 text-xl font-semibold text-white">Abilities</h2>
                            <div class="space-y-4">
                                <div v-for="ability in card.abilities" :key="ability.id" class="rounded-lg bg-gray-700 p-4">
                                    <div class="mb-1 font-medium text-white">{{ ability.name }}</div>
                                    <div class="text-sm text-gray-400">{{ ability.description }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Voting Section (if in voting) -->
                        <div v-if="card.status === 'voting'" class="mb-6 rounded-lg bg-gray-800 p-6">
                            <h2 class="mb-4 text-xl font-semibold text-white">Voting</h2>

                            <!-- If user hasn't voted yet -->
                            <div v-if="!userVotes">
                                <p class="mb-4 text-gray-300">Cast your vote to help determine this card's stats and abilities!</p>
                                <Link
                                    :href="route('cards.voting-feed')"
                                    class="inline-flex items-center rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600"
                                >
                                    Vote Now
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </Link>
                            </div>

                            <!-- If user has voted -->
                            <div v-else>
                                <div class="mb-4 text-gray-300">You've already voted on this card!</div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-gray-700 p-4">
                                        <div class="mb-1 text-sm text-gray-400">Your Rarity Vote</div>
                                        <div class="text-xl font-bold text-white">{{ userVotes.recommended_rarity }}/10</div>
                                    </div>
                                    <div class="rounded-lg bg-gray-700 p-4">
                                        <div class="mb-1 text-sm text-gray-400">Your HP Vote</div>
                                        <div class="text-xl font-bold text-white">{{ userVotes.recommended_hp }}</div>
                                    </div>
                                    <div class="rounded-lg bg-gray-700 p-4">
                                        <div class="mb-1 text-sm text-gray-400">Your Strength Vote</div>
                                        <div class="text-xl font-bold text-white">{{ userVotes.recommended_strength }}</div>
                                    </div>
                                    <div class="rounded-lg bg-gray-700 p-4">
                                        <div class="mb-1 text-sm text-gray-400">Your Defense Vote</div>
                                        <div class="text-xl font-bold text-white">{{ userVotes.recommended_defense }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ownership Info -->
                        <div v-if="card.status === 'minted' && ownershipInfo" class="rounded-lg bg-gray-800 p-6">
                            <h2 class="mb-4 text-xl font-semibold text-white">Card Ownership</h2>
                            <div class="divide-y divide-gray-700">
                                <div v-for="(info, userId) in ownershipInfo" :key="userId" class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="font-medium text-white">{{ info.user.name }}</span>
                                            <span
                                                v-if="info.user.id === card.user_id"
                                                class="ml-2 rounded-full bg-purple-900 px-2 py-1 text-xs text-purple-300"
                                            >
                                                Creator
                                            </span>
                                        </div>
                                        <div class="text-gray-400">Owns {{ info.quantity }} {{ info.quantity === 1 ? 'copy' : 'copies' }}</div>
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

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineComponent } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        Link,
        StatusBadge,
    },

    props: {
        card: {
            type: Object,
            required: true,
        },
        userVotes: {
            type: Object,
            default: null,
        },
        abilityVotes: {
            type: Object,
            default: () => ({}),
        },
        ownershipInfo: {
            type: Object,
            default: null,
        },
        isOwner: {
            type: Boolean,
            required: true,
        },
        isAdmin: {
            type: Boolean,
            required: true,
        },
    },

    setup(props) {
        const submitForVoting = () => {
            if (confirm("Are you sure you want to submit this card for voting? You won't be able to edit it afterwards.")) {
                router.post(route('cards.submit-for-voting', props.card.id));
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        };

        return {
            submitForVoting,
            formatDate,
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
