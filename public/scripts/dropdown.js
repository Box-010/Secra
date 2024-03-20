(() => {
    const actionHandlers = {
        "edit-secret": (id) => {
            window.location.href = `./secrets/${id}/edit`;
        },
        "delete-secret": (id) => {
            const confirmed = confirm('你确定要删除这条秘语吗？');
            if (confirmed) {
                fetch(`./secrets/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                })
                    .then((res) => {
                        if (res.status === 200) {
                            // document.querySelectorAll(`.item-card[data-item-type="secrets"][data-item-id="${id}"]`).forEach((el) => {
                            //     el.remove();
                            // });
                            if ("refresh" in window) {
                                window.refresh();
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert('删除失败');
                        }
                    });
            }
        },
    }

    const initDropdown = () => {
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        dropdowns.forEach((dropdown) => {
            const {activator} = dropdown.dataset;
            const activatorElement = document.querySelector(activator);
            activatorElement.addEventListener('click', (ev) => {
                ev.preventDefault();
                const isActive = dropdown.classList.contains('dropdown-menu--active');
                dropdown.classList.toggle('dropdown-menu--active');
                if (!isActive) {
                    // calculate the position of the dropdown
                    const {top, left} = activatorElement.getBoundingClientRect();
                    dropdown.style.top = `${top + activatorElement.offsetHeight}px`;
                    dropdown.style.left = `${left}px`;
                    // close the dropdown when clicking outside
                    const closeDropdown = (ev) => {
                        console.log(ev);
                        if (!activatorElement.contains(ev.target) && !dropdown.contains(ev.target)) {
                            dropdown.classList.remove('dropdown-menu--active');
                            document.removeEventListener('click', closeDropdown);
                        }
                    };
                    document.addEventListener('click', closeDropdown);
                }
            });

            const items = dropdown.querySelectorAll('.dropdown-item');
            items.forEach((item) => {
                item.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    dropdown.classList.remove('dropdown-menu--active');
                    const {actionType, actionData} = item.dataset;
                    actionHandlers[actionType](actionData);
                });
            });
        });
    }

    window.initDropdown = initDropdown;
    window.addEventListener('DOMContentLoaded', initDropdown);
})();