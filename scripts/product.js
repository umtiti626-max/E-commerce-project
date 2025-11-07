// scripts/script.js
import { products } from "../Smartphones/data.js";

const IMAGE_PATH = "./Smartphones/";

/* --------- State --------- */
let state = {
  q: "",
  brands: new Set(),
  sort: "default",
  page: 1,
  perPage: 12,
  priceMin: null,
  priceMax: null,
  onlyWishlist: false
};

let wishlist = new Set(JSON.parse(localStorage.getItem("wishlist") || "[]"));

/* --------- DOM refs --------- */
const searchInput = document.getElementById("search-input");
const brandFiltersWrap = document.getElementById("brand-filters");
const productsGrid = document.getElementById("products-grid");
const resultsCount = document.getElementById("results-count");
const perPageSelect = document.getElementById("per-page");
const prevPageBtn = document.getElementById("prev-page");
const nextPageBtn = document.getElementById("next-page");
const pageNumbersWrap = document.getElementById("page-numbers");
const paginationWrap = document.getElementById("pagination");
const sortSelect = document.getElementById("sort-select");
const priceMinInput = document.getElementById("price-min");
const priceMaxInput = document.getElementById("price-max");
const priceApplyBtn = document.getElementById("price-apply");
const priceClearBtn = document.getElementById("price-clear");
const clearFiltersBtn = document.getElementById("clear-filters");
const wishlistBtn = document.getElementById("wishlist-open");
const wishlistCount = document.getElementById("wishlist-count");
const wishlistModal = document.getElementById("wishlist-modal");
const wishlistList = document.getElementById("wishlist-list");
const closeWishlistBtn = document.getElementById("close-wishlist");
const clearWishlistBtn = document.getElementById("clear-wishlist");
const onlyWishlistCheckbox = document.getElementById("only-wishlist");

const listingView = document.getElementById("listing-view");
const detailView = document.getElementById("detail-view");

/* ---------- Initialization ---------- */
lucide.createIcons();
updateWishlistUI();
renderBrandFilters();
bindEvents();
render();

/* ---------- Helpers ---------- */
function getUniqueBrands(){
  const s = new Set(products.map(p => p.brand).filter(Boolean));
  return Array.from(s).sort((a,b)=>a.localeCompare(b));
}

function formatCurrency(n){ return `$${Number(n).toLocaleString()}`; }
function clampPage(n, total){ return Math.max(1, Math.min(n, total)); }

/* ---------- Brand filters ---------- */
function renderBrandFilters(){
  const brands = getUniqueBrands();
  brandFiltersWrap.innerHTML = brands.map(b => {
    const id = `brand-${slug(b)}`;
    return `<div><input id="${id}" type="checkbox" data-brand="${escapeHtml(b)}" ${state.brands.has(b) ? 'checked':''} /> <label for="${id}">${b}</label></div>`;
  }).join("");
}

/* ---------- Filtering / Sorting / Pagination pipeline ---------- */
function applyFilters(){
  let out = products.slice();

  // search
  if(state.q){
    const q = state.q.toLowerCase();
    out = out.filter(p => (p.name + " " + p.brand + " " + p.description).toLowerCase().includes(q));
  }

  // brand filters
  if(state.brands.size){
    out = out.filter(p => state.brands.has(p.brand));
  }

  // price range
  if(state.priceMin != null) out = out.filter(p => p.price >= state.priceMin);
  if(state.priceMax != null) out = out.filter(p => p.price <= state.priceMax);

  // wishlist only
  if(state.onlyWishlist){
    out = out.filter(p => wishlist.has(p.id));
  }

  // sorting
  switch(state.sort){
    case "price-asc": out.sort((a,b)=>a.price - b.price); break;
    case "price-desc": out.sort((a,b)=>b.price - a.price); break;
    case "latest": out = out.slice().reverse(); break; // assume original array is chronological
    case "rating": out.sort((a,b)=>b.rating - a.rating); break;
    default: break;
  }

  return out;
}

/* ---------- Render products list ---------- */
function render(){
  const allFiltered = applyFilters();

  // pagination
  const perPage = state.perPage;
  const total = allFiltered.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  state.page = clampPage(state.page, pages);

  const start = (state.page - 1) * perPage;
  const pageItems = allFiltered.slice(start, start + perPage);

  // render grid
  productsGrid.innerHTML = pageItems.map(renderCard).join("");
  resultsCount.textContent = total;
  renderPagination(pages, state.page);
  updateWishlistUI();

  // show/hide listing/detail
  listingView.style.display = "block";
  detailView.classList.add("hidden");
}

