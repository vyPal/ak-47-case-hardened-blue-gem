<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center">
                    <InertiaLink :href="route('trades.index')" class="mr-4 flex items-center text-blue-400 hover:text-blue-300">
                        <ArrowLeft class="mr-1 h-4 w-4" />
                        Back to Trades
                    </InertiaLink>
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        Create New Trade
                    </h1>
                </div>

                <!-- Trade creation form -->
                <form @submit.prevent="submitTrade" class="rounded-lg bg-gray-800 p-6">
                    <!-- Select recipient -->
                    <div class="mb-6">
                        <label for="recipient" class="mb-2 block text-sm font-medium text-gray-200">Trading with:</label>
                        <select
                            id="recipient"
                            v-model="form.recipient_id"
                            class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            @change="loadRecipientCards"
                            :disabled="!!recipient"
                        >
                            <option value="" disabled>Select a user</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <div v-if="form.errors.recipient_id" class="mt-1 text-sm text-red-400">{{ form.errors.recipient_id }}</div>
                    </div>

                    <!-- Optional message -->
                    <div class="mb-6">
                        <label for="message" class="mb-2 block text-sm font-medium text-gray-200">Message (optional):</label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="3"
                            class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            placeholder="Add a message to go with your trade offer..."
                        ></textarea>
                        <div v-if="form.errors.message" class="mt-1 text-sm text-red-400">{{ form.errors.message }}</div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Your offers -->
                        <div>
                            <h2 class="mb-4 flex items-center text-xl font-bold text-white">
                                <SendHorizonal class="mr-2 h-5 w-5 text-purple-400" />
                                Your Offer
                            </h2>

                            <!-- Card selection -->
                            <div class="mb-4 rounded-lg bg-gray-700 p-4">
                                <div v-if="myCards.length === 0" class="py-4 text-center text-gray-400">You don't have any cards to trade.</div>
                                <div v-else>
                                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                        <div
                                            v-for="ownership in myCards"
                                            :key="ownership.id"
                                            class="relative cursor-pointer overflow-hidden rounded-lg border-2"
                                            :class="isCardSelected(ownership, 'my_cards') ? 'border-purple-500' : 'border-transparent'"
                                            @click="toggleCardSelection(ownership, 'my_cards')"
                                        >
                                            <img
                                                :src="`/storage/${ownership.card.image_path}`"
                                                :alt="ownership.card.name"
                                                class="h-32 w-full object-cover"
                                            />
                                            <div class="bg-opacity-70 absolute right-0 bottom-0 left-0 bg-black px-2 py-1">
                                                <div class="truncate text-xs text-white">{{ ownership.card.name }}</div>
                                                <div class="text-xs text-gray-300">Qty: {{ ownership.quantity }}</div>
                                            </div>
                                            <div v-if="isCardSelected(ownership, 'my_cards')" class="absolute top-2 right-2">
                                                <div class="rounded-full bg-purple-500 p-1">
                                                    <Check class="h-3 w-3 text-white" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected cards -->
                            <div v-if="form.my_cards.length > 0">
                                <h3 class="mb-2 text-sm font-medium text-gray-300">Selected cards:</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="(cardData, index) in form.my_cards"
                                        :key="index"
                                        class="flex items-center justify-between rounded bg-gray-700 p-2"
                                    >
                                        <div class="flex items-center">
                                            <img
                                                :src="`/storage/${findCard(cardData.card_id)?.image_path}`"
                                                :alt="findCard(cardData.card_id)?.name"
                                                class="mr-2 h-10 w-10 rounded object-cover"
                                            />
                                            <div>
                                                <div class="text-sm text-white">{{ findCard(cardData.card_id)?.name }}</div>
                                                <div class="text-xs text-gray-400">Serial #{{ cardData.serial_numbers.join(', ') }}</div>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeSelectedCard(index, 'my_cards')" class="text-red-400 hover:text-red-300">
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.my_cards" class="mt-1 text-sm text-red-400">{{ form.errors.my_cards }}</div>
                        </div>

                        <!-- Their offers -->
                        <div>
                            <h2 class="mb-4 flex items-center text-xl font-bold text-white">
                                <ReceiptIcon class="mr-2 h-5 w-5 text-blue-400" />
                                You Request
                            </h2>

                            <div v-if="!form.recipient_id" class="rounded-lg bg-gray-700 py-8 text-center text-gray-400">
                                Select a user to see their available cards
                            </div>

                            <template v-else>
                                <!-- Card selection -->
                                <div class="mb-4 rounded-lg bg-gray-700 p-4">
                                    <div v-if="recipientCards.length === 0" class="py-4 text-center text-gray-400">
                                        This user doesn't have any cards to trade.
                                    </div>
                                    <div v-else>
                                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                            <div
                                                v-for="ownership in recipientCards"
                                                :key="ownership.id"
                                                class="relative cursor-pointer overflow-hidden rounded-lg border-2"
                                                :class="isCardSelected(ownership, 'recipient_cards') ? 'border-blue-500' : 'border-transparent'"
                                                @click="toggleCardSelection(ownership, 'recipient_cards')"
                                            >
                                                <img
                                                    :src="`/storage/${ownership.card.image_path}`"
                                                    :alt="ownership.card.name"
                                                    class="h-32 w-full object-cover"
                                                />
                                                <div class="bg-opacity-70 absolute right-0 bottom-0 left-0 bg-black px-2 py-1">
                                                    <div class="truncate text-xs text-white">{{ ownership.card.name }}</div>
                                                    <div class="text-xs text-gray-300">Qty: {{ ownership.quantity }}</div>
                                                </div>
                                                <div v-if="isCardSelected(ownership, 'recipient_cards')" class="absolute top-2 right-2">
                                                    <div class="rounded-full bg-blue-500 p-1">
                                                        <Check class="h-3 w-3 text-white" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected cards -->
                                <div v-if="form.recipient_cards.length > 0">
                                    <h3 class="mb-2 text-sm font-medium text-gray-300">Selected cards:</h3>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(cardData, index) in form.recipient_cards"
                                            :key="index"
                                            class="flex items-center justify-between rounded bg-gray-700 p-2"
                                        >
                                            <div class="flex items-center">
                                                <img
                                                    :src="`/storage/${findCard(cardData.card_id)?.image_path}`"
                                                    :alt="findCard(cardData.card_id)?.name"
                                                    class="mr-2 h-10 w-10 rounded object-cover"
                                                />
                                                <div>
                                                    <div class="text-sm text-white">{{ findCard(cardData.card_id)?.name }}</div>
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        <span
                                                            v-for="serial in cardData.serial_numbers"
                                                            :key="serial"
                                                            class="inline-flex items-center rounded-full bg-gray-600 px-2 py-0.5 text-xs font-medium text-gray-200"
                                                        >
                                                            #{{ serial }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeSelectedCard(index, 'my_cards')"
                                                class="text-red-400 hover:text-red-300"
                                            >
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-4">
                        <InertiaLink
                            :href="route('trades.index')"
                            class="rounded-md bg-gray-600 px-4 py-2 text-white transition-all hover:bg-gray-500"
                        >
                            Cancel
                        </InertiaLink>
                        <button
                            type="submit"
                            class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600"
                            :disabled="form.processing"
                        >
                            <div class="flex items-center">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <span>Send Trade Offer</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Serial Number Selection Modal -->
        <Modal :show="serialSelectModalOpen" @close="serialSelectModalOpen = false" max-width="md">
            <div class="p-6">
                <h2 class="mb-4 text-xl font-bold text-white">Select Serial Numbers</h2>
                <p class="mb-4 text-gray-300">Select the specific serial numbers you want to include in this trade:</p>

                <div v-if="selectedCard && availableSerials.length" class="mb-4">
                    <div class="mb-2 flex items-center">
                        <img
                            v-if="selectedCard.card"
                            :src="`/storage/${selectedCard.card.image_path}`"
                            :alt="selectedCard.card.name"
                            class="mr-3 h-16 w-16 rounded object-cover"
                        />
                        <div>
                            <div class="font-medium text-white">{{ selectedCard.card?.name }}</div>
                            <div class="text-sm text-gray-400">{{ availableSerials.length }} serial numbers available</div>
                        </div>
                    </div>

                    <div class="max-h-60 overflow-y-auto rounded-lg bg-gray-700 p-3">
                        <div class="space-y-2">
                            <label
                                v-for="serial in availableSerials"
                                :key="serial"
                                class="flex cursor-pointer items-center rounded border border-gray-600 p-2 hover:bg-gray-600"
                                :class="{ 'bg-gray-600': isSerialSelected(serial) }"
                            >
                                <input type="checkbox" :checked="isSerialSelected(serial)" @click="toggleSerialNumber(serial)" class="mr-3" />
                                <span class="text-white">Serial #{{ serial }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                    <button
                        @click="serialSelectModalOpen = false"
                        class="rounded-md bg-gray-600 px-4 py-2 text-white transition-all hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="selectSerialNumbers"
                        class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600"
                        :disabled="selectedCardSerials.length === 0"
                    >
                        Add Selected ({{ selectedCardSerials.length }})
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link as InertiaLink, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Loader2, ReceiptIcon, SendHorizonal, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    myCards: Array,
    recipient: Object,
    recipientCards: Array,
});

