<template>
  <div
    class="loading-spinner"
    :class="[`loading-spinner--${size}`]"
    role="status"
    :aria-label="label"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      :width="dimensions"
      :height="dimensions"
      viewBox="0 0 24 24"
      fill="none"
      aria-hidden="true"
    >
      <circle
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        class="loading-spinner__track"
      />
      <path
        d="M12 2a10 10 0 0 1 10 10"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        class="loading-spinner__arc"
      />
    </svg>
    <span v-if="showLabel" class="loading-spinner__label">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  label: {
    type: String,
    default: 'Memuat...',
  },
  showLabel: {
    type: Boolean,
    default: false,
  },
});

const dimensions = computed(() => {
  const map = { sm: 16, md: 24, lg: 40 };
  return map[props.size];
});
</script>

<style scoped>
.loading-spinner {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--accent);
}

.loading-spinner--sm { font-size: 0.75rem; }
.loading-spinner--md { font-size: 0.875rem; }
.loading-spinner--lg { font-size: 1rem; }

.loading-spinner__track {
  opacity: 0.2;
}

.loading-spinner__arc {
  animation: spin 0.8s linear infinite;
  transform-origin: center;
}

.loading-spinner__label {
  color: var(--muted);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
</style>
