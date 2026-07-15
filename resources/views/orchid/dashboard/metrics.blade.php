<div class="row g-3 mb-4">
    @foreach ($metrics as $metric)
        <div class="col-md-4 col-lg-3">
            <div class="bg-white rounded p-3 h-100 border-start border-4 border-{{ $metric['color'] ?? 'primary' }}">
                <div class="text-muted small text-uppercase">{{ $metric['label'] }}</div>
                <div class="fs-3 fw-bold">{{ $metric['value'] }}</div>
                @if (! empty($metric['description']))
                    <div class="text-muted small">{{ $metric['description'] }}</div>
                @endif
            </div>
        </div>
    @endforeach
</div>
