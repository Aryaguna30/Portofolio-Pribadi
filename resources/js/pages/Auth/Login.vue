<template>
  <div class="login-page">
    <!-- Background orbs -->
    <div class="login-orb login-orb--1" aria-hidden="true"></div>
    <div class="login-orb login-orb--2" aria-hidden="true"></div>

    <div class="login-card">
      <!-- Logo -->
      <div class="login-logo" aria-label="Admin Panel">
        <span class="login-logo__bracket">&lt;</span>
        <span class="login-logo__text">NA</span>
        <span class="login-logo__bracket">/&gt;</span>
      </div>

      <h1 class="login-title">Admin Panel</h1>
      <p class="login-subtitle">Masuk untuk mengelola konten website</p>

      <!-- General error -->
      <div v-if="form.errors.email" class="login-error" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="submit" novalidate>
        <!-- Email field -->
        <div class="login-field">
          <label for="email" class="login-label">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="login-input"
            :class="{ 'login-input--error': form.errors.email }"
            placeholder="admin@example.com"
            autocomplete="email"
            required
            aria-describedby="email-error"
          />
        </div>

        <!-- Password field -->
        <div class="login-field">
          <label for="password" class="login-label">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="login-input"
            :class="{ 'login-input--error': form.errors.password }"
            placeholder="••••••••"
            autocomplete="current-password"
            required
            aria-describedby="password-error"
          />
          <p v-if="form.errors.password" id="password-error" class="login-field-error">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          class="btn btn--glow btn--full login-submit"
          :disabled="form.processing"
          :aria-busy="form.processing"
        >
          <svg v-if="form.processing" class="login-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          <span>{{ form.processing ? 'Masuk...' : 'Masuk' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
});

function submit() {
  form.post('/admin/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg, #0a0e1a);
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
}

/* Background orbs */
.login-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
}

.login-orb--1 {
  width: 400px;
  height: 400px;
  background: rgba(124, 58, 237, 0.15);
  top: -100px;
  right: -100px;
}

.login-orb--2 {
  width: 300px;
  height: 300px;
  background: rgba(6, 182, 212, 0.1);
  bottom: -80px;
  left: -80px;
}

/* Card */
.login-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-card, #131929);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-radius: 16px;
  padding: 2.5rem 2rem;
  position: relative;
  z-index: 1;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(124, 58, 237, 0.1);
}

/* Logo */
.login-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  font-family: 'Fira Code', monospace;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
}

.login-logo__bracket {
  color: var(--accent, #7c3aed);
}

.login-logo__text {
  background: linear-gradient(135deg, var(--accent, #7c3aed), var(--accent2, #06b6d4));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Headings */
.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text, #e8edf5);
  text-align: center;
  margin-bottom: 0.375rem;
}

.login-subtitle {
  font-size: 0.875rem;
  color: var(--muted, #6b7a99);
  text-align: center;
  margin-bottom: 1.75rem;
}

/* Error banner */
.login-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  color: #f87171;
  font-size: 0.875rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
}

/* Form fields */
.login-field {
  margin-bottom: 1.25rem;
}

.login-label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text, #e8edf5);
  margin-bottom: 0.5rem;
}

.login-input {
  width: 100%;
  background: var(--bg, #0a0e1a);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  color: var(--text, #e8edf5);
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
  box-sizing: border-box;
}

.login-input::placeholder {
  color: var(--muted, #6b7a99);
}

.login-input:focus {
  border-color: var(--accent, #7c3aed);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}

.login-input--error {
  border-color: rgba(239, 68, 68, 0.5);
}

.login-input--error:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

.login-field-error {
  font-size: 0.8rem;
  color: #f87171;
  margin-top: 0.375rem;
}

/* Submit button */
.login-submit {
  margin-top: 0.5rem;
  padding: 0.875rem 1.5rem;
  font-size: 0.9375rem;
  border-radius: 10px;
}

.login-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

/* Spinner */
.login-spinner {
  width: 18px;
  height: 18px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