// Form state
const form = useForm({
    recipient_id: props.recipient?.id || '',
    message: '',
    my_cards: [],
    recipient_cards: [],
});

// Track selected serial numbers
const selectedSerials = ref({
    my_cards: {},
    recipient_cards: {},
});

// For serial number selection modal
const serialSelectModalOpen = ref(false);
const selectedCard = ref(null);
const selectedCardType = ref(null);
const availableSerials = ref([]);
const selectedCardSerials = ref([]);

// Helper functions for card selection
function isCardSelected(ownership, type) {
    return !!selectedSerials.value[type][ownership.card_id];
}

function toggleCardSelection(ownership, type) {
    // If card is not selected yet, show serial number selection modal
    if (!isCardSelected(ownership, type)) {
        showSerialSelectModal(ownership, type);
    } else {
        // Remove this card from selection
        const cardIndex = form[type].findIndex((c) => c.card_id === ownership.card_id);
        if (cardIndex !== -1) {
            form[type].splice(cardIndex, 1);
        }
        delete selectedSerials.value[type][ownership.card_id];
    }
}

function showSerialSelectModal(ownership, type) {
    selectedCard.value = ownership;
    selectedCardType.value = type;
    availableSerials.value = [...(ownership.serial_numbers || [])];
    selectedCardSerials.value = [];
    serialSelectModalOpen.value = true;
}

