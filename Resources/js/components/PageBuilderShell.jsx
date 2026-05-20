import React from 'react';

const milestoneItems = [
    {
        title: 'Start Custom',
        description: 'Technical path for building core layouts, chrome layouts, and manual page composition.',
        status: 'Available',
    },
    {
        title: 'Start From Template',
        description: 'Planned client-friendly path for choosing a preset, then editing content and limited controls.',
        status: 'Planned',
    },
    {
        title: 'Plugins / Theme Library',
        description: 'Planned categorized library for theme and plugin assets, inspired by KreatifCMS but adapted to Rawdee.',
        status: 'Planned',
    },
];

const flowItems = [
    {
        title: '1. Start Custom',
        description: 'Use the current engine workspace for manual page composition, low-level layout records, and technical refinement.',
        status: 'Available',
    },
    {
        title: '2. Start From Template',
        description: 'Planned next layer where users choose a built-in preset first, then edit content and safe controls.',
        status: 'Next',
    },
    {
        title: '3. Plugins / Theme',
        description: 'Later categorized library for theme families and plugin/block packs after the first built-in preset is stable.',
        status: 'Later',
    },
];

export default function PageBuilderShell({ payload }) {
    const sections = payload.sections || [];
    const current = payload.current || {};

    return (
        <div className="row g-4">
            <div className="col-12">
                <div className="card border-0 shadow-sm">
                    <div className="card-body p-4 p-lg-5">
                        <div className="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-4">
                            <div>
                                <span className="badge bg-dark-subtle text-dark text-uppercase mb-3">
                                    Isolated Module
                                </span>
                                <h2 className="mb-2">{current.title || 'Page Builder'}</h2>
                                <p className="text-muted mb-0" style={{ maxWidth: '760px' }}>
                                    {current.description || 'Initial shell for the reusable page builder module.'}
                                </p>
                            </div>
                            <div className="rounded-3 border bg-light px-3 py-2 small text-muted">
                                Shared engine first. Template workflow next.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="col-xl-8">
                <div className="card border-0 shadow-sm">
                    <div className="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 className="mb-1">Page Builder Workspace</h5>
                        <p className="text-muted mb-0">This workspace currently holds the shared engine. The final product will branch into Start Custom and Start From Template.</p>
                    </div>
                    <div className="card-body px-4 pb-4">
                        <div className="row g-3">
                            {sections.map((section) => {
                                const isActive = current.route === section.route;

                                return (
                                    <div className="col-md-6" key={section.key}>
                                        <a
                                            href={section.url}
                                            className={`card h-100 text-decoration-none border shadow-none ${
                                                isActive ? 'border-primary bg-primary-subtle' : 'border-light-subtle'
                                            }`}
                                        >
                                            <div className="card-body">
                                                <div className="d-flex justify-content-between align-items-start gap-3">
                                                    <div>
                                                        <h6 className="mb-2 text-dark">{section.title}</h6>
                                                        <p className="text-muted mb-0 small">{section.description}</p>
                                                    </div>
                                                    <span className={`badge ${isActive ? 'bg-primary' : 'bg-light text-dark'}`}>
                                                        {isActive ? 'Active' : 'Open'}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>

            <div className="col-xl-4">
                <div className="card border-0 shadow-sm mb-4">
                    <div className="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 className="mb-1">Product Flow</h5>
                        <p className="text-muted mb-0">The intended entry flow should be easy to explain before users dive into technical forms.</p>
                    </div>
                    <div className="card-body px-4 pb-4">
                        <div className="vstack gap-3">
                            {flowItems.map((item) => (
                                <div className="border rounded-3 p-3" key={item.title}>
                                    <div className="d-flex justify-content-between align-items-center gap-3 mb-2">
                                        <h6 className="mb-0">{item.title}</h6>
                                        <span className="badge bg-dark-subtle text-dark">{item.status}</span>
                                    </div>
                                    <p className="text-muted small mb-0">{item.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="card border-0 shadow-sm">
                    <div className="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 className="mb-1">Product Direction</h5>
                        <p className="text-muted mb-0">This helps explain where the current custom path ends and where the future template path begins.</p>
                    </div>
                    <div className="card-body px-4 pb-4">
                        <div className="alert alert-light border mb-3">
                            <div className="fw-semibold mb-1">First built-in template baseline</div>
                            <p className="small text-muted mb-0">
                                Use the current public website as the first internal preset reference. This should be a controlled translation into builder records, not a blind Blade import.
                            </p>
                        </div>
                        <div className="vstack gap-3">
                            {milestoneItems.map((item) => (
                                <div className="border rounded-3 p-3" key={item.title}>
                                    <div className="d-flex justify-content-between align-items-center gap-3 mb-2">
                                        <h6 className="mb-0">{item.title}</h6>
                                        <span className="badge bg-secondary-subtle text-secondary">{item.status}</span>
                                    </div>
                                    <p className="text-muted small mb-0">{item.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
