document.addEventListener("DOMContentLoaded", () => {
    fetch("data/products.json")
        .then(res => res.json())
        .then(data => displayFeatured(data.slice(0, 3))) // Top 3 only
        .catch(err => console.error("Error loading featured products:", err));
});

function displayFeatured(products) {
    const container = document.getElementById("featured-products");
    products.forEach(product => {
        const card = document.createElement("div");
        card.className = "product-card";

        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p class="price">Ksh ${product.price}</p>
            <a href="product.html?id=${product.id}" class="btn">View</a>
        `;

        container.appendChild(card);
    });
}
