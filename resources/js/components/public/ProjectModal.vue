<template>
  <!-- Backdrop -->
  <Transition name="modal">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-[200] flex items-center justify-center p-6
             bg-black/80 backdrop-blur-[8px]"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="modalTitleId"
      @click.self="close"
    >
      <!-- Modal panel -->
      <div
        ref="trapRef"
        class="relative w-full max-w-[600px] max-h-[90vh] overflow-y-auto rounded-[14px]
               bg-white/[0.04] backdrop-blur-[20px] border border-white/[0.08]"
        @keydown.esc="close"
      >
        <!-- Close button -->
        <button
          class="absolute top-4 right-4 z-10 flex items-center justify-center w-9 h-9
                 rounded-lg bg-[#131929] border border-white/[0.08] text-gray-400
                 transition-all duration-[250ms] hover:text-white hover:border-white/30
                 focus:outline-none focus:ring-2 focus:ring-purple-500/50"
          :aria-label="t('portfolio.close')"
          @click="close"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <!-- Thumbnail -->
        <div class="h-[220px] rounded-t-[14px] overflow-hidden" aria-hidden="true">
          <img
            v-if="project?.thumbnail_path"
            :src="project.thumbnail_path"
            :alt="project.title"
            loading="lazy"
            class="w-full h-full object-cover"
          />
          <div
            v-else
            class="w-full h-full"
            :style="{ background: gradientFallback }"
          ></div>
        </div>

        <!-- Body -->
        <div class="p-7">
          <!-- Title -->
          <h2
            :id="modalTitleId"
            class="text-[1.4rem] font-bold text-white mb-3 leading-snug"
          >
            {{ project?.title }}
          </h2>

          <!-- Tech stack tags -->
          <div
            v-if="techStackList.length"
            class="flex flex-wrap gap-1.5 mb-4"
            :aria-label="t('portfolio.techStack')"
          >
            <span
              v-for="tech in techStackList"
              :key="tech"
              class="text-[0.7rem] font-semibold px-2.5 py-1 rounded
                     bg-purple-500/10 text-purple-400 border border-purple-500/20"
            >
              {{ tech }}
            </span>
          </div>

          <!-- HTML description (sanitized) -->
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div
            class="modal__desc text-gray-400 mb-6 leading-[1.8] text-[0.9rem]"
            v-html="sanitizedDescription"
          ></div>

          <!-- Action links -->
          <div class="flex flex-wrap gap-3">
            <a
              v-if="project?.demo_url"
              :href="project.demo_url"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-semibold text-sm
                     bg-gradient-to-br from-purple-600 to-indigo-600 text-white
                     shadow-[0_0_30px_rgba(124,58,237,.3)]
                     transition-all duration-[250ms] hover:-translate-y-0.5
                     hover:shadow-[0_0_50px_rgba(124,58,237,.5)]
                     focus:outline-none focus:ring-2 focus:ring-purple-500/50"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
              </svg>
              {{ t('portfolio.viewDemo') }}
            </a>

            <a
              v-if="project?.repo_url"
              :href="project.repo_url"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-semibold text-sm
                     bg-transparent text-white border border-white/[0.08]
                     transition-all duration-[250ms] hover:-translate-y-0.5
                     hover:border-purple-500/40 hover:text-purple-400
                     hover:shadow-[0_0_20px_rgba(124,58,237,.3)]
                     focus:outline-none focus:ring-2 focus:ring-purple-500/50"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                   fill="currentColor" aria-hidden="true">
                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61
                         c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77
                         5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0
                         C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77
                         a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7
                         A3.37 3.37 0 0 0 9 18.13V22"/>
              </svg>
              {{ t('portfolio.viewRepo') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue';
import DOMPurify from 'dompurify';
import { useI18n } from 'vue-i18n';
import { useFocusTrap } from '@/composables/useFocusTrap.js';

// ── Props & Emits ──────────────────────────────────────────────────────────
const props = defineProps({
  project: {
    type: Object,
    default: null,
  },
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

// ── i18n ───────────────────────────────────────────────────────────────────
const { t } = useI18n();

// ── Focus trap ─────────────────────────────────────────────────────────────
const { trapRef, activateTrap, deactivateTrap } = useFocusTrap();

watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      // Wait for DOM to render before activating trap
      setTimeout(activateTrap, 50);
    } else {
      deactivateTrap();
    }
  },
);

// ── Escape key ─────────────────────────────────────────────────────────────
function handleKeydown(e) {
  if (e.key === 'Escape' && props.isOpen) {
    close();
  }
}

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

// ── Methods ────────────────────────────────────────────────────────────────
function close() {
  emit('close');
}

// ── Computed ───────────────────────────────────────────────────────────────

/** Unique id for aria-labelledby */
const modalTitleId = 'project-modal-title';

/** Sanitize HTML description before v-html */
const sanitizedDescription = computed(() => {
  if (!props.project?.description) return '';
  return DOMPurify.sanitize(props.project.description);
});

/** Normalise tech_stack — can be Array or JSON string */
const techStackList = computed(() => {
  const ts = props.project?.tech_stack;
  if (!ts) return [];
  if (Array.isArray(ts)) return ts;
  try { return JSON.parse(ts); } catch { return []; }
});

/** Gradient fallback for thumbnail */
const GRADIENTS = [
  'linear-gradient(135deg,#7c3aed,#4f46e5)',
  'linear-gradient(135deg,#059669,#0891b2)',
  'linear-gradient(135deg,#d97706,#dc2626)',
  'linear-gradient(135deg,#7c3aed,#06b6d4)',
];

const gradientFallback = computed(() => {
  const idx = Math.abs(
    (props.project?.title || '').split('').reduce((acc, c) => acc + c.charCodeAt(0), 0),
  ) % GRADIENTS.length;
  return GRADIENTS[idx];
});
</script>

<style scoped>
/* Scale + opacity transition for the modal */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
              opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: translateY(30px) scale(0.95);
  opacity: 0;
}

.modal-enter-to .relative,
.modal-leave-from .relative {
  transform: translateY(0) scale(1);
  opacity: 1;
}

/* Description HTML content styling */
.modal__desc :deep(p) {
  margin-bottom: 0.75rem;
}

.modal__desc :deep(strong) {
  color: #e8edf5;
}

.modal__desc :deep(a) {
  color: #7c3aed;
  text-decoration: underline;
}

.modal__desc :deep(ul),
.modal__desc :deep(ol) {
  padding-left: 1.25rem;
  margin-bottom: 0.75rem;
}
</style>
