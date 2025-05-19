<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
            Edit User: {{ user.name }}
          </h1>
          <Link 
            :href="route('admin.users')" 
            class="text-gray-400 hover:text-white transition-colors"
          >
            Back to Users
          </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- User Details Form -->
          <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6">
              <form @submit.prevent="submit">
                <!-- Name -->
                <div class="mb-6">
                  <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Name
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required
                  >
                </div>

                <!-- Admin Status -->
                <div class="mb-6">
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="form.is_admin"
                      :disabled="user.id === $page.props.auth.user?.id"
                      class="rounded bg-gray-700 border-gray-600 text-purple-500 focus:ring-purple-500"
                    >
                    <span class="ml-2 text-sm text-gray-300">
                      Admin User
                      <span v-if="user.id === $page.props.auth.user?.id" class="text-gray-500">
                        (Cannot change own admin status)
                      </span>
                    </span>
                  </label>
                </div>

                <!-- Invitation Settings -->
                <div class="mb-6">
                  <h3 class="text-lg font-medium text-white mb-4">Invitation Settings</h3>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="invitation_limit" class="block text-sm font-medium text-gray-300 mb-2">
                        Invitation Limit
                      </label>
                      <input
                        id="invitation_limit"
                        v-model="form.invitation_limit"
                        type="number"
                        min="0"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                      >
                    </div>
                    <div>
                      <label for="invitation_count" class="block text-sm font-medium text-gray-300 mb-2">
                        Available Invitations
                      </label>
                      <input
                        id="invitation_count"
                        v-model="form.invitation_count"
                        type="number"
                        min="0"
                        :max="form.invitation_limit"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                      >
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-colors disabled:opacity-50"
                  >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- User Stats -->
          <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6">
              <h3 class="text-lg font-medium text-white mb-4">User Statistics</h3>
              
              <!-- Email -->
              <div class="mb-4">
                <div class="text-sm text-gray-400">Email</div>
                <div class="text-white">{{ user.email }}</div>
              </div>

              <!-- Join Date -->
              <div class="mb-4">
                <div class="text-sm text-gray-400">Joined</div>
                <div class="text-white">{{ formatDate(user.created_at) }}</div>
              </div>

              <!-- Cards Stats -->
              <div class="mb-4">
                <div class="text-sm text-gray-400 mb-2">Cards</div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="bg-gray-700 rounded-lg p-3">
                    <div class="text-2xl font-bold text-white">{{ user.cards?.length || 0 }}</div>
                    <div class="text-xs text-gray-400">Uploaded</div>
                  </div>
                  <div class="bg-gray-700 rounded-lg p-3">
                    <div class="text-2xl font-bold text-white">{{ user.owned_cards?.length || 0 }}</div>
                    <div class="text-xs text-gray-400">Owned</div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div>
                <div class="text-sm text-gray-400 mb-2">Recent Activity</div>
                <div class="space-y-2">
                  <div v-for="card in recentCards" :key="card.id" class="bg-gray-700 rounded-lg p-3">
                    <div class="text-sm text-white">{{ card.name }}</div>
                    <div class="text-xs text-gray-400">{{ formatDate(card.created_at) }}</div>
                  </div>
                  <div v-if="!recentCards.length" class="text-sm text-gray-500 text-center py-3">
                    No recent activity
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
import { defineComponent, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

export default defineComponent({
  components: {
    AppLayout,
    Link,
  },

  props: {
    user: {
      type: Object,
      required: true,
    },
  },

  setup(props) {
    const form = useForm({
      name: props.user.name,
      is_admin: props.user.is_admin,
      invitation_limit: props.user.invitation_limit,
      invitation_count: props.user.invitation_count,
    });

    const recentCards = computed(() => {
      return (props.user.cards || [])
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5);
    });

    const formatDate = (date) => {
      if (!date) return '';
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }).format(new Date(date));
    };

    const submit = () => {
      form.put(route('admin.users.update', props.user.id));
    };

    return {
      form,
      recentCards,
      formatDate,
      submit,
    };
  },
});
</script>