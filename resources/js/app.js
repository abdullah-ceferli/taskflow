const initProgressiveEnhancements = () => {
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (! window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-counter]').forEach((input) => {
        const output = document.querySelector(input.dataset.counter);
        if (! output) return;
        const update = () => { output.textContent = `${input.value.length}/${input.maxLength || '∞'}`; };
        input.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('[data-attachment-input]').forEach((input) => {
        const output = document.querySelector(input.dataset.attachmentInput);
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (output) output.textContent = file ? `${file.name} (${Math.ceil(file.size / 1024)} KB)` : '';
        });
    });

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copy;
            if (! value) return;
            await navigator.clipboard.writeText(value);
            const original = button.textContent;
            button.textContent = 'Copied';
            window.setTimeout(() => { button.textContent = original; }, 1600);
        });
    });
};

document.addEventListener('DOMContentLoaded', initProgressiveEnhancements);
document.addEventListener('livewire:navigated', initProgressiveEnhancements);