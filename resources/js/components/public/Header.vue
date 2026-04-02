<template>
  <header
    class="fixed top-0 left-0 right-0 z-[100] py-4 transition-all duration-300"
    :class="{ 'bg-[rgba(10,14,26,0.85)] dark:bg-[rgba(10,14,26,0.85)] backdrop-blur-xl border-b border-white/[0.08] scrolled': isScrolled }"
    id="header"
  >
    <div class="max-w-[1100px] mx-auto px-6 flex items-center gap-8">

      <!-- Logo -->
      <a href="#home" class="flex items-center gap-1.5 font-['Fira_Code',monospace] text-base font-bold flex-shrink-0 group" @click.prevent="scrollTo('#home')">
        <span class="text-purple-500 text-[1.05rem] transition-colors group-hover:text-cyan-400">&lt;</span>
        <span class="bg-gradient-to-br from-purple-500 to-cyan-400 bg-clip-text text-transparent text-[1.05rem]">NA</span>
        <span class="text-purple-500 text-[1.05rem] transition-colors group-hover:text-cyan-400">/&gt;</span>
        <span class="font-['Inter',sans-serif] text-[0.82rem] font-semibold text-gray-400 tracking-wide ml-0.5 transition-colors group-hover:text-white">
          Nugraha<span class="text-white ml-0.5 transition-colors">Aryaguna</span>
        </span>
      </a>

      <!-- Desktop Navigation -->
      <nav class="hidden md:flex gap-8 ml-auto" aria-label="Navigasi utama">
        <a
          v-for="item in navItems"
          :key="item.anchor"
          :href="item.anchor"
          class="text-sm font-medium text-gray-400 transition-colors hover:text-white relative nav-link focus:outline-none rounded"
          @click.prevent="scrollTo(item.anchor)"
        >
          {{ item.label }}
        </a>
      </nav>

      <!-- Actions -->
      <div class="flex items-center gap-3 ml-auto md:ml-0">
        <LanguageToggle />
        <ThemeToggle />

        <!-- CV Button: link when available, disabled button with tooltip when not -->
        <template v-if="cvUrl">
          <a
            :href="cvUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm text-white bg-gradient-to-br from-purple-600 to-purple-800 shadow-[0_0_30px_rgba(124,58,237,0.3)] hover:shadow-[0_0_50px_rgba(124,58,237,0.5)] hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-transparent"
            :aria-label="t('nav.downloadCv')"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            {{ t('nav.downloadCv') }}
          </a>
        </template>
        <template v-else>
          <div class="relative hidden sm:block group">
            <button
              disabled
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm text-white/40 bg-gradient-to-br from-purple-600/40 to-purple-800/40 cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:ring-offset-2 focus:ring-offset-transparent"
              :aria-label="t('nav.cvNotAvailable')"
              :title="t('nav.cvNotAvailable')"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              {{ t('nav.downloadCv') }}
            </button>
            <!-- Tooltip -->
            <div
              class="absolute top-full left-1/2 -translate-x-1/2 mt-2 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-gray-900 border border-white/10 whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity duration-200 z-10"
              role="tooltip"
            >
              {{ t('nav.cvNotAvailable') }}
              <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 border-l border-t border-white/10 rotate-45"></div>
            </div>
          </div>
        </template>

        <!-- Hamburger (mobile) -->
        <button
          class="flex md:hidden flex-col gap-[5px] p-2.5 min-w-[44px] min-h-[44px] items-center justify-center focus:outline-none focus:ring-2 focus:ring-purple-500 rounded"
          :aria-expanded="mobileMenuOpen"
          :aria-label="mobileMenuOpen ? 'Tutup menu' : 'Buka menu'"
          @click="toggleMobileMenu"
        >
          <span
            class="block w-[22px] h-[2px] bg-white rounded-sm transition-all duration-300"
            :class="{ 'translate-y-[7px] rotate-45': mobileMenuOpen }"
          ></span>
          <span
            class="block w-[22px] h-[2px] bg-white rounded-sm transition-all duration-300"
            :class="{ 'opacity-0': mobileMenuOpen }"
          ></span>
          <span
            class="block w-[22px] h-[2px] bg-white rounded-sm transition-all duration-300"
            :class="{ '-translate-y-[7px] -rotate-45': mobileMenuOpen }"
          ></span>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <nav
      v-show="mobileMenuOpen"
      class="md:hidden fixed top-16 left-0 right-0 bg-[#131929] border-b border-white/[0.08] px-6 py-6 flex flex-col gap-5 z-[99]"
      aria-label="Navigasi mobile"
    >
      <a
        v-for="item in navItems"
        :key="item.anchor"
        :href="item.anchor"
        class="text-sm font-medium text-gray-400 hover:text-white transition-colors focus:outline-none rounded"
        @click.prevent="scrollToAndClose(item.anchor)"
      >
        {{ item.label }}
      </a>

      <!-- CV button in mobile menu -->
      <template v-if="cvUrl">
        <a
          :href="cvUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm text-white bg-gradient-to-br from-purple-600 to-purple-800 w-fit focus:outline-none focus:ring-2 focus:ring-purple-500"
          :aria-label="t('nav.downloadCv')"
          @click="mobileMenuOpen = false"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          {{ t('nav.downloadCv') }}
        </a>
      </template>
      <template v-else>
        <button
          disabled
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm text-white/40 bg-gradient-to-br from-purple-600/40 to-purple-800/40 cursor-not-allowed w-fit"
          :aria-label="t('nav.cvNotAvailable')"
          :title="t('nav.cvNotAvailable')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          {{ t('nav.downloadCv') }}
        </button>
      </template>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import ThemeToggle from '@/components/shared/ThemeToggle.vue';
import LanguageToggle from '@/components/shared/LanguageToggle.vue';
import { useTheme } from '@/composables/useTheme';
import { useLanguage } from '@/composables/useLanguage';

const props = defineProps({
  cvUrl: {
    type: String,
    default: null,
  },
  currentLocale: {
    type: String,
    default: 'id',
  },
  currentTheme: {
    type: String,
    default: 'dark',
  },
});

const { t } = useI18n();
useTheme();
useLanguage();

// Scroll state
const isScrolled = ref(false);

function handleScroll() {
  isScrolled.value = window.scrollY > 50;
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Mobile menu
const mobileMenuOpen = ref(false);

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value;
}

// Navigation items
const navItems = computed(() => [
  { anchor: '#home',      label: t('nav.home') },
  { anchor: '#about',     label: t('nav.about') },
  { anchor: '#portfolio', label: t('nav.portfolio') },
  { anchor: '#stats',     label: t('nav.stats') },
  { anchor: '#contact',   label: t('nav.contact') },
]);

// Smooth scroll
function scrollTo(anchor) {
  const el = document.querySelector(anchor);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
}

function scrollToAndClose(anchor) {
  mobileMenuOpen.value = false;
  scrollTo(anchor);
}
</script>

<style scoped>
.nav-link::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #7c3aed, #06b6d4);
  transform: scaleX(0);
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 2px;
}

.nav-link:hover::after {
  transform: scaleX(1);
}
</style>
