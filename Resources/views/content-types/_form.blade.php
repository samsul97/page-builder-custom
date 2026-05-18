@php
    $schemaFields = old('schema_fields', $contentType->schema ?? [
        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'placeholder' => '', 'help_text' => '', 'options' => []],
        ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'placeholder' => '', 'help_text' => '', 'options' => []],
    ]);

    if (count($schemaFields) === 0) {
        $schemaFields = [
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'placeholder' => '', 'help_text' => '', 'options' => []],
        ];
    }

    $schemaJson = old(
        'schema_json',
        json_encode($contentType->schema ?? [
            ['name' => 'title', 'type' => 'text', 'label' => 'Title'],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Description'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Content Type Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $contentType->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $contentType->slug) }}" class="form-control @error('slug') is-invalid @enderror" required>
                </div>
                <div class="mb-0">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $contentType->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Schema Fields</h5>
                    <p class="text-muted mb-0 small">Define the fields that will appear in the content entry form.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success">Form Based</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-schema-field">
                        Add Field
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="vstack gap-3" id="schema-fields-list">
                    @foreach($schemaFields as $index => $field)
                        <div class="border rounded-3 p-3 schema-field-item" data-schema-field>
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                                    <div class="fw-semibold">Field Row <span data-schema-row-number>{{ $index + 1 }}</span></div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-schema-field>
                                        Delete
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Field Name</label>
                                    <input type="text" name="schema_fields[{{ $index }}][name]" value="{{ data_get($field, 'name') }}" class="form-control" placeholder="quote" data-schema-input="name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Label</label>
                                    <input type="text" name="schema_fields[{{ $index }}][label]" value="{{ data_get($field, 'label') }}" class="form-control" placeholder="Quote" data-schema-input="label">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <select name="schema_fields[{{ $index }}][type]" class="form-select" data-schema-input="type">
                                        @foreach(['text', 'textarea', 'number', 'url', 'select', 'checkbox'] as $type)
                                            <option value="{{ $type }}" @selected(data_get($field, 'type', 'text') === $type)>{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Placeholder</label>
                                    <input type="text" name="schema_fields[{{ $index }}][placeholder]" value="{{ data_get($field, 'placeholder') }}" class="form-control" data-schema-input="placeholder">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Help Text</label>
                                    <input type="text" name="schema_fields[{{ $index }}][help_text]" value="{{ data_get($field, 'help_text') }}" class="form-control" data-schema-input="help_text">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Options</label>
                                    <textarea name="schema_fields[{{ $index }}][options]" rows="3" class="form-control" placeholder="One option per line" data-schema-input="options">{{ is_array(data_get($field, 'options')) ? implode("\n", data_get($field, 'options')) : '' }}</textarea>
                                    <div class="form-text">Used for `select`. Write one option per line.</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="alert alert-light border mt-3 mb-0">
                    You can add as many schema rows as needed. For `select`, keep one field row and place all choices inside `Options`, one line per option.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Advanced JSON</h5>
                    <p class="text-muted mb-0 small">Optional fallback for debugging or unsupported schema structures.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info-subtle text-info">Fallback</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="apply-schema-json">
                        Apply JSON to Form
                    </button>
                </div>
            </div>
            <div class="card-body">
                <textarea id="schema_json" name="schema_json" rows="12" class="form-control font-monospace @error('schema_json') is-invalid @enderror" spellcheck="false">{{ $schemaJson }}</textarea>
                @error('schema_json')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $contentType->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
                    <a href="{{ route('page-builder.content-types.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="schema-field-template">
    <div class="border rounded-3 p-3 schema-field-item" data-schema-field>
        <div class="row g-3">
            <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                <div class="fw-semibold">Field Row <span data-schema-row-number></span></div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-schema-field>
                    Delete
                </button>
            </div>
            <div class="col-md-4">
                <label class="form-label">Field Name</label>
                <input type="text" class="form-control" placeholder="quote" data-schema-input="name">
            </div>
            <div class="col-md-4">
                <label class="form-label">Label</label>
                <input type="text" class="form-control" placeholder="Quote" data-schema-input="label">
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select class="form-select" data-schema-input="type">
                    @foreach(['text', 'textarea', 'number', 'url', 'select', 'checkbox'] as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Placeholder</label>
                <input type="text" class="form-control" data-schema-input="placeholder">
            </div>
            <div class="col-md-6">
                <label class="form-label">Help Text</label>
                <input type="text" class="form-control" data-schema-input="help_text">
            </div>
            <div class="col-12">
                <label class="form-label">Options</label>
                <textarea rows="3" class="form-control" placeholder="One option per line" data-schema-input="options"></textarea>
                <div class="form-text">Used for `select`. Write one option per line.</div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('schema-fields-list');
        const addButton = document.getElementById('add-schema-field');
        const template = document.getElementById('schema-field-template');
        const jsonTextarea = document.getElementById('schema_json');
        const applyJsonButton = document.getElementById('apply-schema-json');

        if (!list || !addButton || !template || !jsonTextarea || !applyJsonButton) {
            return;
        }

        const readRows = () => {
            return Array.from(list.querySelectorAll('[data-schema-field]'))
                .map((row) => {
                    const getValue = (key) => row.querySelector(`[data-schema-input="${key}"]`)?.value ?? '';
                    const options = getValue('options')
                        .split('\n')
                        .map((item) => item.trim())
                        .filter(Boolean);

                    const field = {
                        name: getValue('name').trim(),
                        label: getValue('label').trim(),
                        type: getValue('type').trim() || 'text',
                        placeholder: getValue('placeholder').trim(),
                        help_text: getValue('help_text').trim(),
                    };

                    if (options.length > 0) {
                        field.options = options;
                    }

                    return Object.fromEntries(Object.entries(field).filter(([, value]) => {
                        if (Array.isArray(value)) {
                            return value.length > 0;
                        }

                        return value !== '';
                    }));
                })
                .filter((field) => field.name || field.label);
        };

        const syncJsonFromForm = () => {
            jsonTextarea.value = JSON.stringify(readRows(), null, 2);
        };

        const updateRowIndexes = () => {
            Array.from(list.querySelectorAll('[data-schema-field]')).forEach((row, index) => {
                const number = row.querySelector('[data-schema-row-number]');

                if (number) {
                    number.textContent = String(index + 1);
                }

                row.querySelectorAll('[data-schema-input]').forEach((input) => {
                    const key = input.getAttribute('data-schema-input');
                    input.setAttribute('name', `schema_fields[${index}][${key}]`);
                });
            });

            syncJsonFromForm();
        };

        const bindRowActions = (row) => {
            const removeButton = row.querySelector('[data-remove-schema-field]');

            if (!removeButton) {
                return;
            }

            removeButton.addEventListener('click', function () {
                row.remove();

                if (list.querySelectorAll('[data-schema-field]').length === 0) {
                    addRow();
                }

                updateRowIndexes();
            });

            row.querySelectorAll('[data-schema-input]').forEach((input) => {
                input.addEventListener('input', syncJsonFromForm);
                input.addEventListener('change', syncJsonFromForm);
            });
        };

        const addRow = () => {
            const fragment = template.content.cloneNode(true);
            list.appendChild(fragment);
            bindRowActions(list.lastElementChild);
            updateRowIndexes();
            list.lastElementChild.querySelector('[data-schema-input="name"]')?.focus();
        };

        const renderRowsFromJson = (items) => {
            list.innerHTML = '';

            const rows = Array.isArray(items) && items.length > 0
                ? items
                : [{ name: '', label: '', type: 'text', placeholder: '', help_text: '', options: [] }];

            rows.forEach((item) => {
                const fragment = template.content.cloneNode(true);
                const row = fragment.querySelector('[data-schema-field]');

                row.querySelector('[data-schema-input="name"]').value = item?.name ?? '';
                row.querySelector('[data-schema-input="label"]').value = item?.label ?? '';
                row.querySelector('[data-schema-input="type"]').value = item?.type ?? 'text';
                row.querySelector('[data-schema-input="placeholder"]').value = item?.placeholder ?? '';
                row.querySelector('[data-schema-input="help_text"]').value = item?.help_text ?? '';
                row.querySelector('[data-schema-input="options"]').value = Array.isArray(item?.options) ? item.options.join('\n') : '';

                list.appendChild(fragment);
                bindRowActions(list.lastElementChild);
            });

            updateRowIndexes();
        };

        Array.from(list.querySelectorAll('[data-schema-field]')).forEach(bindRowActions);
        updateRowIndexes();

        addButton.addEventListener('click', addRow);

        applyJsonButton.addEventListener('click', function () {
            try {
                const parsed = JSON.parse(jsonTextarea.value || '[]');

                if (!Array.isArray(parsed)) {
                    throw new Error('Schema JSON must be an array.');
                }

                renderRowsFromJson(parsed);
            } catch (error) {
                window.alert(error.message || 'Schema JSON is invalid.');
            }
        });
    });
</script>
