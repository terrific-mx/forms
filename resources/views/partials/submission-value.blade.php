@if (is_array($value))
    <div class="space-y-1">
        @foreach ($value as $item)
            <flux:text variant="strong" class="block">{{ $item }}</flux:text>
        @endforeach
    </div>
@elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL))
    <flux:text>
        <flux:link href="mailto:{{ $value }}" variant="strong">{{ $value }}</flux:link>
    </flux:text>
@elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
    <flux:text>
        <flux:link href="{{ $value }}" target="_blank" rel="noopener noreferrer" variant="strong" class="break-all">{{ $value }}</flux:link>
    </flux:text>
@elseif (is_string($value) && strlen($value) > 100)
    <flux:text variant="strong" class="whitespace-pre-wrap break-words">{{ $value }}</flux:text>
@else
    <flux:text variant="strong">{{ $value ?? 'N/A' }}</flux:text>
@endif
