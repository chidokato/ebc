(() => {
    const list = document.getElementById('quick-items');
    const add = document.getElementById('add-quick-item');
    if (!list || !add) return;
    let nextIndex = Math.max(-1, ...Array.from(list.querySelectorAll('[name$="[name]"]'), input => Number(input.name.match(/\[(\d+)\]/)[1]))) + 1;
    const refresh = () => {
        const count = list.querySelectorAll('.quick-item-row').length;
        add.disabled = count >= 20;
        document.getElementById('quick-items-empty').hidden = count > 0;
    };
    add.addEventListener('click', () => {
        if (list.children.length >= 20) return;
        list.insertAdjacentHTML('beforeend', document.getElementById('quick-item-template').innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
        list.lastElementChild.querySelector('[name$="[name]"]').focus();
        refresh();
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('.remove-quick-item');
        if (!button) return;
        const row = button.closest('.quick-item-row');
        const preview = row.querySelector('.quick-icon-preview');
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        row.remove();
        refresh();
    });
    list.addEventListener('change', event => {
        if (event.target.type !== 'file') return;
        const preview = event.target.closest('.quick-item-row').querySelector('.quick-icon-preview');
        if (!preview.dataset.originalSrc) preview.dataset.originalSrc = preview.getAttribute('src') || '';
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        const file = event.target.files[0];
        const source = file ? URL.createObjectURL(file) : preview.dataset.originalSrc;
        preview.dataset.objectUrl = file ? source : '';
        if (source) preview.src = source;
        else preview.removeAttribute('src');
        preview.hidden = !source;
    });
    refresh();
})();
