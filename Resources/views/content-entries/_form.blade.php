@php
    $schema = $contentType->schema ?? [];
    $schemaFieldNames = collect($schema)->pluck('name')->filter()->values()->all();
    $entryData = old('content_fields', $entry->data ?? []);
    $extraContentFields = old(
        'extra_content_fields',
        collect($entry->data ?? [])
            ->except($schemaFieldNames)
            ->map(fn ($value, $key) => [
                'key' => $key,
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : $value,
            ])
            ->values()
            ->all()
    );
    $dataJson = old(
        'data_json',
        json_encode($entry->data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Entry Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $entry->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                </div>
                <div class="mb-0">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $entry->slug) }}" class="form-control @error('slug') is-invalid @enderror">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Content Fields</h5>
                    <p class="text-muted mb-0 small">This form is generated from the current content type schema.</p>
                </div>
                <span class="badge bg-success-subtle text-success">Form Based</span>
            </div>
            <div class="card-body">
                @if(empty($schema))
                    <div class="alert alert-light border mb-0">
                        This content type has no schema yet. You can still use the raw JSON editor below.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($schema as $field)
                            @php
                                $fieldName = data_get($field, 'name');
                                $fieldType = data_get($field, 'type', 'text');
                                $fieldLabel = data_get($field, 'label', \Illuminate\Support\Str::headline((string) $fieldName));
                                $fieldPlaceholder = data_get($field, 'placeholder');
                                $fieldValue = data_get($entryData, $fieldName);
                                $fieldOptions = data_get($field, 'options', []);
                                $fieldCol = in_array($fieldType, ['textarea'], true) ? 'col-12' : 'col-md-6';
                            @endphp

                            @if(filled($fieldName))
                                <div class="{{ $fieldCol }}">
                                    <label class="form-label">{{ $fieldLabel }}</label>

                                    @if($fieldType === 'textarea')
                                        <textarea
                                            name="content_fields[{{ $fieldName }}]"
                                            rows="5"
                                            class="form-control"
                                            placeholder="{{ $fieldPlaceholder }}"
                                        >{{ is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue }}</textarea>
                                    @elseif($fieldType === 'number')
                                        <input
                                            type="number"
                                            name="content_fields[{{ $fieldName }}]"
                                            value="{{ $fieldValue }}"
                                            class="form-control"
                                            placeholder="{{ $fieldPlaceholder }}"
                                        >
                                    @elseif($fieldType === 'url')
                                        <input
                                            type="url"
                                            name="content_fields[{{ $fieldName }}]"
                                            value="{{ $fieldValue }}"
                                            class="form-control"
                                            placeholder="{{ $fieldPlaceholder ?: 'https://example.com' }}"
                                        >
                                    @elseif($fieldType === 'select' && is_array($fieldOptions))
                                        <select name="content_fields[{{ $fieldName }}]" class="form-select">
                                            <option value="">Select option</option>
                                            @foreach($fieldOptions as $option)
                                                @php
                                                    $optionValue = is_array($option) ? data_get($option, 'value') : $option;
                                                    $optionLabel = is_array($option) ? data_get($option, 'label', $optionValue) : $option;
                                                @endphp
                                                <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($fieldType === 'checkbox')
                                        <div class="form-check form-switch mt-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="content_fields[{{ $fieldName }}]"
                                                value="1"
                                                {{ $fieldValue ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label">{{ $fieldLabel }}</label>
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            name="content_fields[{{ $fieldName }}]"
                                            value="{{ is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue }}"
                                            class="form-control"
                                            placeholder="{{ $fieldPlaceholder }}"
                                        >
                                    @endif

                                    @if(filled(data_get($field, 'help_text')))
                                        <div class="form-text">{{ data_get($field, 'help_text') }}</div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Extra Fields</h5>
                    <p class="text-muted mb-0 small">Optional key/value pairs for data that is not part of the main schema yet.</p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-extra-field">
                    Add Field
                </button>
            </div>
            <div class="card-body">
                <div class="vstack gap-3" id="extra-fields-list">
                    @foreach($extraContentFields as $index => $field)
                        <div class="border rounded-3 p-3 extra-field-item" data-extra-field>
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                                    <div class="fw-semibold">Extra Field <span data-extra-row-number>{{ $index + 1 }}</span></div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-extra-field>
                                        Delete
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Key</label>
                                    <input type="text" class="form-control" value="{{ data_get($field, 'key') }}" placeholder="custom_key" data-extra-input="key">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Value</label>
                                    <textarea rows="3" class="form-control" placeholder="Custom value" data-extra-input="value">{{ data_get($field, 'value') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="alert alert-light border mt-3 mb-0">
                    Use this for additional fields not yet defined in the schema. The main schema fields above remain the primary entry structure.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Advanced JSON</h5>
                    <p class="text-muted mb-0 small">Optional fallback for debugging or unsupported schema field types.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info-subtle text-info">Synced</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="apply-entry-json">
                        Apply JSON to Form
                    </button>
                </div>
            </div>
            <div class="card-body">
                <textarea id="data_json" name="data_json" rows="16" class="form-control font-monospace @error('data_json') is-invalid @enderror" spellcheck="false">{{ $dataJson }}</textarea>
                @error('data_json')
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
                    <input class="form-check-input" type="checkbox" name="is_published" value="1" {{ old('is_published', $entry->is_published) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ old('is_published', $entry->is_published) ? __('messages.published') : __('messages.unpublished') }}</label>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
                    <a href="{{ route('page-builder.content-types.entries.index', $contentType) }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Schema Reference</h5>
            </div>
            <div class="card-body">
                <pre class="mb-0 small bg-light rounded-3 p-3" style="max-height: 320px; overflow: auto;">{{ json_encode($contentType->schema ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
</div>

<template id="extra-field-template">
    <div class="border rounded-3 p-3 extra-field-item" data-extra-field>
        <div class="row g-3">
            <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                <div class="fw-semibold">Extra Field <span data-extra-row-number></span></div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-extra-field>
                    Delete
                </button>
            </div>
            <div class="col-md-4">
                <label class="form-label">Key</label>
                <input type="text" class="form-control" placeholder="custom_key" data-extra-input="key">
            </div>
            <div class="col-md-8">
                <label class="form-label">Value</label>
                <textarea rows="3" class="form-control" placeholder="Custom value" data-extra-input="value"></textarea>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jsonTextarea = document.getElementById('data_json');
        const applyJsonButton = document.getElementById('apply-entry-json');
        const extraList = document.getElementById('extra-fields-list');
        const addExtraButton = document.getElementById('add-extra-field');
        const extraTemplate = document.getElementById('extra-field-template');

        if (!jsonTextarea || !applyJsonButton || !extraList || !addExtraButton || !extraTemplate) {
            return;
        }

        const schemaFieldInputs = Array.from(document.querySelectorAll('[name^="content_fields["]'));

        const parsePossibleJson = (value) => {
            if (typeof value !== 'string') {
                return value;
            }

            const trimmed = value.trim();

            if (trimmed === '') {
                return '';
            }

            if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
                try {
                    return JSON.parse(trimmed);
                } catch (error) {
                    return trimmed;
                }
            }

            return trimmed;
        };

        const collectSchemaFieldData = () => {
            const data = {};

            schemaFieldInputs.forEach((input) => {
                const match = input.name.match(/^content_fields\[(.+)\]$/);

                if (!match) {
                    return;
                }

                const key = match[1];

                if (input.type === 'checkbox') {
                    if (input.checked) {
                        data[key] = 1;
                    }

                    return;
                }

                const rawValue = input.value ?? '';
                const parsedValue = parsePossibleJson(rawValue);

                if (parsedValue !== '') {
                    data[key] = parsedValue;
                }
            });

            return data;
        };

        const collectExtraFieldData = () => {
            const data = {};

            Array.from(extraList.querySelectorAll('[data-extra-field]')).forEach((row) => {
                const key = row.querySelector('[data-extra-input="key"]')?.value?.trim() ?? '';
                const rawValue = row.querySelector('[data-extra-input="value"]')?.value ?? '';
                const parsedValue = parsePossibleJson(rawValue);

                if (key !== '' && parsedValue !== '') {
                    data[key] = parsedValue;
                }
            });

            return data;
        };

        const syncJsonFromForm = () => {
            const payload = {
                ...collectSchemaFieldData(),
                ...collectExtraFieldData(),
            };

            jsonTextarea.value = JSON.stringify(payload, null, 2);
        };

        const updateExtraRowIndexes = () => {
            Array.from(extraList.querySelectorAll('[data-extra-field]')).forEach((row, index) => {
                row.querySelector('[data-extra-row-number]').textContent = String(index + 1);

                row.querySelectorAll('[data-extra-input]').forEach((input) => {
                    const key = input.getAttribute('data-extra-input');
                    input.setAttribute('name', `extra_content_fields[${index}][${key}]`);
                });
            });

            syncJsonFromForm();
        };

        const bindExtraRow = (row) => {
            row.querySelector('[data-remove-extra-field]')?.addEventListener('click', function () {
                row.remove();
                updateExtraRowIndexes();
            });

            row.querySelectorAll('[data-extra-input]').forEach((input) => {
                input.addEventListener('input', syncJsonFromForm);
                input.addEventListener('change', syncJsonFromForm);
            });
        };

        const addExtraRow = (field = { key: '', value: '' }) => {
            const fragment = extraTemplate.content.cloneNode(true);
            const row = fragment.querySelector('[data-extra-field]');
            row.querySelector('[data-extra-input="key"]').value = field.key ?? '';
            row.querySelector('[data-extra-input="value"]').value = typeof field.value === 'string'
                ? field.value
                : JSON.stringify(field.value ?? '', null, 2);
            extraList.appendChild(fragment);
            bindExtraRow(extraList.lastElementChild);
            updateExtraRowIndexes();
        };

        const applyJsonToForm = (parsed) => {
            const remaining = { ...parsed };

            schemaFieldInputs.forEach((input) => {
                const match = input.name.match(/^content_fields\[(.+)\]$/);

                if (!match) {
                    return;
                }

                const key = match[1];
                const value = remaining[key];

                if (typeof value === 'undefined') {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }

                    return;
                }

                if (input.type === 'checkbox') {
                    input.checked = Boolean(value);
                } else if (Array.isArray(value) || (value && typeof value === 'object')) {
                    input.value = JSON.stringify(value, null, 2);
                } else {
                    input.value = String(value);
                }

                delete remaining[key];
            });

            extraList.innerHTML = '';
            Object.entries(remaining).forEach(([key, value]) => {
                addExtraRow({
                    key,
                    value: Array.isArray(value) || (value && typeof value === 'object')
                        ? JSON.stringify(value, null, 2)
                        : String(value ?? ''),
                });
            });

            updateExtraRowIndexes();
            syncJsonFromForm();
        };

        schemaFieldInputs.forEach((input) => {
            input.addEventListener('input', syncJsonFromForm);
            input.addEventListener('change', syncJsonFromForm);
        });

        Array.from(extraList.querySelectorAll('[data-extra-field]')).forEach(bindExtraRow);
        updateExtraRowIndexes();

        addExtraButton.addEventListener('click', function () {
            addExtraRow();
            extraList.lastElementChild?.querySelector('[data-extra-input="key"]')?.focus();
        });

        applyJsonButton.addEventListener('click', function () {
            try {
                const parsed = JSON.parse(jsonTextarea.value || '{}');

                if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
                    throw new Error('Entry JSON must be an object.');
                }

                applyJsonToForm(parsed);
            } catch (error) {
                window.alert(error.message || 'Entry JSON is invalid.');
            }
        });

        syncJsonFromForm();
    });
</script>
