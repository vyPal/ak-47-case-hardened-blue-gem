<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        Upload New Card
                    </h1>
                    <Link :href="route('cards.index')" class="text-gray-400 transition-colors hover:text-white"> Back to Cards </Link>
                </div>

                <div class="overflow-hidden rounded-lg bg-gray-800 shadow-xl">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Validation Errors -->
                        <div v-if="form.errors" class="mb-6 rounded-md border border-red-700 bg-red-900 p-4">
                            <div class="mb-1 font-medium text-red-300">Whoops! Please fix the following errors:</div>
                            <ul class="list-inside list-disc text-sm text-red-300">
                                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                            </ul>
                        </div>

                        <!-- Card Preview -->
                        <div class="mb-6">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Card Preview</label>
                            <div class="aspect-w-3 aspect-h-4 overflow-hidden rounded-lg border-2 border-dashed border-gray-700 bg-gray-900">
                                <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" alt="Card preview" />
                                <div v-else class="flex items-center justify-center text-gray-500">
                                    <div class="text-center">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mx-auto mb-2 h-12 w-12"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span class="block text-sm">No image selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-6">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Card Image</label>
                            <div
                                class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-700 px-6 pt-5 pb-6 transition-colors hover:border-purple-500"
                            >
                                <div class="space-y-1 text-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        />
                                    </svg>
                                    <div class="text-sm text-gray-400">
                                        <label
                                            for="file-upload"
                                            class="relative cursor-pointer rounded-md font-medium text-purple-400 focus-within:outline-none hover:text-purple-300"
                                        >
                                            <span>Upload a file</span>
                                            <input
                                                id="file-upload"
                                                name="file-upload"
                                                type="file"
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Name -->
                        <div class="mb-6">
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-300">Card Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                required
                            />
                        </div>

                        <!-- Card Description -->
                        <div class="mb-6">
                            <label for="description" class="mb-2 block text-sm font-medium text-gray-300">Description (Optional)</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600 focus:ring-2 focus:ring-purple-500 focus:outline-none disabled:opacity-50"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Card' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidelines -->
                <div class="mt-8 rounded-lg bg-gray-800 p-6">
                    <h3 class="mb-4 text-lg font-medium text-white">Upload Guidelines</h3>
                    <ul class="list-disc space-y-2 pl-5 text-gray-300">
                        <li>Cards can only be uploaded Monday through Friday</li>
                        <li>Choose clear, high-quality images that showcase the subject well</li>
                        <li>Make sure you have permission to use the image</li>
                        <li>Keep names and descriptions appropriate</li>
                        <li>Once submitted for voting, cards cannot be edited</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import DevModeBadge from '@/components/DevModeBadge.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { defineComponent, ref } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        DevModeBadge,
        Link,
    },

    setup() {
        const form = useForm({
            name: '',
            description: '',
            image: null,
        });

        const imagePreview = ref(null);

        const handleImageUpload = (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Preview the image
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.value = e.target.result;
            };
            reader.readAsDataURL(file);

            // Set the file in the form
            form.image = file;
        };

        const submit = () => {
            form.post(route('cards.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    imagePreview.value = null;
                },
            });
        };

        return {
            form,
            imagePreview,
            handleImageUpload,
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