/* ---------- Render single product card ---------- */
function renderCard(p){
  const isWish = wishlist.has(p.id);
  const ratingStars = renderStars(p.rating);
  return `
    <div class="product-card" data-id="${p.id}">
      <img src="${IMAGE_PATH + p.img}" alt="${escapeHtml(p.name)}">
      <div class="product-meta">
        <h4>${escapeHtml(p.name)}</h4>
        <p class="brand">${escapeHtml(p.brand)}</p>
        <div class="rating">${ratingStars} <small style="color:var(--muted);font-size:12px;">(${p.rating !== undefined && typeof p.rating === 'number' ? p.rating.toFixed(1) : 'N/A'})</small></div>
        <div class="price">${formatCurrency(p.price)}</div>
      </div>
      <div style="display:flex;gap:8px; margin-top:10px">
        <button class="btn add-view" data-id="${p.id}">View</button>
        <button class="btn small ${isWish ? '' : 'ghost'} wishlist-toggle" data-id="${p.id}">
          ${isWish ? '♥ Remove' : '♡ Wishlist'}
        </button>
      </div>
    </div>
  `;
}


/* ---------- Render pagination ---------- */
function renderPagination(totalPages, current){
  pageNumbersWrap.innerHTML = "";
  const maxButtons = 7;
  let start = Math.max(1, current - Math.floor(maxButtons/2));
  let end = Math.min(totalPages, start + maxButtons - 1);
  if(end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

  for(let i=start;i<=end;i++){
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.className = i === current ? "active" : "";
    btn.onclick = ()=>{ state.page = i; render(); window.scrollTo({top:100,behavior:'smooth'}); };
    pageNumbersWrap.appendChild(btn);
  }

  prevPageBtn.disabled = current === 1;
  nextPageBtn.disabled = current === totalPages;
}

/* ---------- Detail view (product page replacement) ---------- */
function showDetail(id){
  const p = products.find(x=>x.id===id);
  if(!p) return;
  listingView.style.display = "none";
  detailView.classList.remove("hidden");
  detailView.innerHTML = renderDetail(p);
  bindDetailEvents(p);
  window.scrollTo({top:0,behavior:'smooth'});
}

function renderDetail(p){
  const ratingStars = renderStars(p.rating);
  const thumbs = p.gallery.map((g, idx) => `<img src="${IMAGE_PATH + g}" data-src="${IMAGE_PATH + g}" class="${idx===0 ? 'active':''}" data-index="${idx}" />`).join("");
  const galleryMain = p.gallery[0] ? `<img id="gallery-main" class="gallery-main" src="${IMAGE_PATH + p.gallery[0]}" alt="${escapeHtml(p.name)}">` : "";
  return `
    <div class="detail-view-inner">
      <button id="back-to-list" class="btn ghost small" style="margin-bottom:10px">← Back to list</button>
      <div class="detail-top">
        <div class="gallery">
          ${galleryMain}
          <div class="gallery-thumbs">${thumbs}</div>
        </div>
        <div class="detail-info">
          <h2>${escapeHtml(p.name)}</h2>
          <div class="brand">${escapeHtml(p.brand)}</div>
          <div class="rating">${ratingStars} <small style="color:var(--muted);">(${p.rating.toFixed(1)})</small></div>
          <div class="price">${formatCurrency(p.price)}</div>
          <p style="margin-top:12px">${escapeHtml(p.description)}</p>

          <div style="margin-top:12px;display:flex;gap:10px">
            <button class="btn add-to-cart">Add to cart (placeholder)</button>
            <button class="btn wishlist-toggle ${wishlist.has(p.id)?'':''}" data-id="${p.id}">
              ${wishlist.has(p.id) ? '♥ Remove from wishlist' : '♡ Add to wishlist'}
            </button>
          </div>

          <div class="reviews">
            <h4>Customer reviews (placeholder)</h4>
            <div id="reviews-area">
              <p>No reviews yet. Be the first to review this product.</p>
            </div>
            <div style="margin-top:12px">
              <textarea id="review-text" placeholder="Write a short review…" style="width:100%;min-height:80px"></textarea>
              <button id="submit-review" class="btn small" style="margin-top:8px">Submit review (local)</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

function bindDetailEvents(p){
  const backBtn = document.getElementById("back-to-list");
  backBtn.onclick = ()=>{ detailView.classList.add("hidden"); listingView.style.display = "block"; };

  // gallery thumbs
  document.querySelectorAll(".gallery-thumbs img").forEach(img=>{
    img.addEventListener("click", (e)=>{
      document.querySelectorAll(".gallery-thumbs img").forEach(i=>i.classList.remove("active"));
      e.currentTarget.classList.add("active");
      const src = e.currentTarget.dataset.src;
      const main = document.getElementById("gallery-main");
      if(main) main.src = src;
    });
  });

  // wishlist toggle in detail
  const wishlistToggle = detailView.querySelector(".wishlist-toggle");
  if(wishlistToggle){
    wishlistToggle.onclick = ()=>{
      const id = Number(wishlistToggle.dataset.id || p.id);
      toggleWishlist(id);
      wishlistToggle.innerHTML = wishlist.has(id) ? '♥ Remove from wishlist' : '♡ Add to wishlist';
      updateWishlistUI();
    };
  }

  // submit review (local only)
  const submitReview = document.getElementById("submit-review");
  submitReview.onclick = ()=>{
    const area = document.getElementById("review-text");
    const text = (area.value || "").trim();
    if(!text) return alert("Please write a short review.");
    // push to local reviews (not persisted permanently in file)
    p.reviews.push({text, when: new Date().toISOString()});
    renderReviews(p);
    area.value = "";
  };

  renderReviews(p);
}

function renderReviews(p){
  const reviewsArea = document.getElementById("reviews-area");
  if(!reviewsArea) return;
  if(!p.reviews || p.reviews.length === 0){
    reviewsArea.innerHTML = `<p>No reviews yet. Be the first to review this product.</p>`;
  } else {
    reviewsArea.innerHTML = p.reviews.map(r => `<div style="padding:8px 0;border-bottom:1px solid rgba(0,0,0,.04)"><div style="font-size:13px;color:var(--muted)">${new Date(r.when).toLocaleString()}</div><div>${escapeHtml(r.text)}</div></div>`).join("");
  }
}

/* ---------- Wishlist ---------- */
function toggleWishlist(id){
  if(wishlist.has(id)) wishlist.delete(id);
  else wishlist.add(id);
  localStorage.setItem("wishlist", JSON.stringify(Array.from(wishlist)));
  updateWishlistUI();
  render(); // refresh UI to update wishlist buttons
}

function updateWishlistUI(){
  wishlistCount.textContent = wishlist.size;
}

/* ---------- Wishlist modal rendering ---------- */
function openWishlist(){
  wishlistModal.classList.remove("hidden");
  renderWishlistList();
}

function closeWishlist(e){
  if(e && e.target !== wishlistModal) return;
  wishlistModal.classList.add("hidden");
}

function renderWishlistList(){
  if(wishlist.size === 0){
    wishlistList.innerHTML = "<p>Your wishlist is empty.</p>";
    return;
  }
  const items = products.filter(p => wishlist.has(p.id));
  wishlistList.innerHTML = items.map(p => `
    <div style="display:flex;gap:10px;align-items:center;padding:8px 0;border-bottom:1px solid rgba(0,0,0,.04)">
      <img src="${IMAGE_PATH + p.img}" style="width:72px;height:56px;object-fit:cover;border-radius:6px" />
      <div style="flex:1">
        <div style="font-weight:600">${escapeHtml(p.name)}</div>
        <div style="color:var(--muted);font-size:13px">${escapeHtml(p.brand)} • ${formatCurrency(p.price)}</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:6px">
        <button class="btn small view-from-wishlist" data-id="${p.id}">View</button>
        <button class="btn small ghost remove-from-wishlist" data-id="${p.id}">Remove</button>
      </div>
    </div>
  `).join("");

  wishlistList.querySelectorAll(".view-from-wishlist").forEach(b=>{
    b.addEventListener("click", e=>{
      const id = Number(e.currentTarget.dataset.id);
      wishlistModal.classList.add("hidden");
      showDetail(id);
    });
  });

  wishlistList.querySelectorAll(".remove-from-wishlist").forEach(b=>{
    b.addEventListener("click", e=>{
      const id = Number(e.currentTarget.dataset.id);
      wishlist.delete(id);
      localStorage.setItem("wishlist", JSON.stringify(Array.from(wishlist)));
      renderWishlistList();
      render();
    });
  });
}

/* ---------- Events ---------- */
function bindEvents(){
  // search
  searchInput.addEventListener("input", debounce((e)=>{
    state.q = e.target.value.trim();
    state.page = 1;
    render();
  }, 250));

  // per page
  perPageSelect.addEventListener("change", e=>{
    state.perPage = Number(e.target.value);
    state.page = 1;
    render();
  });

  // pagination next/prev
  prevPageBtn.addEventListener("click", ()=>{
    state.page = Math.max(1, state.page - 1);
    render();
  });
  nextPageBtn.addEventListener("click", ()=>{
    state.page = state.page + 1;
    render();
  });

  // sort
  sortSelect.addEventListener("change", e=>{
    state.sort = e.target.value;
    state.page = 1;
    render();
  });

  // price filter
  priceApplyBtn.addEventListener("click", ()=>{
    const min = Number(priceMinInput.value || "");
    const max = Number(priceMaxInput.value || "");
    state.priceMin = isNaN(min) ? null : min;
    state.priceMax = isNaN(max) ? null : max;
    state.page = 1;
    render();
  });
  priceClearBtn.addEventListener("click", ()=>{
    priceMinInput.value = ""; priceMaxInput.value = "";
    state.priceMin = null; state.priceMax = null;
    render();
  });

  // clear all filters
  clearFiltersBtn.addEventListener("click", ()=>{
    state = {...state, q:"", brands:new Set(), sort:"default", page:1, priceMin:null, priceMax:null, onlyWishlist:false};
    searchInput.value = "";
    // [...] // will update brands below
    document.querySelectorAll("#brand-filters input").forEach(i=>i.checked=false);
    onlyWishlistCheckbox.checked = false;
    render();
  });

  // brand clicks (delegate)
  brandFiltersWrap.addEventListener("change", (e)=>{
    const ch = e.target;
    if(ch && ch.dataset && ch.dataset.brand !== undefined){
      const brand = ch.dataset.brand || ch.getAttribute("data-brand");
      if(ch.checked) state.brands.add(brand);
      else state.brands.delete(brand);
      state.page = 1;
      render();
    }
  });

  // wishlist open
  wishlistBtn.addEventListener("click", openWishlist);
  closeWishlistBtn.addEventListener("click", ()=>wishlistModal.classList.add("hidden"));
  clearWishlistBtn.addEventListener("click", ()=>{
    wishlist.clear(); localStorage.setItem("wishlist", JSON.stringify([])); renderWishlistList(); render();
  });

  // only wishlist checkbox
  onlyWishlistCheckbox.addEventListener("change", e=>{
    state.onlyWishlist = e.target.checked;
    state.page = 1;
    render();
  });

  // products grid click (delegate)
  productsGrid.addEventListener("click", (e)=>{
    const viewBtn = e.target.closest(".add-view");
    if(viewBtn){
      const id = Number(viewBtn.dataset.id);
      showDetail(id);
      return;
    }

    const wishBtn = e.target.closest(".wishlist-toggle");
    if(wishBtn){
      const id = Number(wishBtn.dataset.id);
      toggleWishlist(id);
      return;
    }

    const card = e.target.closest(".product-card");
    if(card && !e.target.matches("button, button *")) {
      const id = Number(card.dataset.id);
      showDetail(id);
    }
  });

  // delegated wishlist actions inside listing are handled above.

  // dark mode toggle
  const darkToggle = document.getElementById("dark-toggle");
  darkToggle.addEventListener("click", ()=>{
    document.body.classList.toggle("dark");
  });
}

/* ---------- Utility ---------- */
function renderStars(score){
  const r = Math.round(score);
  let out = "";
  for(let i=1;i<=5;i++){
    out += i <= r ? '★' : '☆';
  }
  return `<span style="color:var(--accent)">${out}</span>`;
}

function slug(s){ return String(s||"").toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,''); }
function escapeHtml(s){ return String(s||"").replace(/[&<>"']/g, (m)=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
function debounce(fn, wait=200){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), wait); } }

/* ---------- Bind product card buttons that were created after render ---------- */
function postRenderBind(){
  // ensure wishlist buttons bound (we already rely on event delegation)
}

/* ---------- Start rendering helpers ---------- */
function renderInitialBrandChecks(){
  // re-generate brand inputs with data-brand attributes (used earlier)
  const brandInputs = brandFiltersWrap.querySelectorAll("input");
  brandInputs.forEach(i=>{
    const b = i.getAttribute("id").replace("brand-","");
    const labelText = i.nextSibling ? i.nextSibling.textContent : "";
    i.setAttribute("data-brand", labelText.trim());
  });
}

/* ---------- Run once after initial DOM generation ---------- */
setTimeout(()=>{ renderInitialBrandChecks(); }, 40);

/* ---------- Fix: the clear filters handler used [...] placeholder earlier. Re-wire it correctly ---------- */
clearFiltersBtn.addEventListener("click", ()=>{
  state.q = "";
  state.brands = new Set();
  state.sort = "default";
  state.page = 1;
  state.priceMin = null;
  state.priceMax = null;
  state.onlyWishlist = false;

  searchInput.value = "";
  sortSelect.value = "default";
  perPageSelect.value = state.perPage;
  priceMinInput.value = "";
  priceMaxInput.value = "";
  onlyWishlistCheckbox.checked = false;
  document.querySelectorAll("#brand-filters input").forEach(i => i.checked = false);
  render();
});

/* ---------- Start ---------- */
function initial(){
  // ensure perPage select matches
  perPageSelect.value = state.perPage;
  // populate brand filters again if empty
  if(brandFiltersWrap.children.length === 0) renderBrandFilters();
  updateWishlistUI();
  render();
}
initial();
