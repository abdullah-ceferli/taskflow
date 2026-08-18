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

    document.querySelectorAll('[data-task-board]').forEach((board) => {
        let dragged = null;
        board.querySelectorAll('[data-board-card]').forEach((card) => {
            card.addEventListener('dragstart', () => { dragged = card; card.classList.add('opacity-50'); });
            card.addEventListener('dragend', () => { card.classList.remove('opacity-50'); dragged = null; });
        });
        board.querySelectorAll('[data-board-column]').forEach((column) => {
            column.addEventListener('dragover', (event) => event.preventDefault());
            column.addEventListener('drop', async (event) => {
                event.preventDefault();
                if (! dragged || ! dragged.dataset.statusUrl) return;
                const originalList = dragged.parentElement;
                const targetList = column.querySelector('[data-board-list]');
                const status = column.dataset.status;
                if (! targetList || originalList === targetList) return;
                targetList.append(dragged);
                const announcer = document.querySelector('[data-board-announcer]');
                try {
                    const response = await fetch(dragged.dataset.statusUrl, {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify({ status, expected_updated_at: dragged.dataset.updatedAt }),
                    });
                    const payload = await response.json();
                    if (! response.ok) throw new Error(payload.message || 'Status update failed.');
                    dragged.dataset.updatedAt = payload.data.updated_at;
                    dragged.querySelector('input[name="expected_updated_at"]')?.setAttribute('value', payload.data.updated_at);
                    if (announcer) { announcer.classList.remove('text-rose-600'); announcer.classList.add('text-emerald-700'); announcer.textContent = 'Task status updated.'; }
                } catch (error) {
                    originalList.append(dragged);
                    if (announcer) { announcer.classList.remove('text-emerald-700'); announcer.classList.add('text-rose-600'); announcer.textContent = error.message; }
                }
            });
        });
    });
};

document.addEventListener('DOMContentLoaded', initProgressiveEnhancements);
document.addEventListener('livewire:navigated', initProgressiveEnhancements);
