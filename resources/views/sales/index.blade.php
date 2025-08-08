@extends('layouts.main')

@section('page-title')
    {{ __('Sales') }}
@endsection

@section('page-breadcrumb')
    {{ __('Sales') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Upload Sales Data')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="sales_file" class="form-label">{{__('Choose File')}}</label>
                            <input class="form-control" type="file" id="sales_file" name="sales_file">
                        </div>
                        <button type="submit" class="btn btn-primary">{{__('Upload')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($mergedSalesData) && $mergedSalesData->count() > 0)
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('All Sales Data')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{__('ID')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Gross Amount')}}</th>
                                        <th>{{__('Net Amount')}}</th>
                                        <th>{{__('Source')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mergedSalesData as $row)
                                        <tr>
                                            <td>{{ $row['ID'] }}</td>
                                            <td>{{ $row['Date'] }}</td>
                                            <td>{{ $row['Status'] }}</td>
                                            <td>{{ $row['Gross Amount'] }}</td>
                                            <td>{{ $row['Net Amount'] }}</td>
                                            <td>{{ $row['Source'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="alert alert-info" role="alert">
                    {{ __('No sales data available.') }}
                </div>
            </div>
        </div>
    @endif
@endsection
