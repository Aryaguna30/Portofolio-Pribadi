<template>
  <button
    class="btn-icon focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-transparent"
    :aria-label="locale === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia'"
    :title="locale === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia'"
    @click="toggleLanguage"
  >
    <span aria-hidden="true">{{ locale === 'id' ? 'ID' : 'EN' }}</span>
  </button>
</template>

<script setup>
import { useLanguage } from '@/composables/useLanguage';

const { locale, toggleLanguage } = useLanguage();
</script>
