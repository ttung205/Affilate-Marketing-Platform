// Lưu trạng thái submenu vào localStorage
function saveSubmenuState() {
    const submenuStates = {};
    const submenuItems = document.querySelectorAll('.sidebar-menu-item');
    
    submenuItems.forEach((item, index) => {
        const isOpen = item.classList.contains('submenu-open');
        submenuStates[`submenu_${index}`] = isOpen;
    });
    
    localStorage.setItem('sidebarSubmenuStates', JSON.stringify(submenuStates));
}

// Khôi phục trạng thái submenu từ localStorage
function restoreSubmenuState() {
    const savedStates = localStorage.getItem('sidebarSubmenuStates');
    if (savedStates) {
        const submenuStates = JSON.parse(savedStates);
        const submenuItems = document.querySelectorAll('.sidebar-menu-item');
        
        submenuItems.forEach((item, index) => {
            const stateKey = `submenu_${index}`;
            if (submenuStates[stateKey]) {
                item.classList.add('submenu-open');
                const submenu = item.querySelector('.sidebar-submenu');
                const arrow = item.querySelector('.submenu-arrow');
                if (submenu && arrow) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    arrow.style.transform = 'rotate(180deg)';
                    
                    // Áp dụng hiệu ứng đơn giản cho các items
                    const submenuLinks = submenu.querySelectorAll('a');
                    submenuLinks.forEach((link, linkIndex) => {
                        setTimeout(() => {
                            link.style.transform = 'translateX(0)';
                            link.style.opacity = '1';
                        }, linkIndex * 20); // Delay ngắn hơn khi khôi phục
                    });
                }
            }
        });
    }
}

function toggleSubmenu(element) {
    const menuItem = element.closest('.sidebar-menu-item');
    const submenu = menuItem.querySelector('.sidebar-submenu');
    const arrow = element.querySelector('.submenu-arrow');
    
    // Toggle active class
    menuItem.classList.toggle('submenu-open');
    
    // Toggle submenu visibility với hiệu ứng mượt mà
    if (menuItem.classList.contains('submenu-open')) {
        // Mở submenu
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        arrow.style.transform = 'rotate(180deg)';
        
        // Hiệu ứng đơn giản cho các items
        const submenuItems = submenu.querySelectorAll('a');
        submenuItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.transform = 'translateX(0)';
                item.style.opacity = '1';
            }, index * 30); // Delay ngắn hơn
        });
    } else {
        // Đóng submenu
        submenu.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
        
        // Reset hiệu ứng cho các items
        const submenuItems = submenu.querySelectorAll('a');
        submenuItems.forEach(item => {
            item.style.transform = 'translateX(-10px)';
            item.style.opacity = '0';
        });
    }
    
    // Lưu trạng thái sau khi toggle
    saveSubmenuState();
}

// Khởi tạo sidebar khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    // Khôi phục trạng thái đã lưu
    restoreSubmenuState();
    
    // Auto-expand submenu nếu trang hiện tại đang active
    const activeSubmenuItem = document.querySelector('.sidebar-menu-item.active');
    if (activeSubmenuItem && !activeSubmenuItem.classList.contains('submenu-open')) {
        activeSubmenuItem.classList.add('submenu-open');
        const submenu = activeSubmenuItem.querySelector('.sidebar-submenu');
        const arrow = activeSubmenuItem.querySelector('.submenu-arrow');
        if (submenu && arrow) {
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            arrow.style.transform = 'rotate(180deg)';
        }
        // Lưu trạng thái sau khi auto-expand
        saveSubmenuState();
    }
});