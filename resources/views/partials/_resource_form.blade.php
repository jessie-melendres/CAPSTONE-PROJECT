{{-- Generic create/edit form. $action, $method ('POST'|'PUT'), $fields = [['name','label','type','value'?,'options'?,'step'?,'required'?]], $submitLabel --}}
<form method="POST" action="{{ $action }}" class="form-grid">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    @foreach ($fields as $field)
        <label>
            {{ $field['label'] }}
            @php($old = old($field['name'], $field['value'] ?? ''))
            @if (($field['type'] ?? 'text') === 'select')
                <select name="{{ $field['name'] }}" @if($field['required'] ?? false) required @endif>
                    <option value="">-- Select --</option>
                    @foreach ($field['options'] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $old === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @elseif (($field['type'] ?? 'text') === 'textarea')
                <textarea name="{{ $field['name'] }}" rows="3" @if($field['required'] ?? false) required @endif>{{ $old }}</textarea>
            @else
                <input
                    type="{{ $field['type'] ?? 'text' }}"
                    name="{{ $field['name'] }}"
                    value="{{ $old }}"
                    @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                    @if($field['required'] ?? false) required @endif
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                >
            @endif
        </label>
    @endforeach
    <div style="align-self:end;">
        <button type="submit" class="primary-btn">{{ $submitLabel ?? 'Save' }}</button>
    </div>
</form>
