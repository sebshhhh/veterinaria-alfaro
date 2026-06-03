export function mountWorkspaceModals() {
    document.querySelectorAll('div[id$="Modal"].fixed').forEach((modal) => {
        if (modal.dataset.portalMounted === 'true') {
            return;
        }

        document.body.appendChild(modal);
        modal.dataset.portalMounted = 'true';
        modal.classList.add('workspace-modal');

        const shell = modal.firstElementChild;
        const card = shell?.firstElementChild;

        if (shell) {
            shell.classList.add('workspace-modal-shell');
        }

        if (card) {
            card.classList.add('workspace-modal-surface');
        }
    });
}
