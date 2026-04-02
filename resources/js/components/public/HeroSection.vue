<template>
  <section
    class="hero relative overflow-hidden"
    style="background: var(--bg);"
    id="home"
    ref="sectionRef"
    aria-label="Hero section"
  >
    <!-- Particle Network Canvas -->
    <canvas
      ref="canvasRef"
      class="absolute inset-0 w-full h-full pointer-events-none"
      aria-hidden="true"
    ></canvas>

    <!-- Orb Backgrounds -->
    <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-purple-600/20 blur-[120px] pointer-events-none orb-float-1" aria-hidden="true"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-cyan-500/15 blur-[120px] pointer-events-none orb-float-2" aria-hidden="true"></div>
    <div class="absolute top-[40%] left-[50%] w-[400px] h-[400px] rounded-full bg-purple-800/10 blur-[100px] pointer-events-none orb-float-3" aria-hidden="true"></div>

    <!-- Noise Texture Overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.03] noise-texture" aria-hidden="true"></div>

    <!-- Main Content -->
    <div class="container hero__inner" style="position: relative; z-index: 1;">

      <!-- Left: Text Content -->
      <div class="hero__content" ref="contentRef">

        <!-- Badge -->
        <div class="hero__badge">
          <span class="hero__badge-dot" aria-hidden="true"></span>
          {{ t('hero.badge') }}
        </div>

        <!-- Developer Name -->
        <h1 class="hero__name" style="--d:.1s">
          {{ props.name.split(' ')[0] }}<br/>
          <span class="hero__name-accent">
            {{ props.name.split(' ').slice(1).join(' ') || props.name }}
          </span>
        </h1>

        <!-- Typing Animation Row -->
        <div class="hero__typing-row" style="--d:.2s" aria-live="polite" aria-atomic="true">
          <span id="typingPrefix">{{ typingPrefix }}</span>
          <span class="hero__typing">{{ displayedText }}</span>
          <span class="hero__cursor" aria-hidden="true">_</span>
        </div>

        <!-- Description -->
        <p class="hero__desc" style="--d:.3s" v-html="sanitizedDescription"></p>

        <!-- CTA Buttons -->
        <div class="hero__actions" style="--d:.4s">
          <a
            href="#portfolio"
            class="btn btn--glow"
            :aria-label="t('hero.cta.portfolio')"
            @click.prevent="scrollToSection('#portfolio')"
          >
            <span>{{ t('hero.cta.portfolio') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </a>
          <a
            href="#contact"
            class="btn btn--outline"
            :aria-label="t('hero.cta.contact')"
            @click.prevent="scrollToSection('#contact')"
          >
            {{ t('hero.cta.contact') }}
          </a>
        </div>

        <!-- Stack Pills -->
        <div class="hero__stack" style="--d:.5s" :aria-label="t('hero.stack')">
          <span
            v-for="tech in stackTechs"
            :key="tech"
            class="stack-pill"
          >
            {{ tech }}
          </span>
        </div>
      </div>

      <!-- Right: Visual Card -->
      <div class="hero__visual" ref="visualRef">

        <!-- 3D Tilt Card -->
        <div
          class="hero__card tilt-card"
          ref="tiltCardRef"
          @mousemove="handleTilt"
          @mouseleave="resetTilt"
          aria-label="PHP code snippet"
        >
          <!-- Card Glow -->
          <div class="hero__card-glow" aria-hidden="true"></div>

          <!-- Card Header -->
          <div class="hero__card-header">
            <span class="dot dot--red" aria-hidden="true"></span>
            <span class="dot dot--yellow" aria-hidden="true"></span>
            <span class="dot dot--green" aria-hidden="true"></span>
            <span class="hero__card-file">Developer.php</span>
          </div>

          <!-- Code Content -->
          <pre class="hero__code" aria-label="PHP code example"><code><span class="c-comment">// Nugraha Aryaguna — 2025</span>
<span class="c-purple">class</span> <span class="c-yellow">Developer</span> <span class="c-white">{</span>

  <span class="c-blue">public</span> <span class="c-purple">string</span> <span class="c-white">$name</span>
    = <span class="c-green">'{{ props.name }}'</span><span class="c-white">;</span>

  <span class="c-blue">public</span> <span class="c-purple">array</span> <span class="c-white">$stack</span> = [
    <span class="c-green">'Laravel'</span><span class="c-white">,</span> <span class="c-green">'Vue.js'</span><span class="c-white">,</span>
    <span class="c-green">'Inertia'</span><span class="c-white">,</span> <span class="c-green">'MySQL'</span><span class="c-white">,</span>
  ]<span class="c-white">;</span>

  <span class="c-blue">public function</span> <span class="c-yellow">passion</span><span class="c-white">():</span> <span class="c-purple">string</span>
  <span class="c-white">{</span>
    <span class="c-blue">return</span> <span class="c-green">'Clean &amp; Scalable'</span><span class="c-white">;</span>
  <span class="c-white">}</span>
<span class="c-white">}</span></code></pre>
        </div>

        <!-- Floating Badges -->
        <div class="float-badge float-badge--1" aria-label="3+ tahun pengalaman">
          <span aria-hidden="true">⚡</span> 3+ Tahun
        </div>
        <div class="float-badge float-badge--2" aria-label="12 proyek selesai">
          <span aria-hidden="true">🚀</span> 12 Proyek
        </div>
        <div class="float-badge float-badge--3" aria-label="Clean code">
          <span aria-hidden="true">⭐</span> Clean Code
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="hero__scroll" aria-label="Scroll ke bawah">
      <div class="hero__scroll-mouse">
        <div class="hero__scroll-wheel" aria-hidden="true"></div>
      </div>
      <span>{{ t('hero.scrollDown') }}</span>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { gsap } from 'gsap';

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  name: {
    type: String,
    default: 'Nugraha Aryaguna',
  },
  professions: {
    type: Array,
    default: () => ['Full-Stack Developer', 'Laravel Specialist', 'Vue.js Enthusiast', 'Problem Solver'],
  },
  description: {
    type: String,
    default: 'Membangun aplikasi web yang cepat, aman, dan elegan.<br/>Spesialis <strong>Laravel</strong> &amp; <strong>Vue.js</strong> — dari arsitektur hingga pixel.',
  },
  typingSpeed: {
    type: Number,
    default: 80,
  },
});

