<script defer>
window.personalData = (function() {
    const STORAGE_KEY = 'aero-personal-data';
    const SECONDS_UNTIL_DATA_IS_STALE = 120;

    return {
        fetch: function() {
            return fetch(`${window.location.origin}/personaldata`)
                .then(r => r.json())
                .then(data => {
                    console.log('[PERSONAL_DATA] Fetched:', data);
                    localStorage.setItem(personalData, JSON.stringify(data));
                    return data;
                })
        },
        loadFromLocalStorage: function() {
            try {
                return JSON.parse(localStorage.getItem(personalData));
            } catch (e) {
                return null;
            }
        },
        get: function() {
            const localData = this.loadFromLocalStorage();
            if (localData?.time > (Math.round((new Date()).getTime() / 1000) - SECONDS_UNTIL_DATA_IS_STALE)) {
                console.log('[PERSONAL_DATA] Loaded from Local Storage', localData);
                return new Promise((resolve) => { resolve(localData); });
            }
            return this.fetch();
        },
        refresh: function() {
            localStorage.removeItem(personalData);
            this.populate();
        },
        populate: function() {
            const self = this;
            return this.get()
                .then(data => {
                    self.updateDOM(data);
                    return data;
                });
        },
        updateDOM: function(data) {
            this.updateCSRF(data.csrf_token);
            this.updateCartQty(data.cart_count);
            console.log('[PERSONAL_DATA] Updated DOM:', data);
        },
        updateCSRF: function(token) {
            const csrftoken = document.querySelector('meta[name="csrf-token"]');
            csrftoken.setAttribute('content', token);
        },
        updateCartQty: function(qty) {
            const cartIcon = document.querySelector('[data-cart-count]');
            cartIcon.setAttribute('data-cart-count', qty);
            cartIcon.textContent = qty;
        }
    }
})();

setTimeout(function() {
    window.personalData.populate();
    window.aero.events.$on('product.added-to-cart', window.personalData.refresh);
    window.aero.events.$on('product.quantity-updated', window.personalData.refresh);
}, 100);
</script>
