/* CUSTOM CURSOR */
const cursor = document.getElementById('cursor');
const follower = document.getElementById('cursorFollower');
let mx=0,my=0,fx=0,fy=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cursor.style.left=mx+'px';cursor.style.top=my+'px'});
(function animFollower(){fx+=(mx-fx)*.12;fy+=(my-fy)*.12;follower.style.left=fx+'px';follower.style.top=fy+'px';requestAnimationFrame(animFollower)})();
document.querySelectorAll('a,button,.project-card,.skill-badge,.social-link').forEach(el=>{
  el.addEventListener('mouseenter',()=>{cursor.style.transform='translate(-50%,-50%) scale(2.5)';cursor.style.background='transparent';cursor.style.border='1px solid var(--accent)';follower.style.transform='translate(-50%,-50%) scale(1.5)';follower.style.opacity='0'});
  el.addEventListener('mouseleave',()=>{cursor.style.transform='translate(-50%,-50%) scale(1)';cursor.style.background='var(--accent)';cursor.style.border='none';follower.style.transform='translate(-50%,-50%) scale(1)';follower.style.opacity='.6'});
});

/* PARTICLE CANVAS */
const canvas=document.getElementById('particleCanvas');
const ctx=canvas.getContext('2d');
let particles=[];
function resizeCanvas(){canvas.width=window.innerWidth;canvas.height=window.innerHeight}
resizeCanvas();
window.addEventListener('resize',resizeCanvas);
class Particle{
  constructor(){this.reset()}
  reset(){this.x=Math.random()*canvas.width;this.y=Math.random()*canvas.height;this.size=Math.random()*1.5+.5;this.speedX=(Math.random()-.5)*.3;this.speedY=(Math.random()-.5)*.3;this.opacity=Math.random()*.4+.1;this.color=Math.random()>.5?'124,58,237':'6,182,212'}
  update(){this.x+=this.speedX;this.y+=this.speedY;if(this.x<0||this.x>canvas.width||this.y<0||this.y>canvas.height)this.reset()}
  draw(){ctx.beginPath();ctx.arc(this.x,this.y,this.size,0,Math.PI*2);ctx.fillStyle=`rgba(${this.color},${this.opacity})`;ctx.fill()}
}
for(let i=0;i<80;i++)particles.push(new Particle());
function animParticles(){
  ctx.clearRect(0,0,canvas.width,canvas.height);
  particles.forEach(p=>{p.update();p.draw()});
  particles.forEach((p,i)=>{
    particles.slice(i+1).forEach(p2=>{
      const d=Math.hypot(p.x-p2.x,p.y-p2.y);
      if(d<120){ctx.beginPath();ctx.moveTo(p.x,p.y);ctx.lineTo(p2.x,p2.y);ctx.strokeStyle=`rgba(124,58,237,${.08*(1-d/120)})`;ctx.lineWidth=.5;ctx.stroke()}
    });
  });
  requestAnimationFrame(animParticles);
}
animParticles();

/* THEME */
const html=document.documentElement;
const themeBtn=document.getElementById('themeToggle');
const iconSun=document.getElementById('iconSun');
const iconMoon=document.getElementById('iconMoon');
function applyTheme(t){html.setAttribute('data-theme',t);iconSun.style.display=t==='light'?'none':'block';iconMoon.style.display=t==='light'?'block':'none';localStorage.setItem('theme',t)}
applyTheme(localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:light)').matches?'light':'dark'));
themeBtn.addEventListener('click',()=>applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark'));

/* LANGUAGE */
const langBtn=document.getElementById('langToggle');
const langLabel=document.getElementById('langLabel');
let lang=localStorage.getItem('lang')||'id';
const T={id:{prefix:'Seorang ',desc:'Membangun aplikasi web yang cepat, aman, dan elegan.\nSpesialis Laravel & Vue.js — dari arsitektur hingga pixel.',btn1:'Lihat Portofolio',btn2:'Hubungi Saya',cv:'Unduh CV'},en:{prefix:'A ',desc:'Building fast, secure, and elegant web applications.\nLaravel & Vue.js specialist — from architecture to pixel.',btn1:'View Portfolio',btn2:'Contact Me',cv:'Download CV'}};
function applyLang(l){lang=l;langLabel.textContent=l.toUpperCase();localStorage.setItem('lang',l);document.getElementById('typingPrefix').textContent=T[l].prefix}
applyLang(lang);
langBtn.addEventListener('click',()=>applyLang(lang==='id'?'en':'id'));

/* HEADER SCROLL */
const header=document.getElementById('header');
window.addEventListener('scroll',()=>header.classList.toggle('scrolled',scrollY>50),{passive:true});

/* HAMBURGER */
const hamburger=document.getElementById('hamburger');
const nav=document.getElementById('nav');
hamburger.addEventListener('click',()=>{const o=nav.classList.toggle('mobile-open');hamburger.classList.toggle('open',o);hamburger.setAttribute('aria-expanded',o)});
nav.querySelectorAll('.nav-link').forEach(l=>l.addEventListener('click',()=>{nav.classList.remove('mobile-open');hamburger.classList.remove('open');hamburger.setAttribute('aria-expanded',false)}));

/* TYPING ANIMATION */
const professions=['Full-Stack Developer','Laravel Specialist','Vue.js Enthusiast','Problem Solver'];
const typingEl=document.getElementById('typingText');
let pIdx=0,cIdx=0,deleting=false;
function type(){
  const word=professions[pIdx];
  if(!deleting){typingEl.textContent=word.slice(0,++cIdx);if(cIdx===word.length){deleting=true;setTimeout(type,1800);return}setTimeout(type,80)}
  else{typingEl.textContent=word.slice(0,--cIdx);if(cIdx===0){deleting=false;pIdx=(pIdx+1)%professions.length;setTimeout(type,400);return}setTimeout(type,45)}
}
type();

/* TILT CARD */
const tiltCard=document.querySelector('.tilt-card');
if(tiltCard){
  tiltCard.addEventListener('mousemove',e=>{
    const r=tiltCard.getBoundingClientRect();
    const x=(e.clientX-r.left)/r.width-.5;
    const y=(e.clientY-r.top)/r.height-.5;
    tiltCard.style.transform=`perspective(600px) rotateY(${x*12}deg) rotateX(${-y*12}deg) scale(1.02)`;
  });
  tiltCard.addEventListener('mouseleave',()=>tiltCard.style.transform='perspective(600px) rotateY(0) rotateX(0) scale(1)');
}

/* SCROLL REVEAL */
const revEls=document.querySelectorAll('.reveal-up');
const revObs=new IntersectionObserver((entries)=>{
  entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*60);revObs.unobserve(e.target)}});
},{threshold:.1});
revEls.forEach(el=>revObs.observe(el));

