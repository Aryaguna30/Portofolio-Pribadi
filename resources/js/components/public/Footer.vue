<template>
  <footer class="footer">
    <div class="container footer__inner">
      <!-- Left: Logo -->
      <div class="footer__left">
        <a href="#home" class="footer__logo" @click.prevent="scrollToTop" aria-label="Kembali ke atas">
          <span class="logo-bracket">&lt;</span>
          <span class="logo-text">NA</span>
          <span class="logo-bracket">/&gt;</span>
        </a>
      </div>

      <!-- Center: Copyright -->
      <p class="footer__copy">
        &copy; {{ currentYear }} <span class="gradient-text">Nugraha Aryaguna</span>. {{ t('footer.copyright') }}
      </p>

      <!-- Right: Back to top -->
      <div class="footer__right">
        <a href="#home" class="footer__top" :aria-label="t('footer.backToTop')" @click.prevent="scrollToTop">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="18 15 12 9 6 15"/>
          </svg>
        </a>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = computed(() => new Date().getFullYear());

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
