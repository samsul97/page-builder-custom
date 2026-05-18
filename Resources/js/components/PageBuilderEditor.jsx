import React, { useEffect, useMemo, useState } from 'react';
import MediaPickerModal from './MediaPickerModal';

const BLOCK_LIBRARY = [
    { type: 'hero', label: 'Hero', description: 'Large intro section with headline and CTA.' },
    { type: 'text', label: 'Text', description: 'Rich text section for narrative content.' },
    { type: 'image', label: 'Image', description: 'Single image block with optional caption.' },
    { type: 'video', label: 'Video', description: 'Single featured video for explainers, campaigns, or ambience reels.' },
    { type: 'video_grid', label: 'Video Grid', description: 'Grid of multiple video cards for tutorials, reels, or featured clips.' },
    { type: 'slideshow', label: 'Slideshow', description: 'Image slideshow with captions and optional CTA per slide.' },
    { type: 'photogrid', label: 'Photo Grid', description: 'Richer multi-image grid with title and caption per photo.' },
    { type: 'feature_grid', label: 'Feature Grid', description: 'Grid of selling points or package highlights.' },
    { type: 'gallery', label: 'Gallery', description: 'Image gallery for destination or room highlights.' },
    { type: 'faq', label: 'FAQ', description: 'Frequently asked questions with answers.' },
    { type: 'social_media', label: 'Social Media', description: 'Social media icon links for profile discovery and trust.' },
    { type: 'timeline', label: 'Timeline', description: 'Milestones, journey steps, or process stages in timeline form.' },
    { type: 'dynamic_collection', label: 'Dynamic Collection', description: 'Render published entries from a selected content type.' },
    { type: 'form', label: 'Form', description: 'Link a page builder section to an existing Rawdee form.' },
    { type: 'cta', label: 'CTA', description: 'Call-to-action card with button.' },
    { type: 'spacer', label: 'Spacer', description: 'Vertical spacing between sections.' },
];

const DEFAULT_BLOCKS = {
    hero: () => ({
        type: 'hero',
        data: {
            eyebrow: 'Rawdee Experience',
            title: 'Build a focused landing page visually',
            subtitle: 'This is the first visual builder slice inside rawdee-glampings.',
            button_label: 'Book Now',
            button_url: '#contact',
            align: 'left',
        },
    }),
    text: () => ({
        type: 'text',
        data: {
            title: 'Section title',
            content: 'Write a strong supporting paragraph for this landing page section.',
            align: 'left',
        },
    }),
    image: () => ({
        type: 'image',
        data: {
            path: '',
            url: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
            alt: 'Landing page image',
            caption: 'Optional image caption',
        },
    }),
    video: () => ({
        type: 'video',
        data: {
            title: 'Featured Video',
            subtitle: 'Use this block for explainers, ambience reels, or campaign videos.',
            url: '',
            poster_path: '',
            poster_url: '',
            caption: '',
        },
    }),
    video_grid: () => ({
        type: 'video_grid',
        data: {
            title: 'Video Grid',
            subtitle: 'Show several videos together in one landing page section.',
            items: [
                {
                    id: generateId(),
                    title: 'Video 1',
                    description: 'Short description for this video.',
                    url: '',
                    poster_path: '',
                    poster_url: '',
                },
                {
                    id: generateId(),
                    title: 'Video 2',
                    description: 'Add another supporting video card.',
                    url: '',
                    poster_path: '',
                    poster_url: '',
                },
            ],
        },
    }),
    slideshow: () => ({
        type: 'slideshow',
        data: {
            title: 'Slideshow',
            subtitle: 'Rotate the strongest visuals in one focused section.',
            slides: [
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1400&q=80',
                    alt: 'Slide image 1',
                    title: 'Slide Title',
                    description: 'Short supporting caption for this slide.',
                    button_label: '',
                    button_url: '',
                },
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1400&q=80',
                    alt: 'Slide image 2',
                    title: 'Another Slide',
                    description: 'Use this for second highlight or alternate offer.',
                    button_label: '',
                    button_url: '',
                },
            ],
        },
    }),
    photogrid: () => ({
        type: 'photogrid',
        data: {
            title: 'Photo Grid',
            subtitle: 'A richer image grid for moodboards, room types, or portfolio scenes.',
            items: [
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1400&q=80',
                    alt: 'Photo grid image 1',
                    title: 'Photo One',
                    caption: 'Optional supporting caption.',
                },
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1400&q=80',
                    alt: 'Photo grid image 2',
                    title: 'Photo Two',
                    caption: 'Optional supporting caption.',
                },
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1400&q=80',
                    alt: 'Photo grid image 3',
                    title: 'Photo Three',
                    caption: 'Optional supporting caption.',
                },
            ],
        },
    }),
    feature_grid: () => ({
        type: 'feature_grid',
        data: {
            title: 'Why guests choose this stay',
            subtitle: 'Highlight the strongest reasons to book this experience.',
            items: [
                {
                    id: generateId(),
                    title: 'Private Scenic Views',
                    description: 'Wake up with direct access to mountain and lake scenery.',
                },
                {
                    id: generateId(),
                    title: 'Comfortable Facilities',
                    description: 'Well-prepared amenities for couples, families, or group trips.',
                },
                {
                    id: generateId(),
                    title: 'Easy Booking Flow',
                    description: 'Drive visitors straight into WhatsApp or the booking form.',
                },
            ],
        },
    }),
    gallery: () => ({
        type: 'gallery',
        data: {
            title: 'Gallery',
            subtitle: 'Show the visual atmosphere before visitors contact you.',
            images: [
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
                    alt: 'Gallery image 1',
                    caption: '',
                },
                {
                    id: generateId(),
                    path: '',
                    url: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                    alt: 'Gallery image 2',
                    caption: '',
                },
            ],
        },
    }),
    faq: () => ({
        type: 'faq',
        data: {
            title: 'Frequently Asked Questions',
            subtitle: 'Address common questions early and reduce booking friction.',
            items: [
                {
                    id: generateId(),
                    question: 'How do I reserve this glamping package?',
                    answer: 'You can reserve it directly through the CTA button or WhatsApp contact listed on the page.',
                },
                {
                    id: generateId(),
                    question: 'Is breakfast included?',
                    answer: 'Use this answer area to explain package details, inclusions, or upgrade options.',
                },
            ],
        },
    }),
    social_media: () => ({
        type: 'social_media',
        data: {
            title: 'Follow Rawdee',
            subtitle: 'Direct visitors to your social channels.',
            items: [
                { id: generateId(), platform: 'Instagram', label: 'Instagram', url: 'https://instagram.com/' },
                { id: generateId(), platform: 'TikTok', label: 'TikTok', url: 'https://www.tiktok.com/' },
                { id: generateId(), platform: 'YouTube', label: 'YouTube', url: 'https://www.youtube.com/' },
            ],
        },
    }),
    timeline: () => ({
        type: 'timeline',
        data: {
            title: 'Journey Timeline',
            subtitle: 'Show the story, process, or milestones behind this offering.',
            items: [
                { id: generateId(), date: 'Step 1', title: 'Discovery', description: 'Introduce the first important milestone or process step.' },
                { id: generateId(), date: 'Step 2', title: 'Experience', description: 'Explain what visitors, guests, or customers will go through next.' },
                { id: generateId(), date: 'Step 3', title: 'Conversion', description: 'Close with the result or next action you want people to take.' },
            ],
        },
    }),
    dynamic_collection: () => ({
        type: 'dynamic_collection',
        data: {
            title: 'Dynamic Collection',
            subtitle: 'Published entries from a selected content type will render here.',
            content_type_id: '',
            limit: 3,
        },
    }),
    form: () => ({
        type: 'form',
        data: {
            title: 'Fill This Form',
            content: 'Use this block to direct visitors into an existing form flow managed by Rawdee.',
            form_id: '',
            button_label: 'Open Form',
        },
    }),
    cta: () => ({
        type: 'cta',
        data: {
            title: 'Ready to convert visitors?',
            content: 'Use this section for package promos, WhatsApp CTA, or booking nudges.',
            button_label: 'Contact Us',
            button_url: '#contact',
        },
    }),
    spacer: () => ({
        type: 'spacer',
        data: {
            height: 80,
        },
    }),
};

