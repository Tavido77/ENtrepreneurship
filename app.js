/*
  app.js
  - Sample dataset and dynamic rendering of service cards
  - Search, chip filters, sort, and booking toast
  - Modify `services` array below to change sample data
*/

// Services will be loaded from the backend. Keep a local fallback dataset if the API is unavailable.
let services = [];
const DEFAULT_SERVICES = [
  {id:1, name:"Box Braids", duration:"2h 30m", desc:"Protective style • Medium length", price:120, tags:["braiding"], img:"https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=60" },
  {id:2, name:"Wig Installation", duration:"1h 30m", desc:"Lace melt • Custom fit", price:95, tags:["wig"], img:"https://images.unsplash.com/photo-1556228720-4cdbf0e0d7c4?auto=format&fit=crop&w=800&q=60" },
  {id:3, name:"Color & Gloss", duration:"2h", desc:"Ammonia-free • Shine boost", price:140, tags:["color"], img:"https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=60" },
  {id:4, name:"Classic Manicure", duration:"45m", desc:"Cuticle care • Polish", price:35, tags:["manicure"], img:"https://images.unsplash.com/photo-1519744792095-2f2205e87b6f?auto=format&fit=crop&w=800&q=60" },
  {id:5, name:"Silk Press", duration:"1h 15m", desc:"Sleek finish • Heat protectant", price:85, tags:["styling"], img:"https://images.unsplash.com/photo-1556228453-9b76b2c8c2a9?auto=format&fit=crop&w=800&q=60" },
  {id:6, name:"Spa Pedicure", duration:"1h", desc:"Soak • Exfoliate • Polish", price:55, tags:["manicure"], img:"https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=60" },
  {id:7, name:"Precision Haircut", duration:"50m", desc:"Shape • Trim • Style", price:60, tags:["styling"], img:"https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=800&q=60" },
  {id:8, name:"Formal Updo", duration:"1h", desc:"Events • Weddings", price:100, tags:["styling"], img:"https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=60" },
  {id:9, name:"Lash Extensions", duration:"45m", desc:"Classic set • Natural look", price:110, tags:["beauty"], img:"https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=800&q=60" }
];

async function loadServices(){
  try{
    const res = await fetch('/api/services');
    if(!res.ok) throw new Error('Fetch failed');
    const data = await res.json();
    services = data.services || DEFAULT_SERVICES;
  }catch(e){
    services = DEFAULT_SERVICES;
    showToast('Could not load services from API — using local data');
  }

  // render now that services are available
  filterAndRender();
  renderBookingServices();
  renderDashboard();
}

// Currency formatter for Ghana Cedi
function formatCurrency(amount){
  if(amount == null) return '₵0.00';
  // ensure number
  const n = Number(amount);
  return `₵${n.toLocaleString(undefined,{minimumFractionDigits: 2, maximumFractionDigits:2})}`;
}

// State
let activeTags = new Set();
let currentSort = 'asc';

// Elements
const grid = document.getElementById('services-grid');
const searchInput = document.getElementById('search');
const chips = Array.from(document.querySelectorAll('.chip'));
const clearBtn = document.getElementById('clear-filters');
const sortSelect = document.getElementById('sort-select');
const toast = document.getElementById('toast');
const hamburger = document.querySelector('.hamburger');
const sidebar = document.querySelector('.sidebar');

// Render functions
function renderCards(list){
  grid.innerHTML = '';
  if(list.length === 0){
    const empty = document.createElement('div');
    empty.textContent = 'No services found.';
    empty.style.color = 'var(--muted)';
    grid.appendChild(empty);
    return;
  }

  list.forEach(s => {
    const card = document.createElement('article');
    card.className = 'card';
    card.setAttribute('tabindex','0');

    card.innerHTML = `
      <div class="img">
        <img src="${s.img}" alt="${s.name} image" onerror="this.style.background='#f3eaea';this.src='';">
        <div class="duration-badge">${s.duration}</div>
      </div>
      <div class="body">
        <h3 class="service-title">${s.name}</h3>
        <p class="service-desc">${s.desc}</p>
        <div class="meta">
          <div class="price">${formatCurrency(s.price)}</div>
          <button class="book-btn" data-id="${s.id}">Book Now</button>
        </div>
      </div>
    `;

    grid.appendChild(card);
  });

  // attach book button handlers
  document.querySelectorAll('.book-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      const id = Number(e.currentTarget.dataset.id);
      const svc = services.find(x => x.id === id);
      if(svc) showToast(`Booked: ${svc.name} — ${formatCurrency(svc.price)}`);
    });
  });
}

