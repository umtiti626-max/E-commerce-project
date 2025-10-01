let productsHTML = '';
products.forEach((product) => {
    productsHTML += ` 
        <div class="product-container">
            <div class="product-image-container">
            <img class="product-image"
                src="${product.image}">
            </div>

            <div class="product-name limit-text-to-2-lines">
                ${product.name}
            </div>
            <div class="added-to-cart">
            <img src="images/icons/checkmark.png">
            Added
            </div>

            <button class="add-to-cart-button button-primary 
            js-add-to-cart" data-product-name="${product.name}">
            Add to Cart
            </button>
        </div>
     `;
});

document.querySelector('.js-product-grid').innerHTML = productsHTML;
//adding an eventt listener to add items to cart
document.querySelectorAll('.js-add-to-cart').forEach((button)=>{
        button.addEventListener('click', ()=> {
            const productName = button.dataset.productName;
            let matchingItem;
            cart.forEach((item) => {
                //Check if the product is already in the cart
                if(productName === item.productName){
                    matchingItem = item;
                }
            })
                //if it is in the cart increase the quantity
            if(matchingItem){
                matchingItem.quantity += 1;
            }else{
                //if it's not in the cart add it to the cart
                cart.push({
                    productName: productName,
                    quantity: 1
                });
            }
         
        })
})
console.log(cart)