/* PROGRESS BARS */
const barObs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.querySelectorAll('.lang-bar__fill').forEach(b=>b.classList.add('animated'));barObs.unobserve(e.target)}});
},{threshold:.3});
const statsCard=document.querySelector('.stats__lang-card');
if(statsCard)barObs.observe(statsCard);

/* COUNT UP */
function countUp(el){
  const target=parseInt(el.dataset.target);
  const dur=1500;const step=target/(dur/16);let cur=0;
  const t=setInterval(()=>{cur=Math.min(cur+step,target);el.textContent=Math.floor(cur).toLocaleString();if(cur>=target)clearInterval(t)},16);
}
const cntObs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.querySelectorAll('.stat-card__num').forEach(countUp);cntObs.unobserve(e.target)}});
},{threshold:.3});
const statsNums=document.querySelector('.stats__numbers');
if(statsNums)cntObs.observe(statsNums);

/* TIMELINE TABS */
document.querySelectorAll('.timeline__tab').forEach(tab=>{
  tab.addEventListener('click',()=>{
    document.querySelectorAll('.timeline__tab').forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.timeline').forEach(c=>c.classList.add('hidden'));
    document.getElementById('tab-'+tab.dataset.tab).classList.remove('hidden');
  });
});

/* MODAL */
const projects=[
  {title:'Sistem Point of Sales',tags:['Laravel','Vue.js','MySQL','Tailwind CSS'],desc:'Aplikasi kasir modern untuk retail skala menengah. Fitur: manajemen stok real-time, laporan penjualan harian/bulanan, multi-kasir, dan integrasi printer thermal. Dibangun dengan arsitektur SPA menggunakan Laravel + Inertia.js + Vue.js 3.',demo:'https://demo.example.com',repo:'https://github.com/example/pos',g:'linear-gradient(135deg,#7c3aed,#4f46e5)'},
  {title:'Sistem Peminjaman Ruangan',tags:['Laravel','Inertia.js','Tailwind CSS','MySQL'],desc:'Platform booking ruangan untuk kampus/perkantoran dengan alur persetujuan multi-level (pemohon → kepala unit → admin). Dilengkapi notifikasi email otomatis, kalender interaktif, dan dashboard statistik penggunaan ruangan.',demo:null,repo:'https://github.com/example/room',g:'linear-gradient(135deg,#059669,#0891b2)'},
  {title:'REST API E-Commerce',tags:['Laravel','REST API','PostgreSQL','JWT','Swagger'],desc:'Backend API lengkap untuk platform e-commerce dengan autentikasi JWT, manajemen produk & kategori, keranjang belanja, integrasi payment gateway Midtrans, dan dokumentasi API menggunakan Swagger/OpenAPI.',demo:'https://api-docs.example.com',repo:null,g:'linear-gradient(135deg,#d97706,#dc2626)'},
];
const backdrop=document.getElementById('modalBackdrop');
const modalClose=document.getElementById('modalClose');
function openModal(idx){
  const p=projects[idx];
  document.getElementById('modalTitle').textContent=p.title;
  document.getElementById('modalThumb').style.background=p.g;
  document.getElementById('modalDesc').textContent=p.desc;
  document.getElementById('modalTags').innerHTML=p.tags.map(t=>`<span class="tag">${t}</span>`).join('');
  const links=[];
  if(p.demo)links.push(`<a href="${p.demo}" target="_blank" rel="noopener" class="btn btn--glow">&#128279; Live Demo</a>`);
  if(p.repo)links.push(`<a href="${p.repo}" target="_blank" rel="noopener" class="btn btn--outline">&#128230; Repository</a>`);
  document.getElementById('modalLinks').innerHTML=links.join('');
  backdrop.classList.add('open');
  document.body.style.overflow='hidden';
  modalClose.focus();
}
function closeModal(){backdrop.classList.remove('open');document.body.style.overflow=''}
modalClose.addEventListener('click',closeModal);
backdrop.addEventListener('click',e=>{if(e.target===backdrop)closeModal()});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal()});

/* CONTACT FORM */
document.getElementById('contactForm').addEventListener('submit',function(e){
  e.preventDefault();
  const btn=document.getElementById('submitBtn');
  btn.textContent='Mengirim...';btn.disabled=true;
  setTimeout(()=>{
    document.getElementById('formSuccess').style.display='block';
    this.reset();
    btn.innerHTML='Kirim Pesan <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
    btn.disabled=false;
  },1200);
});