function selectSerialNumbers() {
    const ownership = selectedCard.value;
    const type = selectedCardType.value;

    if (selectedCardSerials.value.length > 0) {
        form[type].push({
            card_id: ownership.card_id,
            serial_numbers: [...selectedCardSerials.value],
        });

        selectedSerials.value[type][ownership.card_id] = true;
    }

    // Close modal
    serialSelectModalOpen.value = false;
}

function toggleSerialNumber(serialNumber) {
    const index = selectedCardSerials.value.indexOf(serialNumber);
    if (index === -1) {
        selectedCardSerials.value.push(serialNumber);
    } else {
        selectedCardSerials.value.splice(index, 1);
    }
}

function isSerialSelected(serialNumber) {
    return selectedCardSerials.value.includes(serialNumber);
}

function removeSelectedCard(index, type) {
    const cardId = form[type][index].card_id;
    form[type].splice(index, 1);
    delete selectedSerials.value[type][cardId];
}

function findCard(cardId) {
    const ownership = props.myCards.find((o) => o.card_id === cardId);
    return ownership?.card;
}

function findRecipientCard(cardId) {
    const ownership = props.recipientCards?.find((o) => o.card_id === cardId);
    return ownership?.card;
}

function loadRecipientCards() {
    // Clear any previously selected recipient cards
    form.recipient_cards = [];
    selectedSerials.value.recipient_cards = {};

    // Redirect to the create page with the recipient parameter
    if (form.recipient_id) {
        router.visit(route('trades.create', { recipient: form.recipient_id }), {
            preserveState: true,
            replace: true,
        });
    }
}

function submitTrade() {
    // Validate that at least one card is offered
    if (form.my_cards.length === 0) {
        form.setError('my_cards', 'You must offer at least one card.');
        return;
    }

    form.post(route('trades.store'), {
        onSuccess: () => {
            // Reset form
            form.reset();
            selectedSerials.value = {
                my_cards: {},
                recipient_cards: {},
            };
        },
    });
}
</script>
