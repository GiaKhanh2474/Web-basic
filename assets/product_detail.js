function changeQuantity(value) {

        const input = document.getElementById('quantity');

        if (input.disabled) return;

        const min = parseInt(input.min);
        const max = parseInt(input.max);

        let current = parseInt(input.value) || 1;

        current += value;

        if (current < min) {
            current = min;
        }

        if (current > max) {
            current = max;
        }

        input.value = current;
    }