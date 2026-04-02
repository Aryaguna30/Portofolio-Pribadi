<template>
  <article
    class="project-card relative bg-[#131929] border border-white/[0.08] rounded-[14px] overflow-hidden cursor-pointer
           transition-all duration-[250ms] ease-[cubic-bezier(.4,0,.2,1)]
           hover:-translate-y-2 hover:shadow-[0_20px_60px_rgba(0,0,0,.4),0_0_0_1px_rgba(124,58,237,.3)]
           hover:border-purple-500/40"
    :class="{ 'border-purple-500/30 shadow-[0_0_30px_rgba(124,58,237,.1)]': featured }"
    role="button"
    tabindex="0"
    :aria-label="`${project.title} — klik untuk detail`"
    @click="handleClick"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
  >
    <!-- Hover glow overlay (pseudo-element replacement) -->
    <div
      class="absolute inset-0 rounded-[14px] bg-gradient-to-br from-purple-600 to-cyan-500 opacity-0
             group-hover:opacity-[0.05] transition-opacity duration-[250ms] pointer-events-none z-0"
      aria-hidden="true"
    ></div>

    <!-- Thumbnail -->
    <div class="relative aspect-video overflow-hidden" aria-hidden="true">
      <!-- WebP image (when thumbnail_url is provided) -->
      <img
        v-if="project.thumbnail_url"
        :src="project.thumbnail_url"
        :alt="project.title"
        loading="lazy"
        class="w-full h-full object-cover"
      />
      <!-- Gradient fallback (when no thumbnail) -->
      <div
        v-else
        class="w-full h-full"
        :style="{ background: gradientFallback }"
        aria-hidden="true"
      ></div>

      <!-- Short label overlay -->
      <span
        class="absolute inset-0 flex items-center justify-center
               text-[2.5rem] font-black text-white/15 font-['Fira_Code',monospace] tracking-[-2px]
               pointer-events-none select-none"
      >
        {{ shortLabel }}
      </span>

      <!-- Shine effect overlay -->
      <div
        class="project-card__shine absolute inset-0 pointer-events-none"
        aria-hidden="true"
      ></div>
    </div>

    <!-- Card Body -->
    <div class="relative z-10 p-5">
      <!-- Tech stack pills -->
      <div class="flex flex-wrap gap-1.5 mb-3" :aria-label="t('portfolio.techStack')">
        <span
          v-for="tech in techStackList"
          :key="tech"
          class="text-[0.7rem] font-semibold px-2.5 py-1 rounded bg-purple-500/10 text-purple-400
                 border border-purple-500/20"
        >
          {{ tech }}
        </span>
      </div>

      <!-- Title -->
      <h3 class="text-base font-bold text-white mb-2 leading-snug">
        {{ project.title }}
      </h3>

      <!-- Short description -->
      <p class="text-[0.85rem] text-gray-400 mb-4 leading-relaxed line-clamp-2">
        {{ shortDescription }}
      </p>

      <!-- Arrow icon -->
      <div
        class="project-card__arrow flex items-center justify-center w-8 h-8 rounded-lg
               bg-purple-500/10 border border-purple-500/20 text-purple-400
               transition-all duration-[250ms] ease-[cubic-bezier(.4,0,.2,1)]"
        aria-hidden="true"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2">
          <line x1="5" y1="12" x2="19" y2="12"/>
          <polyline points="12 5 19 12 12 19"/>
        </svg>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  project: {
    type: Object,
    required: true,
    // Expected shape:
    // { title, description, tech_stack, thumbnail_path, demo_url, repo_url }
  },
  featured: {
    type: Boolean,
    default: false,
  },
});

// ── Emits ──────────────────────────────────────────────────────────────────
const emit = defineEmits(['click']);

const { t } = useI18n();

// ── Computed ───────────────────────────────────────────────────────────────

/** Normalise tech_stack — can be Array or JSON string */
const techStackList = computed(() => {
  const ts = props.project.tech_stack;
  if (!ts) return [];
  if (Array.isArray(ts)) return ts;
  try { return JSON.parse(ts); } catch { return []; }
});

/** Short label derived from the first tech or title initials */
const shortLabel = computed(() => {
  if (techStackList.value.length > 0) {
    // Use first word of first tech, max 4 chars
    return techStackList.value[0].split(' ')[0].toUpperCase().slice(0, 4);
  }
  // Fallback: initials from title
  return (props.project.title || '')
    .split(' ')
    .slice(0, 3)
    .map(w => w[0] || '')
    .join('')
    .toUpperCase();
});

/** Strip HTML tags from description for plain-text preview */
const shortDescription = computed(() => {
  const raw = props.project.description || '';
  return raw.replace(/<[^>]*>/g, '').trim();
});

/** Gradient fallback colours cycling through a palette */
const GRADIENTS = [
  'linear-gradient(135deg,#7c3aed,#4f46e5)',
  'linear-gradient(135deg,#059669,#0891b2)',
  'linear-gradient(135deg,#d97706,#dc2626)',
  'linear-gradient(135deg,#7c3aed,#06b6d4)',
];

const gradientFallback = computed(() => {
  const idx = Math.abs(
    (props.project.title || '').split('').reduce((acc, c) => acc + c.charCodeAt(0), 0)
  ) % GRADIENTS.length;
  return GRADIENTS[idx];
});

// ── Methods ────────────────────────────────────────────────────────────────
function handleClick() {
  emit('click', props.project);
}
</script>

<style scoped>
/* Shine effect — static gradient overlay that creates a gloss look */
.project-card__shine {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.10) 0%,
    transparent 50%,
    rgba(255, 255, 255, 0.05) 100%
  );
}

/* Arrow slides right on card hover */
.project-card:hover .project-card__arrow {
  background: #7c3aed;
  color: #fff;
  border-color: #7c3aed;
  transform: translateX(4px);
}

/* Clamp description to 2 lines */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
