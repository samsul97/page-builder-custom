import React from 'react';
import { createRoot } from 'react-dom/client';
import PageBuilderShell from './components/PageBuilderShell';

const container = document.getElementById('page-builder-app');

if (container) {
    const payload = JSON.parse(container.dataset.pageBuilder || '{}');

    createRoot(container).render(
        <React.StrictMode>
            <PageBuilderShell payload={payload} />
        </React.StrictMode>
    );
}
