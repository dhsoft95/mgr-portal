<x-filament-widgets::widget>
    <x-filament::section>
        <div class="progress">
            @foreach ($data['progressBars'] as $bar)
                <div class="progress-bar {{ $bar['class'] }}" role="progressbar" style="width: {{ $bar['value'] }}%" aria-valuenow="{{ $bar['value'] }}" aria-valuemin="0" aria-valuemax="100"></div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