function generateId() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }

    return `pb_${Math.random().toString(36).slice(2, 11)}`;
}

function normalizeBlocks(input) {
    if (!Array.isArray(input)) {
        return [];
    }

    return input.map((block) => ({
        id: block.id || generateId(),
        type: block.type || 'text',
        data: block.data && typeof block.data === 'object' ? block.data : {},
    }));
}

function parseBlocks(value) {
    if (!value || typeof value !== 'string') {
        return [];
    }

    try {
        const parsed = JSON.parse(value);
        return normalizeBlocks(parsed);
    } catch {
        return [];
    }
}

function cloneBlocks(blocks) {
    if (!Array.isArray(blocks)) {
        return [];
    }

    return blocks.map((block) => ({
        ...block,
        id: generateId(),
    }));
}

function resolveImageSource(data) {
    return data?.url || '';
}

function getVideoEmbedUrl(url) {
    if (!url) {
        return '';
    }

    try {
        const parsed = new URL(url);
        const host = parsed.hostname.toLowerCase();

        if (host.includes('youtube.com')) {
            const id = parsed.searchParams.get('v');
            return id ? `https://www.youtube.com/embed/${id}` : '';
        }

        if (host === 'youtu.be') {
            const id = parsed.pathname.replaceAll('/', '');
            return id ? `https://www.youtube.com/embed/${id}` : '';
        }

        if (host.includes('vimeo.com')) {
            const id = parsed.pathname.split('/').filter(Boolean).pop();
            return id ? `https://player.vimeo.com/video/${id}` : '';
        }
    } catch {
        return '';
    }

    return '';
}

function createFeatureItem() {
    return {
        id: generateId(),
        title: 'Feature title',
        description: 'Short explanation for this feature card.',
    };
}

function createGalleryImage() {
    return {
        id: generateId(),
        path: '',
        url: '',
        alt: '',
        caption: '',
    };
}

function createSlideItem() {
    return {
        id: generateId(),
        path: '',
        url: '',
        alt: '',
        title: 'Slide Title',
        description: 'Slide description',
        button_label: '',
        button_url: '',
    };
}

function createVideoGridItem() {
    return {
        id: generateId(),
        title: 'Video Title',
        description: 'Short description for this video.',
        url: '',
        poster_path: '',
        poster_url: '',
    };
}

function createPhotoGridItem() {
    return {
        id: generateId(),
        path: '',
        url: '',
        alt: '',
        title: 'Photo Title',
        caption: 'Photo caption',
    };
}

function createFaqItem() {
    return {
        id: generateId(),
        question: 'Question',
        answer: 'Answer',
    };
}

function createSocialMediaItem() {
    return {
        id: generateId(),
        platform: 'Instagram',
        label: 'Instagram',
        url: 'https://instagram.com/',
    };
}

function createTimelineItem() {
    return {
        id: generateId(),
        date: 'Step',
        title: 'Timeline Title',
        description: 'Describe this step or milestone.',
    };
}

function getBlockName(type) {
    return BLOCK_LIBRARY.find((item) => item.type === type)?.label || type;
}

function moveItem(items, index, direction) {
    const nextIndex = index + direction;

    if (nextIndex < 0 || nextIndex >= items.length) {
        return items;
    }

    const next = [...items];
    const [item] = next.splice(index, 1);
    next.splice(nextIndex, 0, item);
    return next;
}

