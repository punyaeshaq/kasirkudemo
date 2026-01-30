import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const discount = ref({ type: 'percent', value: 0 });
    const paymentMethod = ref('cash');
    const taxRate = ref(0);

    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    });

    const discountAmount = computed(() => {
        if (discount.value.type === 'percent') {
            return subtotal.value * (discount.value.value / 100);
        }
        return discount.value.value;
    });

    const taxableAmount = computed(() => subtotal.value - discountAmount.value);

    const taxAmount = computed(() => taxableAmount.value * (taxRate.value / 100));

    const total = computed(() => taxableAmount.value + taxAmount.value);

    const itemCount = computed(() => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    const addItem = (product) => {
        const existing = items.value.find(item => item.id === product.id);
        if (existing) {
            // Stock validation is now handled in POS component
            existing.quantity++;
        } else {
            items.value.push({
                id: product.id,
                name: product.name,
                price: product.price,
                barcode: product.barcode,
                quantity: 1
            });
        }
    };

    const removeItem = (productId) => {
        const index = items.value.findIndex(item => item.id === productId);
        if (index !== -1) {
            items.value.splice(index, 1);
        }
    };

    const updateQuantity = (productId, quantity) => {
        const item = items.value.find(item => item.id === productId);
        if (item) {
            // Stock validation is now handled in POS component  
            item.quantity = Math.max(1, quantity);
        }
    };

    const incrementQuantity = (productId) => {
        const item = items.value.find(item => item.id === productId);
        if (item) {
            // Stock validation is now handled in POS component
            item.quantity++;
        }
    };

    const decrementQuantity = (productId) => {
        const item = items.value.find(item => item.id === productId);
        if (item && item.quantity > 1) {
            item.quantity--;
        }
    };

    const setDiscount = (type, value) => {
        discount.value = { type, value: parseFloat(value) || 0 };
    };

    const setPaymentMethod = (method) => {
        paymentMethod.value = method;
    };

    const setTaxRate = (rate) => {
        taxRate.value = parseFloat(rate) || 0;
    };

    const clear = () => {
        items.value = [];
        discount.value = { type: 'percent', value: 0 };
        paymentMethod.value = 'cash';
    };

    return {
        items,
        discount,
        paymentMethod,
        taxRate,
        subtotal,
        discountAmount,
        taxableAmount,
        taxAmount,
        total,
        itemCount,
        addItem,
        removeItem,
        updateQuantity,
        incrementQuantity,
        decrementQuantity,
        setDiscount,
        setPaymentMethod,
        setTaxRate,
        clear
    };
});
