<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center">
      <!-- 500 gradient number -->
      <h1
        class="text-[10rem] md:text-[14rem] font-black leading-none bg-gradient-to-r from-purple-500 to-cyan-400 bg-clip-text text-transparent select-none"
        aria-hidden="true"
      >
        500
      </h1>

      <!-- Message -->
      <h2 class="text-2xl md:text-3xl font-bold text-white mt-4 mb-3">
        {{ $t('errors.serverError.title') }}
      </h2>
      <p class="text-gray-400 text-base md:text-lg mb-10 max-w-md mx-auto">
        {{ $t('errors.serverError.message') }}
      </p>

      <!-- Back to home button -->
      <Link
        href="/"
        class="inline-flex items-center gap-2 px-8 py-3 rounded-full font-semibold text-white bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-500 hover:to-cyan-400 transition-all duration-300 shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:-translate-y-0.5"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ $t('errors.notFound.back') }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
</script>
