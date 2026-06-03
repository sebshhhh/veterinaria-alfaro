export function initWorkspaceAlertCenter() {
    const alertCenter = document.getElementById('workspaceAlertCenter');
    const alertTrigger = document.getElementById('workspaceAlertTrigger');
    const alertPanel = document.getElementById('workspaceAlertPanel');

    if (!alertCenter || !alertTrigger || !alertPanel) {
        return;
    }

    let isOpen = false;

    const closeAlertCenter = () => {
        isOpen = false;
        alertCenter.classList.remove('is-open');
        alertTrigger.classList.remove('workspace-alert-trigger--active');
        alertTrigger.setAttribute('aria-expanded', 'false');
        alertPanel.setAttribute('aria-hidden', 'true');
        alertPanel.hidden = true;
    };

    const openAlertCenter = () => {
        isOpen = true;
        alertCenter.classList.add('is-open');
        alertTrigger.classList.add('workspace-alert-trigger--active');
        alertTrigger.setAttribute('aria-expanded', 'true');
        alertPanel.hidden = false;
        alertPanel.setAttribute('aria-hidden', 'false');
    };

    const toggleAlertCenter = (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (isOpen) {
            closeAlertCenter();
        } else {
            openAlertCenter();
        }
    };

    alertTrigger.addEventListener('click', toggleAlertCenter);
    alertPanel.addEventListener('click', (event) => event.stopPropagation());

    document.addEventListener('click', (event) => {
        if (isOpen && !alertCenter.contains(event.target)) {
            closeAlertCenter();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            closeAlertCenter();
        }
    });

    window.closeWorkspaceAlertCenter = closeAlertCenter;
}
