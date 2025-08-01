@extends('layouts.main')
@section('page-title')
    {{ __('Manage Payslip') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payslip') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="row">
        @if (Auth::user()->type != 'staff')
            <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                        <div class="row row-gap-4 align-items-end justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 ">
                                <div class="btn-box form-group mb-0">
                                    {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                                    {{ Form::select('month', $month, date('m'), ['class' => 'form-control ', 'id' => 'month']) }}

                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 ">
                                <div class="btn-box form-group mb-0">
                                    {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                                    {{ Form::select('year', $year, date('Y'), ['class' => 'form-control ']) }}

                                </div>
                            </div>
                            <div class="col-auto float-end mb-1">
                                <div class="d-flex">
                                    <a href="#" onclick="document.getElementById('payslip_form').submit(); return false;"
                                        data-title="{{ __('Generate Payslip') }}" data-bs-toggle="tooltip"
                                        title="{{ __('Generate Payslip') }}" data-original-title="{{ __('payslip') }}"
                                        class="btn btn-sm btn-primary me-2">
                                        <i class="ti ti-report-money"></i>
                                    </a>

                                    @stack('addButtonHook')
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form>
                        <div class="d-flex align-items-center justify-content-start mb-3">
                            <h5>{{ __('Find Employee Payslip') }}</h5>
                        </div>
                        <div class="row row-gap-4 align-items-end justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 ">
                                <div class="btn-box">
                                    <select class="form-control month_date " name="year" tabindex="-1"
                                        aria-hidden="true">
                                        <option value="--">--</option>
                                        @foreach ($month as $k => $mon)
                                            @php
                                                $selected = date('m') == $k ? 'selected' : '';
                                            @endphp
                                            <option value="{{ $k }}" {{ $selected }}>{{ $mon }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 ">
                                <div class="btn-box">
                                    {{ Form::select('year', $years, date('Y'), ['class' => 'form-control year_date ']) }}
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-render-column-cells">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee Id') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Payroll Type') }}</th>
                                    <th>{{ __('Salary') }}</th>
                                    <th>{{ __('Net Salary') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ basic-table ] end -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();

                if (month == '') {
                    month = '{{ date('m', strtotime('last month')) }}';
                    year = '{{ date('Y') }}';
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '{{ route('payslip.search_json') }}',
                    type: 'POST',
                    data: {
                        "datePicker": datePicker,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        var datatable_data = {
                            data: data
                        };

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {
                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;


                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="#" data-url="{{ url('payslip/pdf/') }}/' + id +
                                    '/' + datePicker +
                                    '" data-size="md-pdf"  data-ajax-popup="true" class="btn btn-primary" data-title="{{ __('Employee Payslip') }}">' +
                                    '{{ __('Payslip') }}' + '</a> ';
                            }

                            if (status == "UnPaid" && data != 0) {
                                clickToPaid = '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                    '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                                    '{{ __('Click To Paid') }}' + '</a>  ';
                            }

                            if (data != 0) {
                                view =
                                    '<a href="#" data-url="{{ url('payslip/showemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="{{ __('View Employee Detail') }}">' +
                                    '{{ __('View') }}' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" data-size="lg" class="view-btn blue-bg" data-title="{{ __('Edit Employee salary') }}">' +
                                    '{{ __('Edit') }}' + '</a>';
                            }

                            var url = '{{ route('payslip.delete', ':id') }}';
                            url = url.replace(':id', payslip_id);

                            @if (\Auth::user()->type == 'company')
                                if (valueOfElement[7] != 0) {
                                    var deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn btn btn-danger ms-1 btn-sm"  >' +
                                        '{{ __('Delete') }}' + '</a>';
                                }
                            @else
                                var deleted = '';
                            @endif

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {
                                var status =
                                    '<div class="badge bg-danger p-2 px-3 status-badde4"><a href="#" class="text-white">' +
                                    valueOfElement[6] + '</a></div>';
                                if (valueOfElement[6] == 'Paid') {
                                    var status =
                                        '<div class="badge bg-success p-2 px-3 status-badde4"><a href="#" class="text-white">' +
                                        valueOfElement[6] + '</a></div>';
                                }

                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[7];

                                if (valueOfElement[7] != 0) {
                                    var payslip =
                                        '<a href="#" data-url="{{ url('payslip/pdf/') }}/' +
                                        id +
                                        '/' + datePicker +
                                        '" data-size="lg" data-toggle="tooltip" title="{{ __("Payslip") }}" data-ajax-popup="true" class=" btn-sm btn btn-warning me-1" data-title="{{ __('Employee Payslip') }}">' +
                                        '<i class="ti ti-report-money"></i>' + '</a> ';
                                }
                                if (valueOfElement[6] == "UnPaid" && valueOfElement[7] != 0) {
                                    var clickToPaid =
                                        '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                        '/' + datePicker +
                                        '" data-toggle="tooltip" title="{{ __("Click To Paid") }}" class="btn-sm btn btn-primary me-1">' +
                                        '<i class="ti ti-currency-dollar"></i>' + '</a>  ';
                                } else {
                                    var clickToPaid = '';
                                }

                                if (valueOfElement[7] != 0 && valueOfElement[6] == "UnPaid") {
                                    var edit =
                                        '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                        payslip_id +
                                        '"  data-ajax-popup="true" data-toggle="tooltip" title="{{ __("Edit") }}" data-size="lg" class="btn-sm btn btn-info me-2" data-title="{{ __('Edit Employee salary') }}">' +
                                        '<i class="ti ti-pencil"></i>' + '</a>';
                                } else {
                                    var edit = '';
                                }


                                var url = '{{ route('payslip.delete', ':id') }}';
                                url = url.replace(':id', payslip_id);

                                @if (\Auth::user()->type != 'staff')
                                    if (valueOfElement[7] != 0) {
                                        var deleted = '<a href="#"  data-url="' + url +
                                            '" data-toggle="tooltip" title="{{ __("Delete") }}" class="payslip_delete view-btn btn btn-danger btn-sm"  >' +
                                            '<i class="ti ti-trash"></i>' + '</a>';
                                    }
                                @else
                                    var deleted = '';
                                @endif
                                var url_employee = valueOfElement['url'];
                                var currencySymbolPosition = @json($company_settings['site_currency_symbol_position']);
                                var currencySymbol = @json($company_settings['defult_currancy_symbol']);
                                var salary = currencySymbolPosition == 'pre' ?
                                    currencySymbol + valueOfElement[4] :
                                    valueOfElement[4] + currencySymbol;

                                var net_salary = currencySymbolPosition == 'pre' ?
                                    currencySymbol + valueOfElement[5] :
                                    valueOfElement[5] + currencySymbol;

                                tr +=
                                    '<tr> ' +
                                    '<td> <a class="btn btn-outline-primary" href="' +
                                    url_employee + '">' +
                                    valueOfElement[1] + '</a></td> ' +
                                    '<td>' + valueOfElement[2] + '</td> ' +
                                    '<td>' + valueOfElement[3] + '</td>' +
                                    '<td>' + salary + '</td>' +
                                    '<td>' + net_salary + '</td>' +
                                    '<td>' + status + '</td>' +
                                    '<td>' + payslip + clickToPaid + edit + deleted + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            var tr = '<tr><td class="dataTables-empty" colspan="' + colspan +
                                '">{{ __('No entries found') }}</td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);
                        var table = document.querySelector("#pc-dt-render-column-cells");
                        var datatable = new simpleDatatables.DataTable(table);
                    },
                    error: function(data) {

                    }

                });

            }

            $(document).on("change", ".month_date,.year_date", function() {
                callback();
            });

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');
                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        beforeSend: function() {
                            $(".loader-wrapper").removeClass('d-none');
                        },
                        success: function(data) {
                            toastrs('Success', 'Payslip Deleted Successfully', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 800)
                            $(".loader-wrapper").addClass('d-none');
                        },

                    });
                }
            });
        });
    </script>
@endpush
