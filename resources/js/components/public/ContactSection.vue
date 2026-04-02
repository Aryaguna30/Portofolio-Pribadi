<template>
  <section
    class="section section--dark"
    id="contact"
    aria-labelledby="contact-title"
  >
    <div class="container">
      <!-- Section Label -->
      <div
        class="section__label reveal-item"
        aria-hidden="true"
      >
        {{ t('contact.title') }}
      </div>

      <!-- Section Title -->
      <h2
        id="contact-title"
        class="section__title reveal-item"
        style="--reveal-delay: 0.1s"
      >
        {{ t('contact.subtitle') }}
        <span class="gradient-text">
          {{ t('contact.subtitleAccent') }}
        </span>
      </h2>

      <!-- Grid: Info + Form -->
      <div class="contact__grid">

        <!-- Left: Description + Social Links -->
        <div class="reveal-item" style="--reveal-delay: 0.15s">
          <p class="text-gray-400 text-[1.05rem] mb-8 leading-relaxed">
            {{ t('contact.desc') }}
          </p>

          <!-- Social Links -->
          <div class="social-links" role="list" :aria-label="t('contact.title')">

            <!-- LinkedIn -->
            <a
              :href="socialLinks?.linkedin || '#'"
              class="social-link"
              :aria-label="t('contact.social.linkedin')"
              target="_blank"
              rel="noopener noreferrer"
              role="listitem"
            >
              <div class="social-link__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                  <rect x="2" y="9" width="4" height="12"/>
                  <circle cx="4" cy="4" r="2"/>
                </svg>
              </div>
              <div>
                <strong>{{ t('contact.social.linkedin') }}</strong>
                <span>{{ socialLinks?.linkedinLabel || 'LinkedIn Profile' }}</span>
              </div>
            </a>

            <!-- GitHub -->
            <a
              :href="socialLinks?.github || '#'"
              class="social-link"
              :aria-label="t('contact.social.github')"
              target="_blank"
              rel="noopener noreferrer"
              role="listitem"
            >
              <div class="social-link__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                </svg>
              </div>
              <div>
                <strong>{{ t('contact.social.github') }}</strong>
                <span>{{ socialLinks?.githubLabel || 'GitHub Profile' }}</span>
              </div>
            </a>

            <!-- Email -->
            <a
              :href="socialLinks?.email ? `mailto:${socialLinks.email}` : '#'"
              class="social-link"
              :aria-label="t('contact.social.email')"
              role="listitem"
            >
              <div class="social-link__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
              </div>
              <div>
                <strong>{{ t('contact.social.email') }}</strong>
                <span>{{ socialLinks?.email || 'email@example.com' }}</span>
              </div>
            </a>
          </div>
        </div>

        <!-- Right: Contact Form -->
        <div class="reveal-item" style="--reveal-delay: 0.2s">

          <!-- Success State -->
          <div
            v-if="success"
            class="flex flex-col items-center justify-center gap-4 p-10 rounded-[14px] bg-green-500/10 border border-green-500/30 text-center"
            role="alert"
            aria-live="polite"
          >
            <div class="w-14 h-14 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center text-green-400 text-2xl" aria-hidden="true">
              ✓
            </div>
            <p class="text-green-400 font-medium text-[0.95rem]">
              {{ t('contact.form.success') }}
            </p>
          </div>

          <!-- Form -->
          <form
            v-else
            class="contact__form glass reveal-item"
            style="--reveal-delay: 0.2s"
            @submit.prevent="submit"
            novalidate
            aria-label="Contact form"
          >
            <!-- Honeypot (hidden from humans, visible to bots) -->
            <input
              type="text"
              name="_hp"
              tabindex="-1"
              autocomplete="off"
              style="display:none"
              v-model="honeypot"
              aria-hidden="true"
            />

            <!-- Cloudflare Turnstile widget (rendered only when site key is configured) -->
            <div
              v-if="turnstileSiteKey"
              ref="turnstileRef"
              class="cf-turnstile"
              :data-sitekey="turnstileSiteKey"
              data-theme="dark"
              data-size="flexible"
            ></div>

            <!-- Row: Name + Email -->
            <div class="form-row">
              <!-- Name -->
              <div class="form-group">
                <label for="contact-name" class="form-group label">
                  {{ t('contact.form.name') }}
                </label>
                <input
                  id="contact-name"
                  type="text"
                  v-model="form.name"
                  :placeholder="t('contact.form.namePlaceholder')"
                  class="form-input"
                  :class="{ 'border-red-500/60': fieldErrors.name }"
                  :aria-invalid="!!fieldErrors.name"
                  :aria-describedby="fieldErrors.name ? 'error-name' : undefined"
                  autocomplete="name"
                />
                <p
                  v-if="fieldErrors.name"
                  id="error-name"
                  class="text-red-400 text-[0.78rem] mt-0.5"
                  role="alert"
                >
                  {{ fieldErrors.name }}
                </p>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label for="contact-email" class="form-group label">
                  {{ t('contact.form.email') }}
                </label>
                <input
                  id="contact-email"
                  type="email"
                  v-model="form.email"
                  :placeholder="t('contact.form.emailPlaceholder')"
                  class="form-input"
                  :class="{ 'border-red-500/60': fieldErrors.email }"
                  :aria-invalid="!!fieldErrors.email"
                  :aria-describedby="fieldErrors.email ? 'error-email' : undefined"
                  autocomplete="email"
                />
                <p
                  v-if="fieldErrors.email"
                  id="error-email"
                  class="text-red-400 text-[0.78rem] mt-0.5"
                  role="alert"
                >
                  {{ fieldErrors.email }}
                </p>
              </div>
            </div>

            <!-- Subject -->
            <div class="flex flex-col gap-1.5">
              <label for="contact-subject" class="text-[0.85rem] font-medium text-gray-400">
                {{ t('contact.form.subject') }}
              </label>
              <input
                id="contact-subject"
                type="text"
                v-model="form.subject"
                :placeholder="t('contact.form.subjectPlaceholder')"
                :class="[
                  'px-4 py-3 bg-white/[0.04] border rounded-lg text-white text-[0.9rem] font-[inherit] outline-none transition-all duration-300 placeholder:text-gray-600',
                  fieldErrors.subject
                    ? 'border-red-500/60 focus:border-red-500 focus:shadow-[0_0_0_3px_rgba(239,68,68,0.2)]'
                    : 'border-white/[0.08] focus:border-purple-500 focus:shadow-[0_0_0_3px_rgba(124,58,237,0.25),0_0_20px_rgba(124,58,237,0.15)]'
                ]"
                :aria-invalid="!!fieldErrors.subject"
                :aria-describedby="fieldErrors.subject ? 'error-subject' : undefined"
                autocomplete="off"
              />
              <p
                v-if="fieldErrors.subject"
                id="error-subject"
                class="text-red-400 text-[0.78rem] mt-0.5"
                role="alert"
              >
                {{ fieldErrors.subject }}
              </p>
            </div>

            <!-- Message -->
            <div class="flex flex-col gap-1.5">
              <label for="contact-message" class="text-[0.85rem] font-medium text-gray-400">
                {{ t('contact.form.message') }}
              </label>
              <textarea
                id="contact-message"
                v-model="form.body"
                :placeholder="t('contact.form.messagePlaceholder')"
                rows="5"
                :class="[
                  'px-4 py-3 bg-white/[0.04] border rounded-lg text-white text-[0.9rem] font-[inherit] outline-none transition-all duration-300 placeholder:text-gray-600 resize-y min-h-[130px]',
                  fieldErrors.body
                    ? 'border-red-500/60 focus:border-red-500 focus:shadow-[0_0_0_3px_rgba(239,68,68,0.2)]'
                    : 'border-white/[0.08] focus:border-purple-500 focus:shadow-[0_0_0_3px_rgba(124,58,237,0.25),0_0_20px_rgba(124,58,237,0.15)]'
                ]"
                :aria-invalid="!!fieldErrors.body"
                :aria-describedby="fieldErrors.body ? 'error-body' : undefined"
              ></textarea>
              <p
                v-if="fieldErrors.body"
                id="error-body"
                class="text-red-400 text-[0.78rem] mt-0.5"
                role="alert"
              >
                {{ fieldErrors.body }}
              </p>
            </div>

            <!-- Server-side errors (rate limit, etc.) -->
            <div
              v-if="serverError"
              class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-[0.85rem]"
              role="alert"
            >
              {{ serverError }}
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="submitting"
              class="relative w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm text-white bg-gradient-to-br from-purple-600 to-purple-800 shadow-[0_0_30px_rgba(124,58,237,0.4)] hover:shadow-[0_0_50px_rgba(124,58,237,0.6)] hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-transparent overflow-hidden"
              :aria-busy="submitting"
            >
              <!-- Hover overlay -->
              <span class="absolute inset-0 bg-gradient-to-br from-purple-700 to-cyan-600 opacity-0 hover:opacity-100 transition-opacity duration-300 rounded-xl" aria-hidden="true"></span>
              <span class="relative z-10 flex items-center gap-2">
                <span v-if="submitting">
                  {{ t('contact.form.sending') }}
                  <svg class="inline-block ml-1 animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                  </svg>
                </span>
                <span v-else class="flex items-center gap-2">
                  {{ t('contact.form.send') }}
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                  </svg>
                </span>
              </span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { validateContactForm } from '@/utils/validators.js';