function PreviewBlock({ block }) {
    const { type, data = {} } = block;

    switch (type) {
        case 'hero':
            return (
                <div className="rounded-4 p-4 p-lg-5 text-white" style={{ background: 'linear-gradient(135deg, #0f172a, #2563eb)' }}>
                    <div className="text-uppercase small fw-semibold opacity-75 mb-2">{data.eyebrow || 'Eyebrow'}</div>
                    <h2 className="mb-3">{data.title || 'Hero Title'}</h2>
                    <p className="mb-4 opacity-75">{data.subtitle || 'Hero subtitle goes here.'}</p>
                    <span className="btn btn-light btn-sm">{data.button_label || 'Call To Action'}</span>
                </div>
            );
        case 'text':
            return (
                <div className="p-4 border rounded-4 bg-light">
                    <h4 className="mb-3">{data.title || 'Text Section'}</h4>
                    <p className="mb-0 text-muted" style={{ whiteSpace: 'pre-wrap' }}>
                        {data.content || 'Add your supporting content here.'}
                    </p>
                </div>
            );
        case 'image':
            return (
                <div className="border rounded-4 overflow-hidden bg-white">
                    {resolveImageSource(data) ? (
                        <img src={resolveImageSource(data)} alt={data.alt || 'Preview'} className="img-fluid w-100" style={{ maxHeight: '320px', objectFit: 'cover' }} />
                    ) : (
                        <div className="p-5 text-center text-muted">Image URL not set</div>
                    )}
                    {data.caption ? <div className="px-3 py-2 small text-muted">{data.caption}</div> : null}
                </div>
            );
        case 'video': {
            const embedUrl = getVideoEmbedUrl(data.url || '');
            const posterSource = data.poster_url || '';

            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Featured Video'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="rounded-4 overflow-hidden bg-dark">
                        {embedUrl ? (
                            <div className="ratio ratio-16x9">
                                <iframe src={embedUrl} title={data.title || 'Embedded video'} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowFullScreen />
                            </div>
                        ) : data.url ? (
                            <video className="w-100" controls poster={posterSource || undefined} style={{ maxHeight: '360px', objectFit: 'cover' }}>
                                <source src={data.url} />
                            </video>
                        ) : (
                            <div className="ratio ratio-16x9 d-flex align-items-center justify-content-center text-white-50">
                                Video URL not set
                            </div>
                        )}
                    </div>
                    {data.caption ? <div className="small text-muted mt-3">{data.caption}</div> : null}
                </div>
            );
        }
        case 'video_grid':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Video Grid'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="row g-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index) => (
                            <div key={item.id || index} className="col-md-6">
                                <div className="border rounded-4 overflow-hidden bg-light h-100">
                                    {item.poster_url ? (
                                        <img src={item.poster_url} alt={item.title || 'Video poster'} className="img-fluid w-100" style={{ height: '180px', objectFit: 'cover' }} />
                                    ) : (
                                        <div className="ratio ratio-16x9 d-flex align-items-center justify-content-center text-muted">Poster</div>
                                    )}
                                    <div className="p-3">
                                        <div className="fw-semibold">{item.title || `Video ${index + 1}`}</div>
                                        {item.description ? <div className="small text-muted mt-1">{item.description}</div> : null}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'slideshow':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Slideshow'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="vstack gap-3">
                        {(Array.isArray(data.slides) ? data.slides : []).map((slide, index) => (
                            <div key={slide.id || index} className="border rounded-4 overflow-hidden bg-light">
                                {resolveImageSource(slide) ? (
                                    <img src={resolveImageSource(slide)} alt={slide.alt || 'Slide preview'} className="img-fluid w-100" style={{ height: '220px', objectFit: 'cover' }} />
                                ) : (
                                    <div className="p-5 text-center text-muted">No slide image selected</div>
                                )}
                                <div className="p-3">
                                    <div className="fw-semibold">{slide.title || `Slide ${index + 1}`}</div>
                                    {slide.description ? <div className="small text-muted mt-1">{slide.description}</div> : null}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'photogrid':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Photo Grid'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="row g-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index) => (
                            <div key={item.id || index} className="col-md-6">
                                <div className="border rounded-4 overflow-hidden bg-light h-100">
                                    {resolveImageSource(item) ? (
                                        <img src={resolveImageSource(item)} alt={item.alt || 'Photo preview'} className="img-fluid w-100" style={{ height: '220px', objectFit: 'cover' }} />
                                    ) : (
                                        <div className="p-5 text-center text-muted">No image selected</div>
                                    )}
                                    <div className="p-3">
                                        <div className="fw-semibold">{item.title || `Photo ${index + 1}`}</div>
                                        {item.caption ? <div className="small text-muted mt-1">{item.caption}</div> : null}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'feature_grid':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Feature Grid'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="row g-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item) => (
                            <div key={item.id || item.title} className="col-md-6">
                                <div className="h-100 border rounded-4 p-3 bg-light">
                                    <div className="fw-semibold mb-2">{item.title || 'Feature title'}</div>
                                    <div className="small text-muted">{item.description || 'Feature description'}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'gallery':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Gallery'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="row g-3">
                        {(Array.isArray(data.images) ? data.images : []).map((image) => (
                            <div key={image.id || image.url} className="col-md-6">
                                <div className="border rounded-4 overflow-hidden bg-light h-100">
                                    {resolveImageSource(image) ? (
                                        <img
                                            src={resolveImageSource(image)}
                                            alt={image.alt || 'Gallery preview'}
                                            className="img-fluid w-100"
                                            style={{ height: '180px', objectFit: 'cover' }}
                                        />
                                    ) : (
                                        <div className="p-5 text-center text-muted">No image selected</div>
                                    )}
                                    {image.caption ? <div className="px-3 py-2 small text-muted">{image.caption}</div> : null}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'faq':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'FAQ'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item) => (
                            <div key={item.id || item.question} className="border rounded-4 p-3 bg-light">
                                <div className="fw-semibold mb-2">{item.question || 'Question'}</div>
                                <div className="small text-muted">{item.answer || 'Answer'}</div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'social_media':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Social Media'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="d-flex flex-wrap gap-2">
                        {(Array.isArray(data.items) ? data.items : []).map((item) => (
                            <span key={item.id || item.url} className="badge rounded-pill bg-light text-dark border px-3 py-2">
                                {item.label || item.platform || 'Social'}
                            </span>
                        ))}
                    </div>
                </div>
            );
        case 'timeline':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Timeline'}</h4>
                    {data.subtitle ? <p className="text-muted mb-4">{data.subtitle}</p> : null}
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index) => (
                            <div key={item.id || `${item.date}-${index}`} className="border-start border-3 ps-3">
                                <div className="small text-muted mb-1">{item.date || `Step ${index + 1}`}</div>
                                <div className="fw-semibold mb-1">{item.title || 'Timeline title'}</div>
                                <div className="small text-muted">{item.description || 'Timeline description'}</div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'dynamic_collection': {
            const contentTypes = Array.isArray(window.__pageBuilderContentTypes) ? window.__pageBuilderContentTypes : [];
            const selectedType = contentTypes.find((item) => String(item.id) === String(data.content_type_id));

            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Dynamic Collection'}</h4>
                    {data.subtitle ? <p className="text-muted mb-3">{data.subtitle}</p> : null}
                    <div className="small text-muted mb-2">
                        Content Type: <strong>{selectedType?.name || 'Not selected yet'}</strong>
                    </div>
                    <div className="small text-muted">
                        Limit: {Number(data.limit || 3)} item(s)
                    </div>
                </div>
            );
        }
        case 'form': {
            const forms = Array.isArray(window.__pageBuilderForms) ? window.__pageBuilderForms : [];
            const selectedForm = forms.find((item) => String(item.id) === String(data.form_id));

            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'Form Block'}</h4>
                    {data.content ? <p className="text-muted mb-3">{data.content}</p> : null}
                    <div className="small text-muted mb-3">
                        Selected Form: <strong>{selectedForm?.name || 'Not selected yet'}</strong>
                    </div>
                    <span className="btn btn-primary btn-sm">{data.button_label || 'Open Form'}</span>
                </div>
            );
        }
        case 'cta':
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <h4 className="mb-2">{data.title || 'CTA Title'}</h4>
                    <p className="text-muted mb-3">{data.content || 'CTA copy goes here.'}</p>
                    <span className="btn btn-primary btn-sm">{data.button_label || 'Button'}</span>
                </div>
            );
        case 'spacer':
            return (
                <div className="rounded-4 border border-dashed bg-light d-flex align-items-center justify-content-center text-muted small" style={{ height: `${Number(data.height || 80)}px` }}>
                    Spacer {Number(data.height || 80)}px
                </div>
            );
        default:
            return (
                <div className="border rounded-4 p-4 bg-white">
                    <div className="fw-semibold mb-2">{getBlockName(type)}</div>
                    <pre className="mb-0 small text-muted">{JSON.stringify(data, null, 2)}</pre>
                </div>
            );
    }
}

