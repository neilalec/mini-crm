<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({ business: Object });
const flash = usePage().props.flash;
const form = useForm({ name: '', email: '', phone: '', subject: '', message: '' });

function submit() { form.post(route('enquiry.store', props.business.slug)); }
</script>

<template>
  <Head :title="`${business.name} Enquiry`" />
  <div class="min-h-screen bg-gray-100 px-4 py-10">
    <div class="mx-auto max-w-2xl rounded-xl border bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-semibold text-gray-900">{{ business.name }}</h1>
      <p class="mb-6 text-sm text-gray-600">Send us your enquiry and we will get back to you.</p>
      <p v-if="flash.success" class="mb-4 rounded bg-green-100 p-2 text-sm text-green-700">{{ flash.success }}</p>
      <form @submit.prevent="submit" class="space-y-3">
        <input v-model="form.name" placeholder="Your name" class="w-full rounded-md border-gray-300" required />
        <input v-model="form.email" type="email" placeholder="Email" class="w-full rounded-md border-gray-300" required />
        <input v-model="form.phone" placeholder="Phone" class="w-full rounded-md border-gray-300" />
        <input v-model="form.subject" placeholder="Subject" class="w-full rounded-md border-gray-300" />
        <textarea v-model="form.message" rows="5" placeholder="Tell us what you need" class="w-full rounded-md border-gray-300" required></textarea>
        <button class="rounded bg-indigo-600 px-4 py-2 text-white">Send Enquiry</button>
      </form>
    </div>
  </div>
</template>
