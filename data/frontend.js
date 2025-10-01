const productsGrid = document.getElementById("productsGrid");
const cardTpl = document.getElementById("productCardTpl");

function renderProducts(list) {
  productsGrid.innerHTML = "";
  list.forEach((p) => {
    const node = cardTpl.content.cloneNode(true);
    node.querySelector(".p-img").src = p.image_url;
    node.querySelector(".p-name").textContent = p.name;
    node.querySelector(".p-spec").textContent = `${p.specs.ram} • ${p.specs.storage}`;
    node.querySelector(".p-price").textContent = `$${p.price}`;
    node.querySelector(".view-btn").addEventListener("click", () =>
      alert(`${p.name}\n\n${p.description}`)
    );
    productsGrid.appendChild(node);
  });
}

// fetch from backend
fetch("http://localhost:4000/api/products")
  .then((res) => res.json())
  .then((data) => {
    console.log("Fetched products:", data);
    renderProducts(data.items);
  })
  .catch((err) => console.error("Fetch error:", err));