function BlockFields({ block, onChange, payload }) {
    const data = block.data || {};
    const contentTypes = Array.isArray(payload.contentTypes) ? payload.contentTypes : [];
    const forms = Array.isArray(payload.forms) ? payload.forms : [];
    const [mediaPickerOpen, setMediaPickerOpen] = useState(false);
    const [mediaPickerTarget, setMediaPickerTarget] = useState(null);

    const update = (field, value) => {
        onChange({
            ...block,
            data: {
                ...data,
                [field]: value,
            },
        });
    };

    const updateArrayItem = (field, itemId, nextValues) => {
        const currentItems = Array.isArray(data[field]) ? data[field] : [];

        update(
            field,
            currentItems.map((item) => (item.id === itemId ? { ...item, ...nextValues } : item))
        );
    };

    const addArrayItem = (field, factory) => {
        const currentItems = Array.isArray(data[field]) ? data[field] : [];
        update(field, [...currentItems, factory()]);
    };

    const deleteArrayItem = (field, itemId) => {
        const currentItems = Array.isArray(data[field]) ? data[field] : [];
        update(field, currentItems.filter((item) => item.id !== itemId));
    };

    const moveArrayItem = (field, itemId, direction) => {
        const currentItems = Array.isArray(data[field]) ? data[field] : [];
        const index = currentItems.findIndex((item) => item.id === itemId);
        update(field, moveItem(currentItems, index, direction));
    };

    const handleMediaSelect = (media) => {
        if (!mediaPickerTarget) {
            setMediaPickerOpen(false);
            return;
        }

        if (mediaPickerTarget.kind === 'single') {
            onChange({
                ...block,
                data: {
                    ...data,
                    path: media.path,
                    url: media.url,
                    alt: data.alt || media.name || '',
                },
            });
        }

        if (mediaPickerTarget.kind === 'video_poster') {
            onChange({
                ...block,
                data: {
                    ...data,
                    poster_path: media.path,
                    poster_url: media.url,
                },
            });
        }

        if (mediaPickerTarget.kind === 'video_grid_poster') {
            updateArrayItem('items', mediaPickerTarget.itemId, {
                poster_path: media.path,
                poster_url: media.url,
            });
        }

        if (mediaPickerTarget.kind === 'gallery') {
            updateArrayItem('images', mediaPickerTarget.itemId, {
                path: media.path,
                url: media.url,
                alt: mediaPickerTarget.currentAlt || media.name || '',
            });
        }

        if (mediaPickerTarget.kind === 'slide') {
            updateArrayItem('slides', mediaPickerTarget.itemId, {
                path: media.path,
                url: media.url,
                alt: mediaPickerTarget.currentAlt || media.name || '',
            });
        }

        if (mediaPickerTarget.kind === 'photogrid') {
            updateArrayItem('items', mediaPickerTarget.itemId, {
                path: media.path,
                url: media.url,
                alt: mediaPickerTarget.currentAlt || media.name || '',
            });
        }

        setMediaPickerTarget(null);
        setMediaPickerOpen(false);
    };

    switch (block.type) {
        case 'hero':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Eyebrow</label>
                        <input className="form-control" value={data.eyebrow || ''} onChange={(event) => update('eyebrow', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div className="row g-3">
                        <div className="col-md-6">
                            <label className="form-label">Button Label</label>
                            <input className="form-control" value={data.button_label || ''} onChange={(event) => update('button_label', event.target.value)} />
                        </div>
                        <div className="col-md-6">
                            <label className="form-label">Button URL</label>
                            <input className="form-control" value={data.button_url || ''} onChange={(event) => update('button_url', event.target.value)} />
                        </div>
                    </div>
                </div>
            );
        case 'text':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Content</label>
                        <textarea className="form-control" rows="8" value={data.content || ''} onChange={(event) => update('content', event.target.value)} />
                    </div>
                </div>
            );
        case 'image':
            return (
                <>
                    <div className="vstack gap-3">
                        <div>
                            <label className="form-label">Media Library</label>
                            <div className="d-flex gap-2">
                                <input className="form-control" value={data.path || data.url || ''} readOnly placeholder="Select image from media library" />
                                <button
                                    type="button"
                                    className="btn btn-outline-primary"
                                    onClick={() => {
                                        setMediaPickerTarget({ kind: 'single' });
                                        setMediaPickerOpen(true);
                                    }}
                                >
                                    Browse
                                </button>
                            </div>
                        </div>
                        <div>
                            <label className="form-label">Image URL</label>
                            <input
                                className="form-control"
                                value={data.url || ''}
                                onChange={(event) => {
                                    onChange({
                                        ...block,
                                        data: {
                                            ...data,
                                            url: event.target.value,
                                            path: '',
                                        },
                                    });
                                }}
                            />
                        </div>
                        <div>
                            <label className="form-label">Alt Text</label>
                            <input className="form-control" value={data.alt || ''} onChange={(event) => update('alt', event.target.value)} />
                        </div>
                        <div>
                            <label className="form-label">Caption</label>
                            <input className="form-control" value={data.caption || ''} onChange={(event) => update('caption', event.target.value)} />
                        </div>
                    </div>
                    <MediaPickerModal
                        isOpen={mediaPickerOpen}
                        onClose={() => {
                            setMediaPickerOpen(false);
                            setMediaPickerTarget(null);
                        }}
                        onSelect={handleMediaSelect}
                        indexUrl={payload.mediaIndexUrl}
                        uploadUrl={payload.uploadUrl}
                    />
                </>
            );
        case 'video': {
            const posterValue = data.poster_path || data.poster_url || '';

            return (
                <>
                    <div className="vstack gap-3">
                        <div>
                            <label className="form-label">Title</label>
                            <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                        </div>
                        <div>
                            <label className="form-label">Subtitle</label>
                            <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                        </div>
                        <div>
                            <label className="form-label">Video URL</label>
                            <input className="form-control" value={data.url || ''} onChange={(event) => update('url', event.target.value)} placeholder="YouTube, Vimeo, or direct MP4/WebM URL" />
                            <div className="form-text">Accepts YouTube, Vimeo, or direct video file URLs.</div>
                        </div>
                        <div>
                            <label className="form-label">Poster Image</label>
                            <div className="d-flex gap-2">
                                <input className="form-control" value={posterValue} readOnly placeholder="Select poster from media library" />
                                <button
                                    type="button"
                                    className="btn btn-outline-primary"
                                    onClick={() => {
                                        setMediaPickerTarget({ kind: 'video_poster' });
                                        setMediaPickerOpen(true);
                                    }}
                                >
                                    Browse
                                </button>
                            </div>
                        </div>
                        <div>
                            <label className="form-label">Poster URL</label>
                            <input
                                className="form-control"
                                value={data.poster_url || ''}
                                onChange={(event) => {
                                    onChange({
                                        ...block,
                                        data: {
                                            ...data,
                                            poster_url: event.target.value,
                                            poster_path: '',
                                        },
                                    });
                                }}
                            />
                        </div>
                        <div>
                            <label className="form-label">Caption</label>
                            <textarea className="form-control" rows="3" value={data.caption || ''} onChange={(event) => update('caption', event.target.value)} />
                        </div>
                    </div>
                    <MediaPickerModal
                        isOpen={mediaPickerOpen}
                        onClose={() => {
                            setMediaPickerOpen(false);
                            setMediaPickerTarget(null);
                        }}
                        onSelect={handleMediaSelect}
                        indexUrl={payload.mediaIndexUrl}
                        uploadUrl={payload.uploadUrl}
                    />
                </>
            );
        }
        case 'video_grid':
            return (
                <VideoGridFields
                    data={data}
                    update={update}
                    updateArrayItem={updateArrayItem}
                    addArrayItem={addArrayItem}
                    deleteArrayItem={deleteArrayItem}
                    moveArrayItem={moveArrayItem}
                    mediaIndexUrl={payload.mediaIndexUrl}
                    uploadUrl={payload.uploadUrl}
                />
            );
        case 'slideshow':
            return (
                <SlideshowFields
                    data={data}
                    update={update}
                    updateArrayItem={updateArrayItem}
                    addArrayItem={addArrayItem}
                    deleteArrayItem={deleteArrayItem}
                    moveArrayItem={moveArrayItem}
                    mediaIndexUrl={payload.mediaIndexUrl}
                    uploadUrl={payload.uploadUrl}
                />
            );
        case 'photogrid':
            return (
                <PhotoGridFields
                    data={data}
                    update={update}
                    updateArrayItem={updateArrayItem}
                    addArrayItem={addArrayItem}
                    deleteArrayItem={deleteArrayItem}
                    moveArrayItem={moveArrayItem}
                    mediaIndexUrl={payload.mediaIndexUrl}
                    uploadUrl={payload.uploadUrl}
                />
            );
        case 'feature_grid':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div className="d-flex justify-content-between align-items-center">
                        <label className="form-label mb-0">Items</label>
                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createFeatureItem)}>
                            Add Item
                        </button>
                    </div>
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index, items) => (
                            <div key={item.id} className="border rounded-4 p-3">
                                <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div className="fw-semibold">Feature #{index + 1}</div>
                                    <div className="btn-group btn-group-sm">
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                        <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                    </div>
                                </div>
                                <div className="vstack gap-3">
                                    <div>
                                        <label className="form-label">Title</label>
                                        <input
                                            className="form-control"
                                            value={item.title || ''}
                                            onChange={(event) => updateArrayItem('items', item.id, { title: event.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="form-label">Description</label>
                                        <textarea
                                            className="form-control"
                                            rows="4"
                                            value={item.description || ''}
                                            onChange={(event) => updateArrayItem('items', item.id, { description: event.target.value })}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'gallery':
            return (
                <GalleryFields
                    block={block}
                    data={data}
                    onChange={onChange}
                    payload={payload}
                    update={update}
                    updateArrayItem={updateArrayItem}
                    addArrayItem={addArrayItem}
                    deleteArrayItem={deleteArrayItem}
                    moveArrayItem={moveArrayItem}
                    mediaIndexUrl={payload.mediaIndexUrl}
                    uploadUrl={payload.uploadUrl}
                />
            );
        case 'faq':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div className="d-flex justify-content-between align-items-center">
                        <label className="form-label mb-0">Questions</label>
                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createFaqItem)}>
                            Add Question
                        </button>
                    </div>
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index, items) => (
                            <div key={item.id} className="border rounded-4 p-3">
                                <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div className="fw-semibold">FAQ #{index + 1}</div>
                                    <div className="btn-group btn-group-sm">
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                        <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                    </div>
                                </div>
                                <div className="vstack gap-3">
                                    <div>
                                        <label className="form-label">Question</label>
                                        <input
                                            className="form-control"
                                            value={item.question || ''}
                                            onChange={(event) => updateArrayItem('items', item.id, { question: event.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="form-label">Answer</label>
                                        <textarea
                                            className="form-control"
                                            rows="4"
                                            value={item.answer || ''}
                                            onChange={(event) => updateArrayItem('items', item.id, { answer: event.target.value })}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'social_media':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div className="d-flex justify-content-between align-items-center">
                        <label className="form-label mb-0">Social Links</label>
                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createSocialMediaItem)}>
                            Add Social
                        </button>
                    </div>
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index, items) => (
                            <div key={item.id} className="border rounded-4 p-3">
                                <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div className="fw-semibold">Social #{index + 1}</div>
                                    <div className="btn-group btn-group-sm">
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                        <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                    </div>
                                </div>
                                <div className="row g-3">
                                    <div className="col-md-4">
                                        <label className="form-label">Platform</label>
                                        <input className="form-control" value={item.platform || ''} onChange={(event) => updateArrayItem('items', item.id, { platform: event.target.value })} />
                                    </div>
                                    <div className="col-md-4">
                                        <label className="form-label">Label</label>
                                        <input className="form-control" value={item.label || ''} onChange={(event) => updateArrayItem('items', item.id, { label: event.target.value })} />
                                    </div>
                                    <div className="col-md-4">
                                        <label className="form-label">URL</label>
                                        <input className="form-control" value={item.url || ''} onChange={(event) => updateArrayItem('items', item.id, { url: event.target.value })} />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'timeline':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div className="d-flex justify-content-between align-items-center">
                        <label className="form-label mb-0">Timeline Items</label>
                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createTimelineItem)}>
                            Add Step
                        </button>
                    </div>
                    <div className="vstack gap-3">
                        {(Array.isArray(data.items) ? data.items : []).map((item, index, items) => (
                            <div key={item.id} className="border rounded-4 p-3">
                                <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div className="fw-semibold">Step #{index + 1}</div>
                                    <div className="btn-group btn-group-sm">
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                        <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                        <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                    </div>
                                </div>
                                <div className="vstack gap-3">
                                    <div>
                                        <label className="form-label">Date / Step Label</label>
                                        <input className="form-control" value={item.date || ''} onChange={(event) => updateArrayItem('items', item.id, { date: event.target.value })} />
                                    </div>
                                    <div>
                                        <label className="form-label">Title</label>
                                        <input className="form-control" value={item.title || ''} onChange={(event) => updateArrayItem('items', item.id, { title: event.target.value })} />
                                    </div>
                                    <div>
                                        <label className="form-label">Description</label>
                                        <textarea className="form-control" rows="4" value={item.description || ''} onChange={(event) => updateArrayItem('items', item.id, { description: event.target.value })} />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            );
        case 'dynamic_collection':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Subtitle</label>
                        <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Content Type</label>
                        <select
                            className="form-select"
                            value={data.content_type_id || ''}
                            onChange={(event) => update('content_type_id', event.target.value)}
                        >
                            <option value="">Select content type</option>
                            {contentTypes.map((contentType) => (
                                <option key={contentType.id} value={contentType.id}>
                                    {contentType.name} ({contentType.entries_count || 0} entries)
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="form-label">Limit</label>
                        <input
                            className="form-control"
                            type="number"
                            min="1"
                            max="24"
                            value={data.limit ?? 3}
                            onChange={(event) => update('limit', Number(event.target.value || 1))}
                        />
                    </div>
                    <div className="alert alert-light border mb-0">
                        This block renders published entries from the selected content type on the public page.
                    </div>
                </div>
            );
        case 'form':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Content</label>
                        <textarea className="form-control" rows="4" value={data.content || ''} onChange={(event) => update('content', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Form</label>
                        <select
                            className="form-select"
                            value={data.form_id || ''}
                            onChange={(event) => update('form_id', event.target.value)}
                        >
                            <option value="">Select existing form</option>
                            {forms.map((form) => (
                                <option key={form.id} value={form.id}>
                                    {form.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="form-label">Button Label</label>
                        <input className="form-control" value={data.button_label || ''} onChange={(event) => update('button_label', event.target.value)} />
                    </div>
                    <div className="alert alert-light border mb-0">
                        This block links to an existing Rawdee form. Submission flow still follows the current form module rules and permissions.
                    </div>
                </div>
            );
        case 'cta':
            return (
                <div className="vstack gap-3">
                    <div>
                        <label className="form-label">Title</label>
                        <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                    </div>
                    <div>
                        <label className="form-label">Content</label>
                        <textarea className="form-control" rows="5" value={data.content || ''} onChange={(event) => update('content', event.target.value)} />
                    </div>
                    <div className="row g-3">
                        <div className="col-md-6">
                            <label className="form-label">Button Label</label>
                            <input className="form-control" value={data.button_label || ''} onChange={(event) => update('button_label', event.target.value)} />
                        </div>
                        <div className="col-md-6">
                            <label className="form-label">Button URL</label>
                            <input className="form-control" value={data.button_url || ''} onChange={(event) => update('button_url', event.target.value)} />
                        </div>
                    </div>
                </div>
            );
        case 'spacer':
            return (
                <div>
                    <label className="form-label">Height (px)</label>
                    <input
                        className="form-control"
                        type="number"
                        min="0"
                        step="10"
                        value={data.height ?? 80}
                        onChange={(event) => update('height', Number(event.target.value || 0))}
                    />
                </div>
            );
        default:
            return (
                <div className="alert alert-warning mb-0">
                    This block type is not yet supported in the minimum editor.
                </div>
            );
    }
}

function GalleryFields({
    block,
    data,
    onChange,
    update,
    updateArrayItem,
    addArrayItem,
    deleteArrayItem,
    moveArrayItem,
    mediaIndexUrl,
    uploadUrl,
}) {
    const images = Array.isArray(data.images) ? data.images : [];
    const [mediaPickerOpen, setMediaPickerOpen] = useState(false);
    const [activeImageId, setActiveImageId] = useState(null);

    const handleMediaSelect = (media) => {
        if (!activeImageId) {
            setMediaPickerOpen(false);
            return;
        }

        const currentImage = images.find((item) => item.id === activeImageId);

        updateArrayItem('images', activeImageId, {
            path: media.path,
            url: media.url,
            alt: currentImage?.alt || media.name || '',
        });

        setActiveImageId(null);
        setMediaPickerOpen(false);
    };

    return (
        <>
        <div className="vstack gap-3">
            <div>
                <label className="form-label">Title</label>
                <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
            </div>
            <div>
                <label className="form-label">Subtitle</label>
                <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
            </div>
            <div className="d-flex justify-content-between align-items-center">
                <label className="form-label mb-0">Images</label>
                <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('images', createGalleryImage)}>
                    Add Image
                </button>
            </div>
            <div className="vstack gap-3">
                {images.map((image, index) => (
                    <div key={image.id} className="border rounded-4 p-3">
                        <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div className="fw-semibold">Image #{index + 1}</div>
                            <div className="btn-group btn-group-sm">
                                <button type="button" className="btn btn-light" onClick={() => moveArrayItem('images', image.id, -1)} disabled={index === 0}>↑</button>
                                <button type="button" className="btn btn-light" onClick={() => moveArrayItem('images', image.id, 1)} disabled={index === images.length - 1}>↓</button>
                                <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('images', image.id)}>×</button>
                            </div>
                        </div>
                        <div className="vstack gap-3">
                            <div>
                                <label className="form-label">Media Library</label>
                                <div className="d-flex gap-2">
                                    <input className="form-control" value={image.path || image.url || ''} readOnly placeholder="Select image from media library" />
                                    <button
                                        type="button"
                                        className="btn btn-outline-primary"
                                        onClick={() => {
                                            setActiveImageId(image.id);
                                            setMediaPickerOpen(true);
                                        }}
                                    >
                                        Browse
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label className="form-label">Image URL</label>
                                <input
                                    className="form-control"
                                    value={image.url || ''}
                                    onChange={(event) => updateArrayItem('images', image.id, { url: event.target.value, path: '' })}
                                />
                            </div>
                            <div>
                                <label className="form-label">Alt Text</label>
                                <input
                                    className="form-control"
                                    value={image.alt || ''}
                                    onChange={(event) => updateArrayItem('images', image.id, { alt: event.target.value })}
                                />
                            </div>
                            <div>
                                <label className="form-label">Caption</label>
                                <input
                                    className="form-control"
                                    value={image.caption || ''}
                                    onChange={(event) => updateArrayItem('images', image.id, { caption: event.target.value })}
                                />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
            {images.length === 0 ? (
                <div className="alert alert-light border mb-0">
                    Add the first image to start building the gallery block.
                </div>
            ) : null}
        </div>
        <MediaPickerModal
            isOpen={mediaPickerOpen}
            onClose={() => {
                setMediaPickerOpen(false);
                setActiveImageId(null);
            }}
            onSelect={handleMediaSelect}
            indexUrl={mediaIndexUrl}
            uploadUrl={uploadUrl}
        />
        </>
    );
}

function SlideshowFields({
    data,
    update,
    updateArrayItem,
    addArrayItem,
    deleteArrayItem,
    moveArrayItem,
    mediaIndexUrl,
    uploadUrl,
}) {
    const slides = Array.isArray(data.slides) ? data.slides : [];
    const [mediaPickerOpen, setMediaPickerOpen] = useState(false);
    const [activeSlideId, setActiveSlideId] = useState(null);

    const handleMediaSelect = (media) => {
        if (!activeSlideId) {
            setMediaPickerOpen(false);
            return;
        }

        const currentSlide = slides.find((item) => item.id === activeSlideId);

        updateArrayItem('slides', activeSlideId, {
            path: media.path,
            url: media.url,
            alt: currentSlide?.alt || media.name || '',
        });

        setActiveSlideId(null);
        setMediaPickerOpen(false);
    };

    return (
        <>
            <div className="vstack gap-3">
                <div>
                    <label className="form-label">Title</label>
                    <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                </div>
                <div>
                    <label className="form-label">Subtitle</label>
                    <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                </div>
                <div className="d-flex justify-content-between align-items-center">
                    <label className="form-label mb-0">Slides</label>
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('slides', createSlideItem)}>
                        Add Slide
                    </button>
                </div>
                <div className="vstack gap-3">
                    {slides.map((slide, index) => (
                        <div key={slide.id} className="border rounded-4 p-3">
                            <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div className="fw-semibold">Slide #{index + 1}</div>
                                <div className="btn-group btn-group-sm">
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('slides', slide.id, -1)} disabled={index === 0}>↑</button>
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('slides', slide.id, 1)} disabled={index === slides.length - 1}>↓</button>
                                    <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('slides', slide.id)}>×</button>
                                </div>
                            </div>
                            <div className="vstack gap-3">
                                <div>
                                    <label className="form-label">Media Library</label>
                                    <div className="d-flex gap-2">
                                        <input className="form-control" value={slide.path || slide.url || ''} readOnly placeholder="Select slide image from media library" />
                                        <button
                                            type="button"
                                            className="btn btn-outline-primary"
                                            onClick={() => {
                                                setActiveSlideId(slide.id);
                                                setMediaPickerOpen(true);
                                            }}
                                        >
                                            Browse
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label className="form-label">Image URL</label>
                                    <input className="form-control" value={slide.url || ''} onChange={(event) => updateArrayItem('slides', slide.id, { url: event.target.value, path: '' })} />
                                </div>
                                <div>
                                    <label className="form-label">Alt Text</label>
                                    <input className="form-control" value={slide.alt || ''} onChange={(event) => updateArrayItem('slides', slide.id, { alt: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Title</label>
                                    <input className="form-control" value={slide.title || ''} onChange={(event) => updateArrayItem('slides', slide.id, { title: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Description</label>
                                    <textarea className="form-control" rows="3" value={slide.description || ''} onChange={(event) => updateArrayItem('slides', slide.id, { description: event.target.value })} />
                                </div>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Button Label</label>
                                        <input className="form-control" value={slide.button_label || ''} onChange={(event) => updateArrayItem('slides', slide.id, { button_label: event.target.value })} />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label">Button URL</label>
                                        <input className="form-control" value={slide.button_url || ''} onChange={(event) => updateArrayItem('slides', slide.id, { button_url: event.target.value })} />
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
                {slides.length === 0 ? (
                    <div className="alert alert-light border mb-0">
                        Add the first slide to build the slideshow block.
                    </div>
                ) : null}
            </div>
            <MediaPickerModal
                isOpen={mediaPickerOpen}
                onClose={() => {
                    setMediaPickerOpen(false);
                    setActiveSlideId(null);
                }}
                onSelect={handleMediaSelect}
                indexUrl={mediaIndexUrl}
                uploadUrl={uploadUrl}
            />
        </>
    );
}

function VideoGridFields({
    data,
    update,
    updateArrayItem,
    addArrayItem,
    deleteArrayItem,
    moveArrayItem,
    mediaIndexUrl,
    uploadUrl,
}) {
    const items = Array.isArray(data.items) ? data.items : [];
    const [mediaPickerOpen, setMediaPickerOpen] = useState(false);
    const [activeItemId, setActiveItemId] = useState(null);

    const handleMediaSelect = (media) => {
        if (!activeItemId) {
            setMediaPickerOpen(false);
            return;
        }

        updateArrayItem('items', activeItemId, {
            poster_path: media.path,
            poster_url: media.url,
        });

        setActiveItemId(null);
        setMediaPickerOpen(false);
    };

    return (
        <>
            <div className="vstack gap-3">
                <div>
                    <label className="form-label">Title</label>
                    <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                </div>
                <div>
                    <label className="form-label">Subtitle</label>
                    <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                </div>
                <div className="d-flex justify-content-between align-items-center">
                    <label className="form-label mb-0">Videos</label>
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createVideoGridItem)}>
                        Add Video
                    </button>
                </div>
                <div className="vstack gap-3">
                    {items.map((item, index) => (
                        <div key={item.id} className="border rounded-4 p-3">
                            <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div className="fw-semibold">Video #{index + 1}</div>
                                <div className="btn-group btn-group-sm">
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                    <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                </div>
                            </div>
                            <div className="vstack gap-3">
                                <div>
                                    <label className="form-label">Title</label>
                                    <input className="form-control" value={item.title || ''} onChange={(event) => updateArrayItem('items', item.id, { title: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Description</label>
                                    <textarea className="form-control" rows="3" value={item.description || ''} onChange={(event) => updateArrayItem('items', item.id, { description: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Video URL</label>
                                    <input className="form-control" value={item.url || ''} onChange={(event) => updateArrayItem('items', item.id, { url: event.target.value })} placeholder="YouTube, Vimeo, or direct MP4/WebM URL" />
                                </div>
                                <div>
                                    <label className="form-label">Poster Image</label>
                                    <div className="d-flex gap-2">
                                        <input className="form-control" value={item.poster_path || item.poster_url || ''} readOnly placeholder="Select poster from media library" />
                                        <button
                                            type="button"
                                            className="btn btn-outline-primary"
                                            onClick={() => {
                                                setActiveItemId(item.id);
                                                setMediaPickerOpen(true);
                                            }}
                                        >
                                            Browse
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label className="form-label">Poster URL</label>
                                    <input className="form-control" value={item.poster_url || ''} onChange={(event) => updateArrayItem('items', item.id, { poster_url: event.target.value, poster_path: '' })} />
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
            <MediaPickerModal
                isOpen={mediaPickerOpen}
                onClose={() => {
                    setMediaPickerOpen(false);
                    setActiveItemId(null);
                }}
                onSelect={handleMediaSelect}
                indexUrl={mediaIndexUrl}
                uploadUrl={uploadUrl}
            />
        </>
    );
}

function PhotoGridFields({
    data,
    update,
    updateArrayItem,
    addArrayItem,
    deleteArrayItem,
    moveArrayItem,
    mediaIndexUrl,
    uploadUrl,
}) {
    const items = Array.isArray(data.items) ? data.items : [];
    const [mediaPickerOpen, setMediaPickerOpen] = useState(false);
    const [activeItemId, setActiveItemId] = useState(null);

    const handleMediaSelect = (media) => {
        if (!activeItemId) {
            setMediaPickerOpen(false);
            return;
        }

        const currentItem = items.find((item) => item.id === activeItemId);

        updateArrayItem('items', activeItemId, {
            path: media.path,
            url: media.url,
            alt: currentItem?.alt || media.name || '',
        });

        setActiveItemId(null);
        setMediaPickerOpen(false);
    };

    return (
        <>
            <div className="vstack gap-3">
                <div>
                    <label className="form-label">Title</label>
                    <input className="form-control" value={data.title || ''} onChange={(event) => update('title', event.target.value)} />
                </div>
                <div>
                    <label className="form-label">Subtitle</label>
                    <textarea className="form-control" rows="4" value={data.subtitle || ''} onChange={(event) => update('subtitle', event.target.value)} />
                </div>
                <div className="d-flex justify-content-between align-items-center">
                    <label className="form-label mb-0">Photos</label>
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => addArrayItem('items', createPhotoGridItem)}>
                        Add Photo
                    </button>
                </div>
                <div className="vstack gap-3">
                    {items.map((item, index) => (
                        <div key={item.id} className="border rounded-4 p-3">
                            <div className="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div className="fw-semibold">Photo #{index + 1}</div>
                                <div className="btn-group btn-group-sm">
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, -1)} disabled={index === 0}>↑</button>
                                    <button type="button" className="btn btn-light" onClick={() => moveArrayItem('items', item.id, 1)} disabled={index === items.length - 1}>↓</button>
                                    <button type="button" className="btn btn-outline-danger" onClick={() => deleteArrayItem('items', item.id)}>×</button>
                                </div>
                            </div>
                            <div className="vstack gap-3">
                                <div>
                                    <label className="form-label">Media Library</label>
                                    <div className="d-flex gap-2">
                                        <input className="form-control" value={item.path || item.url || ''} readOnly placeholder="Select image from media library" />
                                        <button
                                            type="button"
                                            className="btn btn-outline-primary"
                                            onClick={() => {
                                                setActiveItemId(item.id);
                                                setMediaPickerOpen(true);
                                            }}
                                        >
                                            Browse
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label className="form-label">Image URL</label>
                                    <input className="form-control" value={item.url || ''} onChange={(event) => updateArrayItem('items', item.id, { url: event.target.value, path: '' })} />
                                </div>
                                <div>
                                    <label className="form-label">Alt Text</label>
                                    <input className="form-control" value={item.alt || ''} onChange={(event) => updateArrayItem('items', item.id, { alt: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Title</label>
                                    <input className="form-control" value={item.title || ''} onChange={(event) => updateArrayItem('items', item.id, { title: event.target.value })} />
                                </div>
                                <div>
                                    <label className="form-label">Caption</label>
                                    <textarea className="form-control" rows="3" value={item.caption || ''} onChange={(event) => updateArrayItem('items', item.id, { caption: event.target.value })} />
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
            <MediaPickerModal
                isOpen={mediaPickerOpen}
                onClose={() => {
                    setMediaPickerOpen(false);
                    setActiveItemId(null);
                }}
                onSelect={handleMediaSelect}
                indexUrl={mediaIndexUrl}
                uploadUrl={uploadUrl}
            />
        </>
    );
}

export default function PageBuilderEditor({ payload }) {
    const initialBlocks = useMemo(() => {
        if (payload.oldBlocksJson) {
            return parseBlocks(payload.oldBlocksJson);
        }

        return normalizeBlocks(payload.blocks || []);
    }, [payload.blocks, payload.oldBlocksJson]);

    const [blocks, setBlocks] = useState(initialBlocks);
    const [activeBlockId, setActiveBlockId] = useState(initialBlocks[0]?.id || null);
    const [showJsonDebug, setShowJsonDebug] = useState(false);
    const reusableBlocks = Array.isArray(payload.reusableBlocks) ? payload.reusableBlocks : [];
    const blockTypes = Array.isArray(payload.blockTypes) && payload.blockTypes.length > 0
        ? payload.blockTypes
        : BLOCK_LIBRARY;
    const disabledBlockTypesUsed = Array.isArray(payload.disabledBlockTypesUsed) ? payload.disabledBlockTypesUsed : [];
    const enabledBlockTypeSet = useMemo(() => new Set(blockTypes.map((item) => item.type)), [blockTypes]);
    window.__pageBuilderContentTypes = Array.isArray(payload.contentTypes) ? payload.contentTypes : [];
    window.__pageBuilderForms = Array.isArray(payload.forms) ? payload.forms : [];

    const activeBlock = blocks.find((block) => block.id === activeBlockId) || null;
    const serialized = useMemo(() => JSON.stringify(blocks, null, 2), [blocks]);

    useEffect(() => {
        const input = document.getElementById('blocks_json');

        if (input) {
            input.value = serialized;
        }
    }, [serialized]);

    useEffect(() => {
        if (!activeBlockId && blocks[0]) {
            setActiveBlockId(blocks[0].id);
        }

        if (activeBlockId && !blocks.some((block) => block.id === activeBlockId)) {
            setActiveBlockId(blocks[0]?.id || null);
        }
    }, [activeBlockId, blocks]);

    const addBlock = (type) => {
        const factory = DEFAULT_BLOCKS[type];
        const nextBlock = {
            id: generateId(),
            ...(factory ? factory() : { type, data: {} }),
        };

        setBlocks((current) => [...current, nextBlock]);
        setActiveBlockId(nextBlock.id);
    };

    const insertReusableBlock = (reusableBlock) => {
        const nextBlocks = cloneBlocks(reusableBlock?.blocks || []);

        if (nextBlocks.length === 0) {
            return;
        }

        setBlocks((current) => [...current, ...nextBlocks]);
        setActiveBlockId(nextBlocks[0].id);
    };

    const updateBlock = (nextBlock) => {
        setBlocks((current) => current.map((block) => (block.id === nextBlock.id ? nextBlock : block)));
    };

    const deleteBlock = (id) => {
        setBlocks((current) => current.filter((block) => block.id !== id));
    };

    const moveBlock = (id, direction) => {
        setBlocks((current) => {
            const index = current.findIndex((block) => block.id === id);
            return moveItem(current, index, direction);
        });
    };

    return (
        <div className="card border-0 shadow-sm">
            <div className="card-header bg-transparent border-0 pt-4 px-4">
                <div className="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <div>
                        <div className="d-flex align-items-center gap-2 mb-2">
                            <h5 className="mb-0">Visual Builder</h5>
                            <span className="badge bg-success-subtle text-success">Phase 10</span>
                        </div>
                        <p className="text-muted mb-0">
                            Visual block editor for Page Builder. It writes directly to <code>pb_pages.blocks</code> through the existing form submit.
                        </p>
                    </div>
                    <button type="button" className="btn btn-sm btn-light" onClick={() => setShowJsonDebug((value) => !value)}>
                        {showJsonDebug ? 'Hide JSON' : 'Show JSON'}
                    </button>
                </div>
            </div>

            <div className="card-body px-4 pb-4">
                <div className="row g-4">
                    <div className="col-xl-3">
                        <div className="border rounded-4 p-3 bg-light-subtle h-100">
                            <div className="fw-semibold mb-3">Add Block</div>
                            <div className="d-grid gap-2">
                                {blockTypes.map((item) => (
                                    <button
                                        key={item.type}
                                        type="button"
                                        className="btn btn-outline-primary text-start"
                                        onClick={() => addBlock(item.type)}
                                    >
                                        <div className="fw-semibold">{item.label}</div>
                                        <div className="small opacity-75">{item.description}</div>
                                    </button>
                                ))}
                            </div>
                            {blockTypes.length === 0 ? (
                                <div className="alert alert-warning border-warning-subtle mb-0">
                                    No block types are enabled. Enable at least one block type from Plugins / Theme.
                                </div>
                            ) : null}

                            {disabledBlockTypesUsed.length > 0 ? (
                                <div className="alert alert-warning border-warning-subtle mt-3 mb-0">
                                    Existing content uses disabled block type(s): <code>{disabledBlockTypesUsed.join(', ')}</code>.
                                </div>
                            ) : null}

                            {reusableBlocks.length > 0 ? (
                                <>
                                    <hr className="my-4" />
                                    <div className="fw-semibold mb-3">Insert Reusable</div>
                                    <div className="d-grid gap-2">
                                        {reusableBlocks.map((reusableBlock) => (
                                            <button
                                                key={reusableBlock.id || reusableBlock.slug}
                                                type="button"
                                                className="btn btn-outline-dark text-start"
                                                onClick={() => insertReusableBlock(reusableBlock)}
                                            >
                                                <div className="fw-semibold">{reusableBlock.name}</div>
                                                <div className="small opacity-75">
                                                    {reusableBlock.description || `${(reusableBlock.blocks || []).length} block(s)`}
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </>
                            ) : null}
                        </div>
                    </div>

                    <div className="col-xl-4">
                        <div className="border rounded-4 h-100">
                            <div className="border-bottom px-3 py-3 d-flex justify-content-between align-items-center">
                                <div className="fw-semibold">Structure</div>
                                <span className="badge bg-light text-dark">{blocks.length} block(s)</span>
                            </div>
                            <div className="p-3 vstack gap-2" style={{ minHeight: '420px' }}>
                                {blocks.length === 0 ? (
                                    <div className="text-center text-muted py-5">
                                        Add the first block from the library to start building this page.
                                    </div>
                                ) : (
                                    blocks.map((block, index) => {
                                        const isActive = block.id === activeBlockId;

                                        return (
                                            <div
                                                key={block.id}
                                                className={`border rounded-3 p-3 ${isActive ? 'border-primary bg-primary-subtle' : 'bg-white'}`}
                                            >
                                                <div className="d-flex justify-content-between align-items-start gap-2">
                                                    <button
                                                        type="button"
                                                        className="btn btn-link text-decoration-none text-start p-0 flex-grow-1"
                                                        onClick={() => setActiveBlockId(block.id)}
                                                    >
                                                        <div className="fw-semibold text-dark">{getBlockName(block.type)}</div>
                                                        <div className="small text-muted">
                                                            Block #{index + 1}
                                                            {!enabledBlockTypeSet.has(block.type) ? ' · disabled type' : ''}
                                                        </div>
                                                    </button>
                                                    <div className="btn-group btn-group-sm">
                                                        <button type="button" className="btn btn-light" onClick={() => moveBlock(block.id, -1)} disabled={index === 0}>↑</button>
                                                        <button type="button" className="btn btn-light" onClick={() => moveBlock(block.id, 1)} disabled={index === blocks.length - 1}>↓</button>
                                                        <button type="button" className="btn btn-outline-danger" onClick={() => deleteBlock(block.id)}>×</button>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-xl-5">
                        <div className="border rounded-4 h-100 overflow-hidden">
                            <div className="border-bottom px-3 py-3">
                                <div className="fw-semibold">Inspector + Preview</div>
                                <div className="small text-muted">
                                    {activeBlock ? `Editing ${getBlockName(activeBlock.type)}` : 'Select a block to edit its fields'}
                                </div>
                            </div>
                            <div className="p-3">
                                {activeBlock ? (
                                    <div className="vstack gap-4">
                                        <BlockFields block={activeBlock} onChange={updateBlock} payload={payload} />
                                        <div>
                                            <div className="small text-uppercase text-muted fw-semibold mb-2">Live Preview</div>
                                            <PreviewBlock block={activeBlock} />
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center text-muted py-5">
                                        No block selected yet.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {showJsonDebug ? (
                    <div className="mt-4">
                        <label className="form-label fw-semibold">Generated JSON</label>
                        <pre className="bg-dark text-light rounded-4 p-3 mb-0 small" style={{ maxHeight: '320px', overflow: 'auto' }}>
                            {serialized}
                        </pre>
                    </div>
                ) : null}
            </div>
        </div>
    );
}
