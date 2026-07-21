<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({ lead: Object, messages: Array, businessName: String });
const flash = usePage().props.flash;
const showSuccess = ref(!!flash.success);
const showNotice = ref(!!flash.chat_notice);
const chatMessages = ref([...props.messages]);
const form = useForm({ name: '', body: '' });

function send() {
  form.post(route('chat.store', props.lead.chat_token), {
    preserveScroll: true,
    onSuccess: () => form.reset('body'),
  });
}

function onChatKeydown(event) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    if (form.body.trim()) send();
  }
}

function onMessageCreated(event) {
  if (!chatMessages.value.find((item) => item.id === event.message.id)) chatMessages.value.push(event.message);
}

onMounted(() => {
  window.Echo?.channel(`lead-chat.${props.lead.chat_token}`).listen('.lead.message.created', onMessageCreated);
  if (showSuccess.value) setTimeout(() => { showSuccess.value = false; }, 120000);
  if (showNotice.value) setTimeout(() => { showNotice.value = false; }, 120000);
});

onBeforeUnmount(() => window.Echo?.leave(`lead-chat.${props.lead.chat_token}`));
</script>

<template>
  <Head :title="`${businessName} Chat`" />
  <div class="min-h-screen bg-gray-100 px-4 py-10 dark:bg-slate-950">
    <div class="mx-auto max-w-2xl rounded-xl border bg-white p-6 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ businessName }}</h1>
      <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">{{ lead.name }} | {{ lead.email }}</p>
      <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{{ lead.phone || 'No phone' }} | {{ lead.subject || 'No subject' }}</p>

      <p v-if="showSuccess" class="mb-3 rounded bg-green-100 p-2 text-sm text-green-700">{{ flash.success }}</p>
      <p v-if="showNotice" class="mb-4 rounded bg-blue-100 p-2 text-sm text-blue-700">{{ flash.chat_notice }}</p>

      <div class="mb-4 max-h-80 space-y-2 overflow-y-auto rounded border p-3 dark:border-[#2a2d3a]">
        <div class="rounded px-3 py-2 text-sm mr-10 bg-gray-100 text-gray-800 dark:bg-[#262a3a] dark:text-gray-100">
          <p class="mb-1 text-xs opacity-80">Original enquiry</p>
          <p>{{ lead.message }}</p>
        </div>
        <div v-for="message in chatMessages" :key="message.id" class="rounded px-3 py-2 text-sm" :class="message.sender_type === 'business' ? 'mr-10 bg-gray-100 text-gray-800 dark:bg-[#262a3a] dark:text-gray-100' : 'ml-10 bg-indigo-600 text-white'">
          <p class="mb-1 text-xs opacity-80">{{ message.sender_name || message.sender_type }}</p>
          <p>{{ message.body }}</p>
        </div>
      </div>

      <form @submit.prevent="send" class="space-y-2">
        <input v-model="form.name" placeholder="Your name (optional)" class="w-full rounded-md border-gray-300" />
        <textarea v-model="form.body" @keydown="onChatKeydown" rows="4" placeholder="Write your message and press Enter to send (Shift+Enter for newline)" class="w-full rounded-md border-gray-300" required></textarea>
      </form>
    </div>
  </div>
</template>
