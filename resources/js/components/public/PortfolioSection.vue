<template>
  <section
    class="section section--dark"
    id="portfolio"
    aria-labelledby="portfolio-title"
  >
    <div class="container">

      <!-- Section Label -->
      <div
        ref="labelRef"
        class="section__label reveal-item"
      >
        {{ t('portfolio.title') }}
      </div>

      <!-- Section Title -->
      <h2
        id="portfolio-title"
        ref="titleRef"
        class="section__title reveal-item"
        style="--reveal-delay: 0.1s"
      >
        {{ t('portfolio.subtitle') }}
        <span class="gradient-text">
          {{ t('portfolio.subtitleAccent') }}
        </span>
      </h2>

      <!-- Empty state -->
      <div
        v-if="!projects || projects.length === 0"
        class="flex flex-col items-center justify-center py-20 text-center"
        role="status"
        aria-live="polite"
      >
        <div class="text-5xl mb-4" aria-hidden="true">📂</div>
        <p class="text-gray-500 text-base">{{ t('portfolio.empty') }}</p>
      </div>

      <!-- Projects grid -->
      <div
        v-else
        class="portfolio__grid"
      >
        <div
          v-for="(project, index) in projects"
          :key="project.id ?? index"
          :ref="el => setCardRef(el, index)"
          class="reveal-item"
          :style="`--reveal-delay: ${0.1 + index * 0.05}s`"
        >
          <ProjectCard
            :project="project"
            :featured="project.is_featured ?? false"
            @click="openModal(project)"
          />
        </div>
      </div>

    </div>

    <!-- Project Modal -->
    <ProjectModal
      :project="selectedProject"
      :is-open="isModalOpen"
      @close="closeModal"
    />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import ProjectCard from '@/components/public/ProjectCard.vue';
import ProjectModal from '@/components/public/ProjectModal.vue';
import { useIntersectionObserver } from '@/composables/useIntersectionObserver.js';

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  projects: {
    type: Array,
    default: () => [],
  },
});

const { t } = useI18n();

// ── Modal state ────────────────────────────────────────────────────────────
const selectedProject = ref(null);
const isModalOpen = ref(false);

function openModal(project) {
  selectedProject.value = project;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedProject.value = null;
}

// ── Scroll reveal ──────────────────────────────────────────────────────────
const labelRef = ref(null);
const titleRef = ref(null);
const cardRefs = ref([]);

function setCardRef(el, index) {
  if (el) cardRefs.value[index] = el;
}

const { observeEl } = useIntersectionObserver(
  (entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
    }
  },
  { threshold: 0.1 }
);

onMounted(() => {
  [labelRef.value, titleRef.value].forEach(el => {
    if (el) observeEl(el);
  });
  cardRefs.value.forEach(el => {
    if (el) observeEl(el);
  });
});
</script>

<style scoped>
/* Scroll reveal animation */
.reveal-item {
  opacity: 0;
  transform: translateY(28px);
  transition:
    opacity 0.65s ease,
    transform 0.65s ease;
  transition-delay: var(--reveal-delay, 0s);
}

.reveal-item.is-visible {
  opacity: 1;
  transform: translateY(0);
}
</style>