// Filtering logic
function filterAndRender(){
  const q = searchInput.value.trim().toLowerCase();
  let results = services.filter(s => {
    const text = `${s.name} ${s.desc}`.toLowerCase();
    const matchesQuery = text.includes(q);
    const matchesTags = activeTags.size === 0 || s.tags.some(t => activeTags.has(t));
    return matchesQuery && matchesTags;
  });

  // Sorting
  results.sort((a,b) => currentSort === 'asc' ? a.price - b.price : b.price - a.price);

  renderCards(results);
}

// Toast
let toastTimer = null;
function showToast(message){
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=>{toast.classList.remove('show')},3000);
}

// Event bindings
searchInput.addEventListener('input', () => filterAndRender());

chips.forEach(button => {
  // skip the clear filter button here; handled separately
  if(button.classList.contains('clear')) return;
  button.addEventListener('click', () => {
    const tag = button.dataset.tag;
    if(activeTags.has(tag)){
      activeTags.delete(tag);
      button.classList.remove('active');
    } else {
      activeTags.add(tag);
      button.classList.add('active');
    }
    filterAndRender();
  });
});

clearBtn.addEventListener('click', () => {
  // Reset search, chips, sort
  searchInput.value = '';
  activeTags.clear();
  chips.forEach(c => c.classList.remove('active'));
  sortSelect.value = 'asc';
  currentSort = 'asc';
  filterAndRender();
});

sortSelect.addEventListener('change', (e) => {
  currentSort = e.target.value;
  filterAndRender();
});

// Sidebar toggle for mobile
hamburger.addEventListener('click', () => {
  const open = sidebar.classList.toggle('open');
  hamburger.setAttribute('aria-expanded', String(open));
});

// Initialize
loadServices();

/* ----------------------------
   Booking page logic
   - Renders services (reusing `services` dataset)
   - Mocked date/time slots and stylists
   - Updates summary card
*/
const bookingServicesEl = document.getElementById('booking-services');
const dateListEl = document.getElementById('date-list');
const timeSlotsEl = document.getElementById('time-slots');
const stylistsEl = document.getElementById('stylists');
const sumService = document.getElementById('sum-service');
const sumDuration = document.getElementById('sum-duration');
const sumStylist = document.getElementById('sum-stylist');
const sumDate = document.getElementById('sum-date');
const sumTime = document.getElementById('sum-time');
const sumTotal = document.getElementById('sum-total');
const confirmBtn = document.getElementById('confirm-booking');

// Booking state
const bookingState = {
  service: null,
  date: null,
  time: null,
  stylist: null,
  payment: 'salon'
};

// Render services (step 1)
function renderBookingServices(){
  if(!bookingServicesEl) return;
  bookingServicesEl.innerHTML = '';
  services.forEach(s => {
    const row = document.createElement('div');
    row.className = 'service-row';
    row.innerHTML = `
      <div>
        <div style="font-weight:700">${s.name}</div>
        <div class="service-meta">${s.duration} • ${s.desc}</div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <div style="font-weight:700">${formatCurrency(s.price)}</div>
        <button data-id="${s.id}">Select</button>
      </div>
    `;
    bookingServicesEl.appendChild(row);
  });

  bookingServicesEl.querySelectorAll('button').forEach(b => {
    b.addEventListener('click', (e)=>{
      const id = Number(e.currentTarget.dataset.id);
      bookingState.service = services.find(x=>x.id===id);
      updateSummary();
      showToast(`Selected service: ${bookingState.service.name}`);
    });
  });
}

