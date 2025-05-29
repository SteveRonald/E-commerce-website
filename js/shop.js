document.addEventListener("DOMContentLoaded", () => {
    fetch("data/products.json")
        .then(res => res.json())
        .then(products => displayProducts(products))
        .catch(err => console.error("Failed to load products:", err));
});

function displayProducts(products) {
    const productList = document.getElementById("product-list");
    products.forEach(product => {
        const card = document.createElement("div");
        card.className = "product-card";

        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>${product.description}</p>
            <p class="price">Ksh ${product.price}</p>
            <button onclick="addToCart(${product.id})">Add to Cart</button>
        `;

        productList.appendChild(card);
    });
}

function addToCart(productId) {
    alert("Product " + productId + " added to cart!"); // Placeholder action
}
