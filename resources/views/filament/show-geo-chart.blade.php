<!-- resources/views/show-geo-chart.blade.php -->

@extends('filament::layouts.app') <!-- Assuming you have a Filament layout -->

@section('content')
    <div>
        {!! (new \App\Filament\Widgets\GeoChartWidget())->render() !!}
    </div>
@endsection

@push('scripts')
    <!-- Include Chart.js -->
    <script src="{{ asset('js/app.js') }}"></script>
@endpush