// Mock dates: show next 7 days
function renderDates(){
  if(!dateListEl) return;
  dateListEl.innerHTML = '';
  const today = new Date();
  for(let i=0;i<7;i++){
    const d = new Date(); d.setDate(today.getDate()+i);
    const el = document.createElement('div');
    el.className = 'date-item';
    el.dataset.iso = d.toISOString();
    el.innerHTML = `<div>${d.toLocaleString(undefined,{weekday:'short'})}</div><div style="font-weight:700">${d.getDate()}</div>`;
    el.addEventListener('click', ()=>{
      document.querySelectorAll('.date-item').forEach(x=>x.classList.remove('active'));
      el.classList.add('active');
      bookingState.date = d;
      renderTimeSlots(d);
      updateSummary();
    });
    dateListEl.appendChild(el);
  }
}

// Mock times for a date
function renderTimeSlots(date){
  if(!timeSlotsEl) return;
  timeSlotsEl.innerHTML = '';
  const times = ['9:00 AM','10:30 AM','11:30 AM','12:30 PM','1:00 PM','2:00 PM'];
  times.forEach(t=>{
    const b = document.createElement('button');
    b.className = 'time-slot';
    b.textContent = t;
    b.addEventListener('click', ()=>{
      document.querySelectorAll('.time-slot').forEach(x=>x.classList.remove('selected'));
      b.classList.add('selected');
      bookingState.time = t;
      updateSummary();
    });
    timeSlotsEl.appendChild(b);
  });
}

// Mock stylists
const stylistsData = [
  {id:1,name:'Sophia',role:'Precision cuts',img:'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=60'},
  {id:2,name:'Amelia',role:'Color expert',img:'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=200&q=60'}
];

function renderStylists(){
  if(!stylistsEl) return;
  stylistsEl.innerHTML = '';
  stylistsData.forEach(s=>{
    const card = document.createElement('div');
    card.className = 'stylist-card';
    card.innerHTML = `
      <img src="${s.img}" alt="${s.name}">
      <div style="flex:1">
        <div style="font-weight:700">${s.name}</div>
        <div class="meta">${s.role}</div>
      </div>
      <button data-id="${s.id}">Select</button>
    `;
    stylistsEl.appendChild(card);
  });
  stylistsEl.querySelectorAll('button').forEach(b=>{
    b.addEventListener('click', (e)=>{
      const id = Number(e.currentTarget.dataset.id);
      bookingState.stylist = stylistsData.find(x=>x.id===id);
      updateSummary();
      showToast(`Selected stylist: ${bookingState.stylist.name}`);
    });
  });
}

// Payment option listener
document.querySelectorAll('input[name="payment"]').forEach(r=>{
  r.addEventListener('change', (e)=>{
    bookingState.payment = e.target.value;
  });
});

// Update summary
function updateSummary(){
  sumService.textContent = bookingState.service ? bookingState.service.name : '—';
  sumDuration.textContent = bookingState.service ? bookingState.service.duration : '—';
  sumStylist.textContent = bookingState.stylist ? bookingState.stylist.name : 'No preference';
  sumDate.textContent = bookingState.date ? bookingState.date.toDateString() : '—';
  sumTime.textContent = bookingState.time ? bookingState.time : '—';
  sumTotal.textContent = bookingState.service ? formatCurrency(bookingState.service.price) : formatCurrency(0);
}

