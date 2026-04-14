<template>
  <MetaManager
    :title="meta?.title || ''"
    :description="meta?.description || ''"
    :og-image="meta?.ogImage || ''"
    :og-url="meta?.ogUrl || ''"
  />

  <HeroSection
    :name="hero?.name || ''"
    :professions="hero?.professions || []"
    :description="hero?.description || ''"
    :typing-speed="hero?.typingSpeed || 80"
  />

  <AboutSection
    :timeline-entries="timelineEntries"
    :skills="skills"
    :about="about"
  />

  <PortfolioSection
    :projects="projects"
  />

  <CodingStats />

  <ContactSection
    :social-links="socialLinks"
  />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import MetaManager from '@/components/shared/MetaManager.vue';
import HeroSection from '@/components/public/HeroSection.vue';
import AboutSection from '@/components/public/AboutSection.vue';
import PortfolioSection from '@/components/public/PortfolioSection.vue';
import CodingStats from '@/components/public/CodingStats.vue';
import ContactSection from '@/components/public/ContactSection.vue';

defineOptions({ layout: PublicLayout });

// ── Props from HomeController ──────────────────────────────────────────────
const props = defineProps({
  hero: {
    type: Object,
    default: () => ({}),
  },
  projects: {
    type: Array,
    default: () => [],
  },
  about: {
    type: Object,
    default: () => ({}),
  },
  timelineEntries: {
    type: Array,
    default: () => [],
  },
  skills: {
    type: Array,
    default: () => [],
  },
  socialLinks: {
    type: Object,
    default: () => ({}),
  },
  cvUrl: {
    type: String,
    default: null,
  },
  meta: {
    type: Object,
    default: () => ({}),
  },
});

// ── Custom Cursor (dot + follower) with requestAnimationFrame ──────────────
const cursorDotRef = ref(null);
const cursorFollowerRef = ref(null);

let dotX = -100;
let dotY = -100;
let followerX = -100;
let followerY = -100;
let targetX = -100;
let targetY = -100;
let rafId = null;
let isCursorEnabled = false;

function onMouseMove(e) {
  dotX = e.clientX;
  dotY = e.clientY;
  targetX = e.clientX;
  targetY = e.clientY;

  if (cursorDotRef.value) {
    cursorDotRef.value.style.transform = `translate(${dotX}px, ${dotY}px)`;
  }
}

function animateCursor() {
  followerX += (targetX - followerX) * 0.15;
  followerY += (targetY - followerY) * 0.15;

  if (cursorFollowerRef.value) {
    cursorFollowerRef.value.style.transform = `translate(${followerX}px, ${followerY}px)`;
  }

  rafId = requestAnimationFrame(animateCursor);
}

onMounted(() => {
  // Only enable on pointer (non-touch) devices
  if (window.matchMedia('(hover: hover)').matches) {
    isCursorEnabled = true;
    window.addEventListener('mousemove', onMouseMove, { passive: true });
    animateCursor();
  }
});

onUnmounted(() => {
  if (isCursorEnabled) {
    window.removeEventListener('mousemove', onMouseMove);
  }
  if (rafId) cancelAnimationFrame(rafId);
});
</script>

<style scoped>
/* Custom cursor elements are rendered in PublicLayout;
   this page does not duplicate them. */
</style>
