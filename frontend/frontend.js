// Dummy product data
const dummyProducts = [
  {
    id: 1,
    name: "iPhone 15 Pro",
    brand: "Apple",
    description: "Flagship Apple phone with A17 chip",
    price: 1199,
    image_url: "https://via.placeholder.com/150?text=iPhone+15+Pro",
    specs: { ram: "8GB", storage: "256GB" }
  },
  {
    id: 2,
    name: "Galaxy S24 Ultra",
    brand: "Samsung",
    description: "Samsung's latest flagship",
    price: 1299,
    image_url: "https://via.placeholder.com/150?text=Galaxy+S24+Ultra",
    specs: { ram: "12GB", storage: "512GB" }
  },
  {
    id: 3,
    name: "Xiaomi 13 Pro",
    brand: "Xiaomi",
    description: "Affordable high-end phone",
    price: 899,
    image_url: "https://via.placeholder.com/150?text=Xiaomi+13+Pro",
    specs: { ram: "12GB", storage: "256GB" }
  }
];

const productsGrid = document.getElementById("productsGrid");
const cardTpl = document.getElementById("productCardTpl");
let activeCategory = "All";

// Render products
function renderProducts(list) {
  productsGrid.innerHTML = "";
  list.forEach((p) => {
    const node = cardTpl.content.cloneNode(true);
    node.querySelector(".p-img").src = p.image_url;
    node.querySelector(".p-name").textContent = p.name;
    node.querySelector(".p-spec").textContent =
      `${p.specs.ram} • ${p.specs.storage}`;
    node.querySelector(".p-price").textContent = `$${p.price}`;
    node.querySelector(".view-btn").addEventListener("click", () =>
      alert(`${p.name}\\n\\n${p.description}`)
    );
    productsGrid.appendChild(node);
  });
}

// Filter by brand
document.querySelectorAll(".cat-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    activeCategory = btn.dataset.cat;
    if (activeCategory === "All") renderProducts(dummyProducts);
    else {
      const filtered = dummyProducts.filter((p) => p.brand === activeCategory);
      renderProducts(filtered);
    }
  });
});

// Init
renderProducts(dummyProducts);