const { t, locale } = useI18n();

// ── Refs ───────────────────────────────────────────────────────────────────
const sectionRef   = ref(null);
const contentRef   = ref(null);
const visualRef    = ref(null);
const canvasRef    = ref(null);
const tiltCardRef  = ref(null);

// ── Stack pills ────────────────────────────────────────────────────────────
const stackTechs = ['Laravel', 'Vue.js 3', 'Inertia.js', 'Tailwind', 'MySQL'];

// ── Typing prefix (locale-aware) ───────────────────────────────────────────
const typingPrefix = computed(() => locale.value === 'en' ? 'A ' : 'Seorang ');

// ── Sanitized description (basic XSS guard — allow only safe tags) ─────────
const sanitizedDescription = computed(() => {
  // Allow only <strong>, <br>, <em> tags
  return props.description
    .replace(/<(?!\/?(?:strong|br|em)\b)[^>]*>/gi, '');
});

// ── Typing Animation ───────────────────────────────────────────────────────
const displayedText = ref('');
let typingTimer = null;
let profIdx = 0;
let charIdx = 0;
let isDeleting = false;

function runTyping() {
  const word = props.professions[profIdx] ?? '';

  if (!isDeleting) {
    charIdx++;
    displayedText.value = word.slice(0, charIdx);
    if (charIdx === word.length) {
      isDeleting = true;
      typingTimer = setTimeout(runTyping, 1800);
      return;
    }
    typingTimer = setTimeout(runTyping, props.typingSpeed);
  } else {
    charIdx--;
    displayedText.value = word.slice(0, charIdx);
    if (charIdx === 0) {
      isDeleting = false;
      profIdx = (profIdx + 1) % props.professions.length;
      typingTimer = setTimeout(runTyping, 400);
      return;
    }
    typingTimer = setTimeout(runTyping, Math.floor(props.typingSpeed * 0.55));
  }
}

