function toggleSubmenu(element) {
    const menuItem = element.closest('.sidebar-menu-item');
    const submenu = menuItem.querySelector('.sidebar-submenu');
    const arrow = element.querySelector('.submenu-arrow');
    
    // Toggle active class
    menuItem.classList.toggle('submenu-open');
    
    // Toggle submenu visibility
    if (menuItem.classList.contains('submenu-open')) {
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// Auto-expand submenu if current page is active
document.addEventListener('DOMContentLoaded', function() {
    const activeSubmenuItem = document.querySelector('.sidebar-menu-item.active');
    if (activeSubmenuItem) {
        activeSubmenuItem.classList.add('submenu-open');
        const submenu = activeSubmenuItem.querySelector('.sidebar-submenu');
        const arrow = activeSubmenuItem.querySelector('.submenu-arrow');
        if (submenu && arrow) {
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            arrow.style.transform = 'rotate(180deg)';
        }
    }
});