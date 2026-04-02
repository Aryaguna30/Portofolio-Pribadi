<template>
  <section
    class="section"
    id="about"
    ref="sectionRef"
    aria-labelledby="about-title"
  >
    <div class="container">

      <!-- Section Label -->
      <div
        ref="labelRef"
        class="section__label reveal-item"
      >
        {{ t('about.title') }}
      </div>

      <!-- Section Title -->
      <h2
        id="about-title"
        ref="titleRef"
        class="section__title reveal-item"
        style="--reveal-delay: 0.1s"
      >
        {{ t('about.subtitle') }}
        <span class="gradient-text">
          {{ t('about.subtitleAccent') }}
        </span>
      </h2>

      <!-- Two-column grid -->
      <div class="about__grid">

        <!-- Left: Bio + Stats + Skills -->
        <div
          ref="bioRef"
          class="about__bio reveal-item"
          style="--reveal-delay: 0.15s"
        >
          <!-- Bio paragraphs -->
          <p class="text-gray-400 leading-relaxed">
            {{ t('about.bio1').split('Full-Stack Web Developer')[0] }}<strong class="text-white">Full-Stack Web Developer</strong>{{ t('about.bio1').split('Full-Stack Web Developer')[1] }}
          </p>
          <p class="text-gray-400 leading-relaxed">
            {{ t('about.bio2') }}
          </p>

          <!-- Stats Row -->
          <div class="about__stats">
            <div class="about__stat">
              <span class="about__stat-num gradient-text">3+</span>
              <span>{{ t('about.stats.years') }}</span>
            </div>
            <div class="about__stat">
              <span class="about__stat-num gradient-text">12</span>
              <span>{{ t('about.stats.projects') }}</span>
            </div>
            <div class="about__stat">
              <span class="about__stat-num gradient-text">1.2K+</span>
              <span>{{ t('about.stats.commits') }}</span>
            </div>
          </div>

          <!-- Skills Grid -->
          <div
            class="skills__grid"
            role="list"
            :aria-label="locale === 'en' ? 'Skills' : 'Keahlian'"
          >
            <div
              v-for="skill in skills"
              :key="skill.name"
              role="listitem"
              class="skill-badge"
            >
              <!-- devicon class icon -->
              <i
                v-if="skill.icon_class"
                :class="[skill.icon_class, 'text-base leading-none']"
                aria-hidden="true"
              ></i>
              <!-- fallback emoji if no icon_class -->
              <span v-else aria-hidden="true">⚡</span>
              {{ skill.name }}
            </div>

            <!-- Fallback skills when no skills prop provided -->
            <template v-if="!skills || skills.length === 0">
              <div
                v-for="fb in fallbackSkills"
                :key="fb.name"
                role="listitem"
                class="skill-badge"
              >
                <span aria-hidden="true">{{ fb.icon }}</span>
                {{ fb.name }}
              </div>
            </template>
          </div>
        </div>

        <!-- Right: Timeline -->
        <div
          ref="timelineRef"
          class="reveal-item"
          style="--reveal-delay: 0.2s"
        >
          <div class="timeline-wrap">

            <!-- Timeline Tabs -->
            <div class="timeline__tabs" role="tablist" :aria-label="locale === 'en' ? 'Timeline tabs' : 'Tab timeline'">
              <button
                role="tab"
                :aria-selected="activeTab === 'education'"
                :aria-controls="'tab-panel-education'"
                class="timeline__tab"
                :class="{ 'active': activeTab === 'education' }"
                @click="activeTab = 'education'"
              >
                🎓 {{ t('about.tabs.education') }}
              </button>
              <button
                role="tab"
                :aria-selected="activeTab === 'experience'"
                :aria-controls="'tab-panel-experience'"
                class="timeline__tab"
                :class="{ 'active': activeTab === 'experience' }"
                @click="activeTab = 'experience'"
              >
                💼 {{ t('about.tabs.experience') }}
              </button>
            </div>

            <!-- Education Tab Panel -->
            <div
              id="tab-panel-education"
              role="tabpanel"
              :aria-label="t('about.tabs.education')"
              v-show="activeTab === 'education'"
            >
              <div v-if="educationEntries.length > 0">
                <div
                  v-for="(entry, index) in educationEntries"
                  :key="entry.id ?? index"
                  class="flex gap-4 pb-6 relative"
                  :class="{ 'pb-0': index === educationEntries.length - 1 }"
                >
                  <!-- Connector line (not on last item) -->
                  <div
                    v-if="index < educationEntries.length - 1"
                    class="absolute left-[7px] top-5 bottom-0 w-px bg-gradient-to-b from-purple-500/60 to-transparent"
                    aria-hidden="true"
                  ></div>

                  <!-- Dot -->
                  <div
                    class="w-[15px] h-[15px] rounded-full bg-gradient-to-br from-purple-500 to-cyan-400 flex-shrink-0 mt-1 shadow-[0_0_10px_rgba(124,58,237,0.5)]"
                    aria-hidden="true"
                  ></div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <span class="text-xs text-purple-400 font-semibold font-['Fira_Code',monospace]">
                      {{ formatYear(entry.start_year) }} — {{ entry.end_year ? formatYear(entry.end_year) : (locale === 'en' ? 'Present' : 'Sekarang') }}
                    </span>
                    <h4 class="text-sm font-semibold text-white mt-0.5 mb-0.5">{{ entry.role }}</h4>
                    <p class="text-sm text-gray-500 mb-1">{{ entry.institution }}</p>
                    <p v-if="entry.description" class="text-xs text-gray-500 leading-relaxed">{{ entry.description }}</p>
                  </div>
                </div>
              </div>

              <!-- Fallback education entries -->
              <div v-else>
                <div
                  v-for="(entry, index) in fallbackEducation"
                  :key="index"
                  class="flex gap-4 pb-6 relative"
                  :class="{ 'pb-0': index === fallbackEducation.length - 1 }"
                >
                  <div
                    v-if="index < fallbackEducation.length - 1"
                    class="absolute left-[7px] top-5 bottom-0 w-px bg-gradient-to-b from-purple-500/60 to-transparent"
                    aria-hidden="true"
                  ></div>
                  <div class="w-[15px] h-[15px] rounded-full bg-gradient-to-br from-purple-500 to-cyan-400 flex-shrink-0 mt-1 shadow-[0_0_10px_rgba(124,58,237,0.5)]" aria-hidden="true"></div>
                  <div class="flex-1 min-w-0">
                    <span class="text-xs text-purple-400 font-semibold font-['Fira_Code',monospace]">{{ entry.year }}</span>
                    <h4 class="text-sm font-semibold text-white mt-0.5 mb-0.5">{{ entry.role }}</h4>
                    <p class="text-sm text-gray-500 mb-1">{{ entry.institution }}</p>
                    <p v-if="entry.description" class="text-xs text-gray-500 leading-relaxed">{{ entry.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Experience Tab Panel -->
            <div
              id="tab-panel-experience"
              role="tabpanel"
              :aria-label="t('about.tabs.experience')"
              v-show="activeTab === 'experience'"
            >
              <div v-if="experienceEntries.length > 0">
                <div
                  v-for="(entry, index) in experienceEntries"
                  :key="entry.id ?? index"
                  class="flex gap-4 pb-6 relative"
                  :class="{ 'pb-0': index === experienceEntries.length - 1 }"
                >
                  <div
                    v-if="index < experienceEntries.length - 1"
                    class="absolute left-[7px] top-5 bottom-0 w-px bg-gradient-to-b from-purple-500/60 to-transparent"
                    aria-hidden="true"
                  ></div>
                  <div
                    class="w-[15px] h-[15px] rounded-full bg-gradient-to-br from-purple-500 to-cyan-400 flex-shrink-0 mt-1 shadow-[0_0_10px_rgba(124,58,237,0.5)]"
                    aria-hidden="true"
                  ></div>
                  <div class="flex-1 min-w-0">
                    <span class="text-xs text-purple-400 font-semibold font-['Fira_Code',monospace]">
                      {{ formatYear(entry.start_year) }} — {{ entry.end_year ? formatYear(entry.end_year) : (locale === 'en' ? 'Present' : 'Sekarang') }}
                    </span>
                    <h4 class="text-sm font-semibold text-white mt-0.5 mb-0.5">{{ entry.role }}</h4>
                    <p class="text-sm text-gray-500 mb-1">{{ entry.institution }}</p>
                    <p v-if="entry.description" class="text-xs text-gray-500 leading-relaxed">{{ entry.description }}</p>
                  </div>
                </div>
              </div>

              <!-- Fallback experience entries -->
              <div v-else>
                <div
                  v-for="(entry, index) in fallbackExperience"
                  :key="index"
                  class="flex gap-4 pb-6 relative"
                  :class="{ 'pb-0': index === fallbackExperience.length - 1 }"
                >
                  <div
                    v-if="index < fallbackExperience.length - 1"
                    class="absolute left-[7px] top-5 bottom-0 w-px bg-gradient-to-b from-purple-500/60 to-transparent"
                    aria-hidden="true"
                  ></div>
                  <div class="w-[15px] h-[15px] rounded-full bg-gradient-to-br from-purple-500 to-cyan-400 flex-shrink-0 mt-1 shadow-[0_0_10px_rgba(124,58,237,0.5)]" aria-hidden="true"></div>
                  <div class="flex-1 min-w-0">
                    <span class="text-xs text-purple-400 font-semibold font-['Fira_Code',monospace]">{{ entry.year }}</span>
                    <h4 class="text-sm font-semibold text-white mt-0.5 mb-0.5">{{ entry.role }}</h4>
                    <p class="text-sm text-gray-500 mb-1">{{ entry.institution }}</p>
                    <p v-if="entry.description" class="text-xs text-gray-500 leading-relaxed">{{ entry.description }}</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useIntersectionObserver } from '@/composables/useIntersectionObserver.js';

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  timelineEntries: {
    type: Array,
    default: () => [],
  },
  skills: {
    type: Array,
    default: () => [],
  },
});

