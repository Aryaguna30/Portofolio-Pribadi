<template>
  <div class="min-h-screen">
    <!-- Noise overlay -->
    <div class="noise" aria-hidden="true"></div>

    <!-- Custom cursor (desktop only) -->
    <div
      ref="cursorRef"
      class="cursor"
      aria-hidden="true"
      :style="{ left: cursorX + 'px', top: cursorY + 'px' }"
    ></div>
    <div
      ref="followerRef"
      class="cursor-follower"
      aria-hidden="true"
      :style="{ left: followerX + 'px', top: followerY + 'px' }"
    ></div>

    <Header
      :cv-url="cvUrl"
      :current-locale="locale"
      :current-theme="theme"
    />

    <main>
      <slot />
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Header from '@/components/public/Header.vue';
import Footer from '@/components/public/Footer.vue';
import { useTheme } from '@/composables/useTheme';
import { useLanguage } from '@/composables/useLanguage';

defineProps({
  cvUrl: {
    type: String,
    default: null,
  },
});

const { theme } = useTheme();
const { locale } = useLanguage();

// Custom cursor
const cursorX = ref(-100);
const cursorY = ref(-100);
const followerX = ref(-100);
const followerY = ref(-100);

let followerAnimFrame = null;
let targetX = -100;
let targetY = -100;

function onMouseMove(e) {
  cursorX.value = e.clientX;
  cursorY.value = e.clientY;
  targetX = e.clientX;
  targetY = e.clientY;
}

function animateFollower() {
  followerX.value += (targetX - followerX.value) * 0.15;
  followerY.value += (targetY - followerY.value) * 0.15;
  followerAnimFrame = requestAnimationFrame(animateFollower);
}

onMounted(() => {
  // Only enable custom cursor on pointer devices
  if (window.matchMedia('(hover: hover)').matches) {
    window.addEventListener('mousemove', onMouseMove, { passive: true });
    animateFollower();
  }
});

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove);
  if (followerAnimFrame) cancelAnimationFrame(followerAnimFrame);
});
</script>
