<flux:tooltip toggleable>
    <flux:button
        icon="information-circle"
        size="{{ $size ?? 'xs' }}"
        variant="ghost"
    />
    <flux:tooltip.content class="max-w-[20rem] space-y-2">
        @if ($submission->ip_address)
            <p><strong>{{ __('IP:') }}</strong> {{ $submission->ip_address }}</p>
        @endif
        @if ($submission->user_agent)
            <p><strong>{{ __('Browser:') }}</strong> {{ $submission->user_agent }}</p>
        @endif
        @if ($submission->referrer)
            <p><strong>{{ __('From:') }}</strong> <a href="{{ $submission->referrer }}" target="_blank" rel="noopener noreferrer" class="underline">{{ $submission->referrer }}</a></p>
        @endif
    </flux:tooltip.content>
</flux:tooltip>
