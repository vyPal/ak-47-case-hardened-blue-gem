<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            System Settings
          </h1>
          <Link
            :href="route('admin.dashboard')"
            class="text-gray-400 hover:text-white transition-colors"
          >
            Back to Dashboard
          </Link>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- General Settings -->
          <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-white mb-6">General Settings</h2>
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <!-- Default Invitation Limit -->
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Default Invitation Limit
                    <span class="text-gray-500 text-xs ml-1">
                      (For new users)
                    </span>
                  </label>
                  <input
                    v-model="form.default_invitation_limit"
                    type="number"
                    min="0"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                  >
                  <p class="mt-1 text-sm text-gray-500">
                    Number of invitations new users will be able to send
                  </p>
                </div>

                <div class="pt-4">
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-colors disabled:opacity-50"
                  >
                    {{ form.processing ? 'Saving...' : 'Save Settings' }}
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Card Settings -->
          <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-white mb-6">Card Settings</h2>
            <div class="space-y-6">
              <!-- Card Upload Schedule -->
              <div>
                <h3 class="text-sm font-medium text-gray-300 mb-2">Card Upload Schedule</h3>
                <div class="bg-gray-700 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-white">Upload Period</span>
                    <span class="text-green-400">Monday - Friday</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-white">Voting Period</span>
                    <span class="text-purple-400">Saturday - Sunday</span>
                  </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                  Schedule is fixed to ensure consistent card creation and evaluation cycles
                </p>
              </div>

              <!-- Current Status -->
              <div>
                <h3 class="text-sm font-medium text-gray-300 mb-2">Current Status</h3>
                <div class="bg-gray-700 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <span class="text-white">Current Period</span>
                    <span
                      :class="isWeekend ? 'text-purple-400' : 'text-green-400'"
                    >
                      {{ isWeekend ? 'Voting Period' : 'Upload Period' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Force Mint Cards -->
              <div v-if="isWeekend">
                <button
                  @click="showMintConfirmation = true"
                  class="w-full px-4 py-2 bg-gradient-to-br from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white rounded-md transition-colors"
                >
                  Force Mint All Cards in Voting
                </button>
                <p class="mt-1 text-sm text-gray-500">
                  Manually trigger the minting process for all cards in voting
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-gray-800 rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold text-white mb-4">Settings Help</h2>
          <div class="text-gray-300 space-y-4">
            <p>
              <strong class="text-white">Default Invitation Limit:</strong>
              This is the number of invitations that new users will receive when they join. Users can only invite others if they have available invitations.
            </p>
            <p>
              <strong class="text-white">Card Upload Schedule:</strong>
              The platform operates on a weekly cycle. Users can upload cards during weekdays, and voting/minting occurs on weekends.
            </p>
            <p>
              <strong class="text-white">Force Minting:</strong>
              While minting normally happens automatically, you can use the force mint option during the weekend to manually trigger the process.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Mint Confirmation Modal -->
    <Modal :show="showMintConfirmation" @close="showMintConfirmation = false">
      <div class="p-6">
        <h2 class="text-xl font-bold text-white mb-4">Force Mint Confirmation</h2>
        <p class="text-gray-300 mb-6">
          Are you sure you want to force mint all cards currently in voting? This action:
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Cannot be undone</li>
            <li>Will finalize all card stats based on current votes</li>
            <li>Will distribute card copies to users</li>
            <li>Should only be used if the automatic minting fails</li>
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
            @click="forceMint"
            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-colors"
          >
            Force Mint Now
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script>
import { defineComponent, ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';

export default defineComponent({
  components: {
    AppLayout,
    Link,
    Modal,
  },

  props: {
    settings: {
      type: Object,
      required: true,
    },
    isWeekend: {
      type: Boolean,
      required: true,
    },
  },

  setup(props) {
    const form = useForm({
      default_invitation_limit: props.settings.default_invitation_limit || 3,
    });

    const showMintConfirmation = ref(false);

    const submit = () => {
      form.put(route('admin.settings.update'));
    };

    const forceMint = () => {
      router.post(route('admin.mint-cards'), {}, {
        onSuccess: () => {
          showMintConfirmation.value = false;
        },
      });
    };

    return {
      form,
      showMintConfirmation,
      submit,
      forceMint,
    };
  },
});
</script>
