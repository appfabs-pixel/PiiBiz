@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Butchery Inventory Dashboard') }}
@endsection
@push('css')
    <style>
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
        }
    </style>
@endpush
@section('content')
    {{-- Cards row --}}
    <div class="row">
        <div class="col-lg-2 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Purchases</h5>
                    <h3>{{ $totalPurchases }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Stock</h5>
                    <h3>{{ $totalStock }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Production</h5>
                    <h3>{{ $totalProduction }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sales</h5>
                    <h3>{{ $totalSales }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Wastage</h5>
                    <h3>{{ $totalWastage }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Reused</h5>
                    <h3>{{ $Reusable }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphs row --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Production</h5>
                    <canvas id="productionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sales</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Wastage</h5>
                    <canvas id="wastageChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Purchases</h5> {{-- Renamed --}}
                    <canvas id="purchasesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var productionCtx = document.getElementById('productionChart').getContext('2d');
            var salesCtx = document.getElementById('salesChart').getContext('2d');
            var wastageCtx = document.getElementById('wastageChart').getContext('2d');
            var purchasesCtx = document.getElementById('purchasesChart').getContext('2d');

            new Chart(productionCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Production',
                        data: {!! json_encode($productionData) !!},
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 1
                    }]
                }
            });

            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Sales',
                        data: {!! json_encode($salesData) !!},
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 1
                    }]
                }
            });

            new Chart(wastageCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Wastage',
                        data: {!! json_encode($wastageData) !!},
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 1
                    }]
                }
            });

            new Chart(purchasesCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Purchases', // Changed label
                        data: {!! json_encode($purchasesData) !!},
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 1
                    }]
                }
            });
        });
    </script>
@endpush
