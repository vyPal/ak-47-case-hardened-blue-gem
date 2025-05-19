<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900">
    <div>
      <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500">
        CardBros
      </h1>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
      <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-white">Welcome to CardBros!</h2>
        <p class="text-gray-400 text-sm mt-2">You've been invited to join the private card trading platform</p>
      </div>

      <!-- Validation Errors -->
      <div v-if="Object.keys($page.props.errors).length > 0" class="mb-4 bg-red-900 border border-red-700 p-4 rounded-md">
        <div class="font-medium text-red-300 mb-1">Whoops! Please fix the following errors:</div>
        <ul class="text-sm text-red-300 list-disc list-inside">
          <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
        </ul>
      </div>

      <form @submit.prevent="submit">
        <!-- Name -->
        <div>
          <label for="name" class="block font-medium text-sm text-gray-300">Name</label>
          <input 
            id="name" 
            type="text" 
            v-model="form.name" 
            required 
            autofocus 
            autocomplete="name" 
            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-purple-500 focus:outline-none focus:ring focus:ring-purple-500 focus:ring-opacity-50"
          >
        </div>

        <!-- Email Address -->
        <div class="mt-4">
          <label for="email" class="block font-medium text-sm text-gray-300">Email</label>
          <input 
            id="email" 
            type="email" 
            v-model="form.email" 
            required 
            autocomplete="username" 
            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-purple-500 focus:outline-none focus:ring focus:ring-purple-500 focus:ring-opacity-50"
          >
        </div>

        <!-- Password -->
        <div class="mt-4">
          <label for="password" class="block font-medium text-sm text-gray-300">Password</label>
          <input 
            id="password" 
            type="password" 
            v-model="form.password" 
            required 
            autocomplete="new-password" 
            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-purple-500 focus:outline-none focus:ring focus:ring-purple-500 focus:ring-opacity-50"
          >
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
          <label for="password_confirmation" class="block font-medium text-sm text-gray-300">Confirm Password</label>
          <input 
            id="password_confirmation" 
            type="password" 
            v-model="form.password_confirmation" 
            required 
            autocomplete="new-password" 
            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-purple-500 focus:outline-none focus:ring focus:ring-purple-500 focus:ring-opacity-50"
          >
        </div>

        <div class="flex items-center justify-end mt-6">
          <button 
            type="submit" 
            class="w-full px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-all focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50"
            :disabled="form.processing"
          >
            Register
          </button>
        </div>
      </form>

      <div class="mt-6 flex items-center justify-center">
        <span class="text-sm text-gray-400">
          Already have an account? 
          <a :href="route('login')" class="text-purple-400 hover:text-purple-300 hover:underline">Log in</a>
        </span>
      </div>
    </div>

    <div class="mt-8 text-center text-gray-500 text-sm">
      <p>This is a private, invitation-only platform.</p>
      <p>Only people with invitation links can register.</p>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';

export default defineComponent({
  props: {
    token: {
      type: String,
      required: true
    }
  },

  setup(props) {
    const form = useForm({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    });

    const submit = () => {
      form.post(route('invitation.register', props.token));
    };

    return {
      form,
      submit
    };
  }
});
</script>