// ── Particle Canvas ────────────────────────────────────────────────────────
let animFrameId = null;

function initParticles() {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  function resize() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x       = Math.random() * canvas.width;
      this.y       = Math.random() * canvas.height;
      this.size    = Math.random() * 1.5 + 0.5;
      this.speedX  = (Math.random() - 0.5) * 0.3;
      this.speedY  = (Math.random() - 0.5) * 0.3;
      this.opacity = Math.random() * 0.4 + 0.1;
      this.color   = Math.random() > 0.5 ? '124,58,237' : '6,182,212';
    }
    update() {
      this.x += this.speedX;
      this.y += this.speedY;
      if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) {
        this.reset();
      }
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${this.color},${this.opacity})`;
      ctx.fill();
    }
  }

  const particles = [];
  for (let i = 0; i < 80; i++) particles.push(new Particle());

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    // Draw connections
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 120) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(124,58,237,${0.08 * (1 - dist / 120)})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
    animFrameId = requestAnimationFrame(animate);
  }
  animate();
}

// ── 3D Tilt Card ───────────────────────────────────────────────────────────
function handleTilt(e) {
  const card = tiltCardRef.value;
  if (!card) return;
  const rect = card.getBoundingClientRect();
  const x = (e.clientX - rect.left) / rect.width  - 0.5;
  const y = (e.clientY - rect.top)  / rect.height - 0.5;
  card.style.transform = `perspective(600px) rotateY(${x * 12}deg) rotateX(${-y * 12}deg) scale(1.02)`;
}

function resetTilt() {
  const card = tiltCardRef.value;
  if (!card) return;
  card.style.transform = 'perspective(600px) rotateY(0deg) rotateX(0deg) scale(1)';
}

// ── Smooth Scroll ──────────────────────────────────────────────────────────
function scrollToSection(anchor) {
  const el = document.querySelector(anchor);
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}

// ── GSAP Fade-in on Mount ──────────────────────────────────────────────────
onMounted(() => {
  // Animate each child element of contentRef with stagger
  if (contentRef.value) {
    const children = Array.from(contentRef.value.children);
    gsap.fromTo(
      children,
      { opacity: 0, y: 30 },
      { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', delay: 0.1 }
    );
  }

  // Animate visual card
  if (visualRef.value) {
    gsap.fromTo(
      visualRef.value,
      { opacity: 0, y: 30 },
      { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out', delay: 0.3 }
    );
  }

  // Start typing animation
  typingTimer = setTimeout(runTyping, 600);

  // Start particle canvas
  initParticles();
});

onUnmounted(() => {
  if (typingTimer) clearTimeout(typingTimer);
  if (animFrameId) cancelAnimationFrame(animFrameId);
});
</script>

<style scoped>
/* Orb float animations */
@keyframes orbFloat1 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(30px, -20px) scale(1.05); }
  66%       { transform: translate(-20px, 15px) scale(0.97); }
}
@keyframes orbFloat2 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  40%       { transform: translate(-25px, 20px) scale(1.04); }
  70%       { transform: translate(15px, -15px) scale(0.98); }
}
@keyframes orbFloat3 {
  0%, 100% { transform: translate(-50%, 0) scale(1); }
  50%       { transform: translate(-50%, -25px) scale(1.06); }
}

.orb-float-1 { animation: orbFloat1 8s ease-in-out infinite; }
.orb-float-2 { animation: orbFloat2 10s ease-in-out infinite; }
.orb-float-3 { animation: orbFloat3 7s ease-in-out infinite; }

/* Noise texture */
.noise-texture {
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
  background-repeat: repeat;
  background-size: 200px 200px;
}

/* Tilt card smooth transition on leave */
.tilt-card {
  transition: transform 0.3s ease;
}
</style>
