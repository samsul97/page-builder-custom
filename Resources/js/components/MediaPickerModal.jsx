import React, { useEffect, useMemo, useState } from 'react';

async function fetchMedia(indexUrl, query = '') {
    const url = new URL(indexUrl, window.location.origin);
    url.searchParams.set('json', '1');

    if (query) {
        url.searchParams.set('q', query);
    }

    const response = await fetch(url.toString(), {
        headers: {
            Accept: 'application/json',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error('Failed to fetch media library');
    }

    const result = await response.json();
    return Array.isArray(result.media) ? result.media : [];
}

async function uploadMedia(file, uploadUrl) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            Accept: 'application/json',
        },
        body: formData,
        credentials: 'same-origin',
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Upload failed');
    }

    return result.media;
}

export default function MediaPickerModal({ isOpen, onClose, onSelect, indexUrl, uploadUrl }) {
    const [media, setMedia] = useState([]);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [uploading, setUploading] = useState(false);

    useEffect(() => {
        if (!isOpen || !indexUrl) {
            return;
        }

        let active = true;

        const load = async () => {
            setLoading(true);
            setError('');

            try {
                const items = await fetchMedia(indexUrl);

                if (active) {
                    setMedia(items);
                }
            } catch (nextError) {
                if (active) {
                    setError(nextError.message || 'Failed to load media');
                }
            } finally {
                if (active) {
                    setLoading(false);
                }
            }
        };

        load();

        return () => {
            active = false;
        };
    }, [indexUrl, isOpen]);

    const filteredMedia = useMemo(() => {
        const keyword = query.trim().toLowerCase();

        if (!keyword) {
            return media;
        }

        return media.filter((item) =>
            [item.name, item.filename, item.path, item.mime_type]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword))
        );
    }, [media, query]);

    if (!isOpen) {
        return null;
    }

    return (
        <div className="modal fade show d-block" tabIndex="-1" role="dialog" style={{ background: 'rgba(15, 23, 42, 0.55)' }}>
            <div className="modal-dialog modal-xl modal-dialog-scrollable">
                <div className="modal-content border-0 shadow-lg">
                    <div className="modal-header">
                        <div>
                            <h5 className="modal-title mb-1">Media Library</h5>
                            <div className="small text-muted">Select an existing image or upload a new one.</div>
                        </div>
                        <button type="button" className="btn-close" aria-label="Close" onClick={onClose} />
                    </div>

                    <div className="modal-body">
                        <div className="row g-3 align-items-end mb-4">
                            <div className="col-lg-6">
                                <label className="form-label">Search Media</label>
                                <input className="form-control" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search by name or path" />
                            </div>
                            <div className="col-lg-6">
                                <label className="form-label">Upload Image</label>
                                <input
                                    className="form-control"
                                    type="file"
                                    accept="image/*"
                                    onChange={async (event) => {
                                        const file = event.target.files?.[0];

                                        if (!file || !uploadUrl) {
                                            return;
                                        }

                                        setUploading(true);
                                        setError('');

                                        try {
                                            const uploaded = await uploadMedia(file, uploadUrl);

                                            if (uploaded) {
                                                setMedia((current) => [uploaded, ...current]);
                                            }
                                        } catch (nextError) {
                                            setError(nextError.message || 'Upload failed');
                                        } finally {
                                            setUploading(false);
                                            event.target.value = '';
                                        }
                                    }}
                                />
                                {uploading ? <div className="small text-primary mt-2">Uploading image...</div> : null}
                            </div>
                        </div>

                        {error ? <div className="alert alert-danger">{error}</div> : null}

                        {loading ? (
                            <div className="text-center py-5 text-muted">Loading media...</div>
                        ) : filteredMedia.length === 0 ? (
                            <div className="text-center py-5 text-muted">No media found.</div>
                        ) : (
                            <div className="row g-3">
                                {filteredMedia.map((item) => (
                                    <div key={item.id} className="col-md-4 col-xl-3">
                                        <button
                                            type="button"
                                            className="card h-100 border shadow-sm text-start w-100 bg-white"
                                            onClick={() => onSelect(item)}
                                        >
                                            <div className="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                                                {item.is_image ? (
                                                    <img src={item.url} alt={item.name} className="w-100 h-100 object-fit-cover" />
                                                ) : (
                                                    <div className="d-flex align-items-center justify-content-center text-muted">No preview</div>
                                                )}
                                            </div>
                                            <div className="card-body">
                                                <div className="fw-semibold text-truncate" title={item.name}>{item.name}</div>
                                                <div className="small text-muted text-truncate" title={item.path}>{item.path}</div>
                                            </div>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="modal-footer">
                        <button type="button" className="btn btn-light" onClick={onClose}>Close</button>
                    </div>
                </div>
            </div>
        </div>
    );
}
