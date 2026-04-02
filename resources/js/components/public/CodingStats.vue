<template>
  <section id="stats" class="section">
    <div class="container">
      <!-- Section Header -->
      <div ref="sectionLabelRef" class="section__label reveal-item">
        {{ $t('stats.title') }}
      </div>
      <h2 ref="sectionTitleRef" class="section__title reveal-item" style="--reveal-delay:.1s">
        {{ $t('stats.subtitle') }} <span class="gradient-text">{{ $t('stats.subtitleAccent') }}</span>
      </h2>

      <!-- Stats Content — always rendered once stats is set -->
      <div v-if="stats" class="stats__grid">
        <!-- Language Progress Bars Card -->
        <div ref="langCardRef" class="stats__lang-card glass">
          <h3 class="stats__card-title">{{ $t('stats.languages') }}</h3>
          <div class="lang-bars">
            <div v-for="(lang, index) in stats.languages" :key="lang.name" class="lang-bar">
              <div class="lang-bar__info">
                <span>{{ lang.name }}</span>
                <span class="lang-bar__pct">{{ lang.percent }}%</span>
              </div>
              <div class="lang-bar__track">
                <div
                  class="lang-bar__fill"
                  :class="{ 'animated': animatedBars }"
                  :style="{ '--w': `${lang.percent}%`, '--c': lang.color || getBarColor(index) }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stat Number Cards -->
        <div class="stats__numbers">
          <div
            v-for="(card, index) in statCards"
            :key="card.key"
            class="stat-card glass"
          >
            <span class="stat-card__num gradient-text">
              {{ displayValues[card.key] ?? 0 }}{{ card.suffix || '' }}
            </span>
            <span class="stat-card__label">{{ card.label }}</span>
          </div>
        </div>
      </div>

      <!-- Loading fallback -->
      <div v-else class="flex justify-center py-16">
        <LoadingSpinner size="lg" :show-label="true" :label="$t('stats.loading')" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';
import { useIntersectionObserver } from '@/composables/useIntersectionObserver.js';

const props = defineProps({
  initialData: {
    type: Object,
    default: null,
  },
});

const { t } = useI18n();

const stats = ref(props.initialData || null);
const loading = ref(false); // start false, show fallback immediately
const error = ref(false);
const animatedBars = ref(false);
const langCardRef = ref(null);
const sectionLabelRef = ref(null);
const sectionTitleRef = ref(null);

// Fallback static data shown when API is unavailable
const fallbackStats = {
  languages: [
    { name: 'PHP',        percent: 48, color: '#7c3aed' },
    { name: 'JavaScript', percent: 32, color: '#f59e0b' },
    { name: 'Vue SFC',    percent: 12, color: '#10b981' },
    { name: 'CSS/Blade',  percent: 8,  color: '#3b82f6' },
  ],
  total_commits: 1247,
  repositories: 38,
  projects_completed: 12,
  years_experience: 3,
};

// Count-up animation state
const displayValues = reactive({});

// Stat cards config — derived from stats when available
const statCards = computed(() => {
  if (!stats.value) return [];
  const cards = [];
  if (stats.value.total_commits != null) {
    cards.push({ key: 'total_commits', target: stats.value.total_commits, label: t('stats.commits'), suffix: '' });
  }
  if (stats.value.repositories != null) {
    cards.push({ key: 'repositories', target: stats.value.repositories, label: t('stats.repositories'), suffix: '' });
  }
  if (stats.value.projects_completed != null) {
    cards.push({ key: 'projects_completed', target: stats.value.projects_completed, label: t('about.stats.projects'), suffix: '' });
  }
  if (stats.value.years_experience != null) {
    cards.push({ key: 'years_experience', target: stats.value.years_experience, label: t('about.stats.years'), suffix: '+' });
  }
  if (stats.value.streak_days != null) {
    cards.push({ key: 'streak_days', target: stats.value.streak_days, label: t('stats.streak'), suffix: '' });
  }
  return cards;
});

// Bar colors fallback
function getBarColor(index) {
  const colors = ['#7c3aed', '#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#06b6d4'];
  return colors[index % colors.length];
}

// Count-up animation using requestAnimationFrame
function animateCountUp(key, target, duration = 1500) {
  const start = performance.now();
  const startVal = 0;

  function step(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    // Ease out cubic
    const eased = 1 - Math.pow(1 - progress, 3);
    displayValues[key] = Math.round(startVal + (target - startVal) * eased);
    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      displayValues[key] = target;
    }
  }

  requestAnimationFrame(step);
}

// Initialize display values to 0
function initDisplayValues() {
  statCards.value.forEach((card) => {
    displayValues[card.key] = 0;
  });
}

// Start all count-up animations
function startCountUpAnimations() {
  statCards.value.forEach((card, index) => {
    setTimeout(() => {
      animateCountUp(card.key, card.target, 1500);
    }, index * 150);
  });
}

// Setup IntersectionObserver for progress bars and count-up
const { observeEl } = useIntersectionObserver(
  (entry) => {
    if (entry.isIntersecting) {
      animatedBars.value = true;
      startCountUpAnimations();
    }
  },
  { threshold: 0.2 }
);

// Fetch coding stats on mount
onMounted(async () => {
  // Set fallback immediately
  stats.value = fallbackStats;
  await nextTick();
  initDisplayValues();

  // Observe lang card for bar animation + count-up trigger
  if (langCardRef.value) observeEl(langCardRef.value);

  // Try to fetch real GitHub data in background
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 3000);
  try {
    const response = await fetch('/coding-stats', {
      signal: controller.signal,
      headers: { Accept: 'application/json' },
    });
    clearTimeout(timeoutId);
    if (response.ok) {
      const json = await response.json();
      const data = json.data ?? json;
      if (data && !json.error && Array.isArray(data.languages) && data.languages.length) {
        stats.value = data;
        await nextTick();
        initDisplayValues();
      }
    }
  } catch {
    clearTimeout(timeoutId);
  }
});
</script>

<style scoped>
.reveal-item {
  opacity: 0;
  transform: translateY(28px);
  transition: opacity 0.65s ease, transform 0.65s ease;
  transition-delay: var(--reveal-delay, 0s);
}
.reveal-item.is-visible {
  opacity: 1;
  transform: translateY(0);
}

.gradient-text {
  background: linear-gradient(135deg, #a855f7, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.section__label {
  color: var(--accent, #a855f7);
}

.lang-bar__fill {
  will-change: width;
  background: var(--c);
  box-shadow: 0 0 10px var(--c);
}

.lang-bar__fill.animated {
  width: var(--w);
}
</style>