// Confirm booking (POST to backend)
confirmBtn?.addEventListener('click', async ()=>{
  if(!bookingState.service || !bookingState.date || !bookingState.time){
    showToast('Please select a service, date and time before confirming.');
    return;
  }

  const payload = {
    serviceId: bookingState.service.id,
    date: bookingState.date.toISOString(),
    time: bookingState.time,
    stylist: bookingState.stylist ? bookingState.stylist.name : null
  };

  try{
    const res = await fetch('/api/bookings', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    if(!res.ok){
      const err = await res.json().catch(()=>({error: 'unknown'}));
      showToast(`Booking failed: ${err.error || res.statusText}`);
      return;
    }
    const created = await res.json();

    // Mirror to UI appointments list (in-memory)
    appointments.unshift({
      id: created.id || Date.now(),
      date: created.date,
      service: created.serviceName || bookingState.service.name,
      stylist: created.stylist || bookingState.stylist?.name || 'No preference',
      duration: bookingState.service.duration,
      status: 'confirmed'
    });

    showToast(`Booked: ${bookingState.service.name} on ${bookingState.date.toLocaleDateString()} at ${bookingState.time}`);

    // reset simplified booking
    bookingState.service = null; bookingState.date=null; bookingState.time=null; bookingState.stylist=null;
    renderBookingServices(); renderDates(); renderStylists(); updateSummary(); renderAppointments(); renderDashboard();

  }catch(err){
    console.error(err);
    showToast('Booking error — please try again');
  }
});

// Initialize booking UI (booking services will be rendered after services load)
renderDates(); renderStylists(); updateSummary();

/* ----------------------------
   Dashboard logic
   - Populate stat cards and popular services (demo values)
*/
function renderDashboard(){
  const salesEl = document.getElementById('stat-sales');
  const bookingsEl = document.getElementById('stat-bookings');
  const avgEl = document.getElementById('stat-average');
  const returningEl = document.getElementById('stat-returning');

  // Demo aggregations
  const totalRevenue = services.reduce((s,a)=>s + a.price * (Math.floor(Math.random()*100)+10), 0);
  const totalBookings = services.reduce((s,a)=>s + (Math.floor(Math.random()*200)+20), 0);
  const avgOrder = Math.round(totalRevenue / Math.max(1, totalBookings));
  const returning = 62; // static demo

  if(salesEl) salesEl.textContent = formatCurrency(Number(totalRevenue));
  if(bookingsEl) bookingsEl.textContent = `${Math.floor(totalBookings/10)}`; // scaled demo
  if(avgEl) avgEl.textContent = formatCurrency(avgOrder);
  if(returningEl) returningEl.textContent = `${returning}%`;

  // Popular services list
  const popEl = document.getElementById('popular-list');
  if(popEl){
    popEl.innerHTML = '';
    // create demo popularity by randomizing bookings for each service
    const demo = services.map(s=>({
      name: s.name,
      bookings: Math.floor(Math.random()*300)+50,
      revenue: (Math.floor(Math.random()*300)+50) * s.price
    })).sort((a,b)=>b.bookings - a.bookings);

    demo.forEach(item =>{
      const row = document.createElement('div');
      row.className = 'popular-item';
      row.innerHTML = `
        <div class="label"><div class="popular-icon">✂️</div><div>${item.name}</div></div>
        <div style="display:flex;gap:18px;min-width:180px;justify-content:flex-end"><div>${item.bookings}</div><div>${formatCurrency(Number(item.revenue))}</div></div>
      `;
      popEl.appendChild(row);
    });
  }
}

/* ----------------------------
   Appointments logic
*/
const upcomingEl = document.getElementById('upcoming-list');
const pastEl = document.getElementById('past-list');

let appointments = [
  {id:1,date:'2025-11-28T10:30',service:'Balayage & Style', stylist:'Amelia', duration:'2h', status:'confirmed'},
  {id:2,date:'2025-12-03T14:00',service:'Classic Manicure', stylist:'Sophia', duration:'45m', status:'confirmed'},
  {id:3,date:'2025-12-15T09:15',service:'Hydrating Facial', stylist:'Naomi', duration:'60m', status:'confirmed'}
];

let pastAppointments = [
  {id:11,date:'2025-11-08T12:00',service:'Keratin Treatment', stylist:'Amelia', duration:'1h30m', status:'completed'},
  {id:12,date:'2025-10-26T16:00',service:'Gel Pedicure', stylist:'Sophia', duration:'50m', status:'completed'},
  {id:13,date:'2025-10-10T11:30',service:'Root Touch-up', stylist:'Amelia', duration:'1h', status:'cancelled'}
];

function formatDate(iso){
  const d = new Date(iso);
  return `${d.toLocaleString(undefined,{month:'short'})} ${d.getDate()}, ${d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
}

function renderAppointments(){
  upcomingEl.innerHTML = '';
  appointments.forEach(a=>{
    const card = document.createElement('div');
    card.className = 'appt-card';
    card.innerHTML = `
      <div>
        <div style="font-weight:700">${a.service}</div>
        <div class="appt-info">with ${a.stylist} • ${a.duration}</div>
      </div>
      <div style="display:flex;gap:8px;flex-direction:column;align-items:flex-end">
        <div style="font-weight:600">${formatDate(a.date)}</div>
        <div style="display:flex;gap:8px"><span class="badge">Confirmed</span><button class="btn-small" data-id="${a.id}" data-action="reschedule">Reschedule</button><button class="btn-small btn-cancel" data-id="${a.id}" data-action="cancel">Cancel</button></div>
      </div>
    `;
    upcomingEl.appendChild(card);
  });

  pastEl.innerHTML = '';
  pastAppointments.forEach(a=>{
    const card = document.createElement('div');
    card.className = 'appt-card';
    card.innerHTML = `
      <div>
        <div style="font-weight:700">${a.service}</div>
        <div class="appt-info">with ${a.stylist} • ${a.duration}</div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end">
        <div style="font-weight:600">${formatDate(a.date)}</div>
        <div><span class="badge">${a.status === 'completed' ? 'Completed' : 'Cancelled'}</span></div>
      </div>
    `;
    pastEl.appendChild(card);
  });

  // attach actions
  document.querySelectorAll('[data-action="cancel"]').forEach(b=>{
    b.addEventListener('click',(e)=>{
      const id = Number(e.currentTarget.dataset.id);
      const idx = appointments.findIndex(x=>x.id===id);
      if(idx>-1){
        const [removed] = appointments.splice(idx,1);
        removed.status = 'cancelled';
        pastAppointments.unshift(removed);
        renderAppointments();
        showToast('Appointment cancelled');
      }
    });
  });

  document.querySelectorAll('[data-action="reschedule"]').forEach(b=>{
    b.addEventListener('click',(e)=>{
      const id = Number(e.currentTarget.dataset.id);
      showToast('Open reschedule dialog (demo)');
    });
  });
}

renderAppointments();

// Hero and bottom CTA interactions
document.getElementById('hero-book')?.addEventListener('click', ()=> showToast('Opening booking panel...'));
document.getElementById('hero-view')?.addEventListener('click', ()=> { document.getElementById('search').focus(); });
document.getElementById('hero-login')?.addEventListener('click', ()=> showToast('Open login (demo)'));
document.getElementById('bottom-book')?.addEventListener('click', ()=> showToast('Opening booking panel...'));
document.getElementById('bottom-view')?.addEventListener('click', ()=> { document.getElementById('search').focus(); });

// Testimonials carousel simple controls
const tList = document.querySelector('.t-list');
const tPrev = document.getElementById('t-prev');
const tNext = document.getElementById('t-next');
if(tPrev && tNext && tList){
  let tIndex = 0;
  const cards = Array.from(tList.children);
  function showTestimonial(i){
    tIndex = (i + cards.length) % cards.length;
    const w = cards[tIndex].offsetWidth + 14; // include gap
    tList.scrollTo({left: w * tIndex, behavior: 'smooth'});
  }
  tPrev.addEventListener('click', ()=> showTestimonial(tIndex-1));
  tNext.addEventListener('click', ()=> showTestimonial(tIndex+1));
}

// Accessibility: close sidebar on outside click (mobile)
document.addEventListener('click', (e) => {
  if(window.innerWidth > 820) return;
  if(!sidebar.classList.contains('open')) return;
  if(e.target.closest('.sidebar') || e.target.closest('.hamburger')) return;
  sidebar.classList.remove('open');
  hamburger.setAttribute('aria-expanded','false');
});

// Highlight active nav link based on section in view
const navLinks = Array.from(document.querySelectorAll('.sidebar nav a'));
const sections = Array.from(document.querySelectorAll('main section[id]'));

// Close sidebar on nav click (mobile) and set active immediately
navLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    // close sidebar on mobile
    if(window.innerWidth <= 820){
      sidebar.classList.remove('open');
      hamburger.setAttribute('aria-expanded','false');
    }
    navLinks.forEach(l=>l.classList.remove('active'));
    link.classList.add('active');
  });
});

// IntersectionObserver to update active nav link while scrolling
if('IntersectionObserver' in window){
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        const id = entry.target.id;
        navLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === `#${id}`));
      }
    });
  },{root: null, rootMargin: '-40% 0px -40% 0px', threshold: 0});
  sections.forEach(s => io.observe(s));
}
