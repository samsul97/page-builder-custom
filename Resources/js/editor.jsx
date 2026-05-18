import React from 'react';
import { createRoot } from 'react-dom/client';
import PageBuilderEditor from './components/PageBuilderEditor';

const container = document.getElementById('page-builder-editor');

if (container) {
    const payload = JSON.parse(container.dataset.pageBuilderEditor || '{}');

    createRoot(container).render(
        <React.StrictMode>
            <PageBuilderEditor payload={payload} />
        </React.StrictMode>
    );
}