const { t, locale } = useI18n();

// ── Active tab state ───────────────────────────────────────────────────────
const activeTab = ref('education');

// ── Filtered timeline entries ──────────────────────────────────────────────
const educationEntries = computed(() =>
  [...props.timelineEntries]
    .filter(e => e.type === 'education')
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
);

const experienceEntries = computed(() =>
  [...props.timelineEntries]
    .filter(e => e.type === 'experience')
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
);

// ── Year formatter ─────────────────────────────────────────────────────────
function formatYear(year) {
  return year ? String(year) : '';
}

// ── Fallback data (shown when no props provided) ───────────────────────────
const fallbackSkills = [
  { name: 'Laravel',    icon: '⚡' },
  { name: 'Vue.js 3',   icon: '💚' },
  { name: 'Inertia.js', icon: '🔗' },
  { name: 'Tailwind',   icon: '🎨' },
  { name: 'MySQL',      icon: '🗄️' },
  { name: 'PHP 8',      icon: '🐘' },
  { name: 'Docker',     icon: '📦' },
  { name: 'Git',        icon: '🔧' },
];

const fallbackEducation = [
  {
    year: '2020 — 2024',
    role: 'S1 Teknik Informatika',
    institution: 'Universitas Negeri Semarang',
    description: 'Fokus rekayasa perangkat lunak. IPK 3.72/4.00',
  },
  {
    year: '2017 — 2020',
    role: 'Rekayasa Perangkat Lunak',
    institution: 'SMK Negeri 1 Semarang',
    description: 'Jurusan RPL, dasar pemrograman dan web development.',
  },
];

const fallbackExperience = [
  {
    year: '2023 — Sekarang',
    role: 'Full-Stack Developer',
    institution: 'PT. Teknologi Maju Indonesia',
    description: 'Membangun sistem ERP Laravel + Vue.js untuk 500+ pengguna aktif.',
  },
  {
    year: '2022 — 2023',
    role: 'Web Developer Intern',
    institution: 'CV. Digital Kreatif',
    description: 'Mengembangkan fitur e-commerce dan CMS menggunakan Laravel.',
  },
];

// ── Scroll Reveal via useIntersectionObserver ──────────────────────────────
const sectionRef  = ref(null);
const labelRef    = ref(null);
const titleRef    = ref(null);
const bioRef      = ref(null);
const timelineRef = ref(null);

const { observeEl } = useIntersectionObserver(
  (entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
    }
  },
  { threshold: 0.15 }
);

onMounted(() => {
  [labelRef.value, titleRef.value, bioRef.value, timelineRef.value].forEach(el => {
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
