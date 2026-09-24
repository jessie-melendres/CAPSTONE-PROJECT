(() => {
    function bindLogin() {
        const select = document.querySelector('#access_type');
        const studentFields = document.querySelector('#studentFields');
        const staffFields = document.querySelector('#staffFields');
        const tabs = document.querySelectorAll('[data-access]');
        if (!select || !studentFields || !staffFields || !tabs.length) return;
        const update = (value) => {
            select.value = value;
            const student = value === 'student';
            studentFields.hidden = !student;
            staffFields.hidden = student;
            tabs.forEach((tab) => {
                const active = tab.dataset.access === value;
                tab.classList.toggle('active', active);
                tab.setAttribute('aria-selected', String(active));
            });
        };
        tabs.forEach((tab) => tab.addEventListener('click', () => update(tab.dataset.access)));
        update(select.value || 'student');
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindLogin();
    });
})();
