<template>
  <Head>
    <title>{{ fullTitle }}</title>
    <meta name="description" :content="description" />
    <meta name="keywords" :content="keywords" />

    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:title" :content="ogTitle || fullTitle" />
    <meta property="og:description" :content="ogDescription || description" />
    <meta property="og:url" :content="ogUrl || currentUrl" />
    <meta v-if="ogImage" property="og:image" :content="ogImage" />
    <meta property="og:locale" content="id_ID" />
    <meta property="og:locale:alternate" content="en_US" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="ogTitle || fullTitle" />
    <meta name="twitter:description" :content="ogDescription || description" />
    <meta v-if="ogImage" name="twitter:image" :content="ogImage" />
  </Head>
</template>

<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  keywords: {
    type: String,
    default: '',
  },
  ogTitle: {
    type: String,
    default: '',
  },
  ogDescription: {
    type: String,
    default: '',
  },
  ogImage: {
    type: String,
    default: '',
  },
  ogUrl: {
    type: String,
    default: '',
  },
});

const page = usePage();

const siteName = 'Portfolio';

const fullTitle = computed(() =>
  props.title ? `${props.title} — ${siteName}` : siteName
);

const currentUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href;
  }
  return page.props.ziggy?.url ?? '';
});
</script>
