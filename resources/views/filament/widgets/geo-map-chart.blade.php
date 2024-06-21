<!-- resources/views/filament/widgets/geo-chart.blade.php -->
<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <h1>GeoChart Widget</h1>
            <canvas id="geoChartCanvas" width="400" height="400"></canvas>
        </div>
    </x-filament::section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ctx = document.getElementById('geoChartCanvas').getContext('2d');
                var data = @json($chartData);

                var geoChart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-widgets::widget>
<!-- resources/views/filament/widgets/geo-chart.blade.php -->
<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <h1>GeoChart Widget</h1>
            <canvas id="geoChartCanvas" width="400" height="400"></canvas>
        </div>
    </x-filament::section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ctx = document.getElementById('geoChartCanvas').getContext('2d');
                var data = @json($chartData);

                var geoChart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-widgets::widget>