import { useIntersectionObserver } from '@/composables/useIntersectionObserver.js';

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  socialLinks: {
    type: Object,
    default: () => ({}),
  },
});

const { t } = useI18n();
const page = usePage();

// Turnstile site key from Inertia shared props or meta
const turnstileSiteKey = computed(() => page.props.turnstileSiteKey ?? null);
const turnstileRef = ref(null);

// ── State ──────────────────────────────────────────────────────────────────
const honeypot   = ref('');
const submitting = ref(false);
const success    = ref(false);

// Client-side validation errors
const clientErrors = ref({ name: '', email: '', subject: '', body: '' });

// Inertia useForm for submission
const form = useForm({
  name:               '',
  email:              '',
  subject:            '',
  body:               '',
  _hp:                '',
  cf_turnstile_token: '',
});

// ── Scroll Reveal ──────────────────────────────────────────────────────────
const { observeEl } = useIntersectionObserver(
  (entry) => { if (entry.isIntersecting) entry.target.classList.add('is-visible'); },
  { threshold: 0.1 }
);

onMounted(() => {
  document.querySelectorAll('#contact .reveal-item').forEach(el => observeEl(el));
});

// Merge client-side errors with server-side errors (server takes precedence)
const fieldErrors = computed(() => {
  const serverErrors = page.props.errors ?? {};
  return {
    name:    serverErrors.name    || clientErrors.value.name,
    email:   serverErrors.email   || clientErrors.value.email,
    subject: serverErrors.subject || clientErrors.value.subject,
    body:    serverErrors.body    || clientErrors.value.body,
  };
});

// Non-field server errors (rate limit, etc.)
const serverError = computed(() => {
  const errors = page.props.errors ?? {};
  return errors.rate_limit || errors.honeypot || null;
});

// ── Methods ────────────────────────────────────────────────────────────────
function submit() {
  // Clear previous client errors
  clientErrors.value = { name: '', email: '', subject: '', body: '' };

  // Client-side validation
  const { valid, errors } = validateContactForm({
    name:    form.name,
    email:   form.email,
    subject: form.subject,
    body:    form.body,
  });

  if (!valid) {
    clientErrors.value = errors;
    return;
  }

  // Sync honeypot value into form data
  form._hp = honeypot.value;

  // Collect Turnstile token if widget is present
  if (turnstileRef.value) {
    const tokenInput = turnstileRef.value.querySelector('[name="cf-turnstile-response"]');
    form.cf_turnstile_token = tokenInput?.value ?? '';
  }

  submitting.value = true;

  form.post('/contact', {
    preserveScroll: true,
    onSuccess: () => {
      success.value = true;
      form.reset();
      honeypot.value = '';
    },
    onError: () => {
      // Server errors are surfaced via page.props.errors / fieldErrors computed
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
}
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
</style>
