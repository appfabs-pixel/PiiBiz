@extends('layouts.main')
@section('page-title')
    {{ __('Edit Employee') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if (!empty($employee))
                        {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate' ,'id'=>'personal-details-form']) }}
                    @else
                        {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate','id'=>'personal-details-form']) }}
                    @endif
                    @if (!empty($user->id))
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                    @endif
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="personal-details-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>{{ __('Personal Details') }}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                                            <div class="form-icon-user">
                                                {{ Form::text('name', !empty($user->name) ? $user->name : '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter employee Name')]) }}
                                            </div>
                                        </div>
                                        <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}" value="{{!empty($user->mobile_no) ? $user->mobile_no : null}}"
                                            placeholder="{{ __('Enter employee phone') }}" id="phone" required>
                                        </x-mobile>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<x-required></x-required>
                                            {!! Form::date('dob', null, [
                                                'class' => 'form-control ',
                                                'required' => 'required',
                                                'placeholder' => __('Select Date of Birth'),
                                                'max' => date('Y-m-d'),
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<x-required></x-required>
                                            <div class="d-flex radio-check">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        id="g_male" value="Male"
                                                        {{ !empty($employee) ? ($employee->gender == 'Male' ? 'checked' : '') : 'checked' }}>
                                                    <label class="form-check-label"
                                                        for="g_male">{{ __('Male') }}</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        id="g_female" value="Female"
                                                        {{ !empty($employee) ? ($employee->gender == 'Female' ? 'checked' : '') : '' }}>
                                                    <label class="form-check-label"
                                                        for="g_female">{{ __('Female') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('passport_country', __('Passport country'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('passport_country', null, ['class' => 'form-control', 'placeholder' => __('Enter Passport Country')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('passport', __('Passport'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('passport', null, ['class' => 'form-control', 'placeholder' => __('Enter Passport')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h5>{{ __('Location Details') }}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {{ Form::label('location_type', __('Location Type'), ['class' => 'form-label']) }}
                                            {{ Form::select('location_type', $location_type, null, ['class' => 'form-control select']) }}
                                        </div>

                                        <div class="form-group col-md-6">
                                            {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter State')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter City')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('zipcode', __('Zip code'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::text('zipcode', null, ['class' => 'form-control', 'placeholder' => __('Enter Zip code')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<x-required></x-required>
                                            {!! Form::textarea('address', null, [
                                                'class' => 'form-control',
                                                'rows' => 3,
                                                'placeholder' => __('Enter employee address'),
                                                'required' => 'required',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (\Auth::user()->type != 'employee')
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h5>{{ __('Document') }}</h5>
                                        <hr>
                                        <div class="card-body employee-detail-create-body p-0">
                                            @foreach ($document_types as $key => $document)
                                                <div class="row">
                                                    <div class="form-group col-12 d-flex">
                                                        <div class="float-left col-9">
                                                            {{ Form::label('document', $document->name, ['class' => 'float-left pt-1 form-label']) }}
                                                            @if ($document->is_required == 1)
                                                                <x-required></x-required>
                                                            @endif
                                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]"
                                                            value="{{ $document->id }}">
                                                        @php
                                                            $employeedoc = !empty($employee->documents) ? $employee->documents()->pluck('document_value', 'document_id'): [];
                                                        @endphp
                                                        <div class="choose-file form-group">
                                                            <label for="document[{{ $document->id }}]"
                                                                class="form-label d-block">
                                                                <input type="file"
                                                                    class="form-control file @error('document') is-invalid @enderror doc_data"
                                                                    name="document[{{ $document->id }}]"
                                                                    id="document[{{ $document->id }}]"
                                                                    data-filename="{{ $document->id . '_filename' }}"

                                                                    @if ($document->is_required == 1 && empty($employeedoc[$document->id])) data-key="{{ $key }}" required @endif

                                                                    onchange="previewFile(this, '{{ $key }}')">
                                                                <hr>
                                                            </label>
                                                        </div>
                                                        </div>
                                                        <div class="float-right col-3">
                                                            @php
                                                                $docPath = isset($employeedoc[$document->id]) ? $employeedoc[$document->id] : null;
                                                                $ext = $docPath ? strtolower(pathinfo($docPath, PATHINFO_EXTENSION)) : null;
                                                            @endphp
                                                            @if ($docPath && strtolower($ext) === 'pdf')
                                                                <img id="preview_{{ $key }}" style="margin-left: 15px;"
                                                                    src="{{ asset('images/folder.png') }}"
                                                                    width="120" height="100" class="rounded" />
                                                            @elseif ($docPath)
                                                                <img id="preview_{{ $key }}" style="margin-left: 15px;"
                                                                    src="{{ get_file($docPath) }}"
                                                                    width="120" height="100" class="rounded" />
                                                            @else
                                                                <img id="preview_{{ $key }}" style="margin-left: 15px;"
                                                                    src="{{ asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}"
                                                                    width="120" height="100" class="rounded" />
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <h5>{{ __('Bank Account Detail') }}</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                                {!! Form::text('account_holder_name', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter Account Holder Name'),
                                                ]) !!}

                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                                {!! Form::number('account_number', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter Account Number'),
                                                ]) !!}

                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                                {!! Form::text('bank_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Bank Name')]) !!}

                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                                                {!! Form::text('bank_identifier_code', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter Bank Identifier Code'),
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                                {!! Form::text('branch_location', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter Branch Location'),
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                                {!! Form::text('tax_payer_id', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('Enter Tax Payer Id'),
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="employee-detail-wrap">
                                            <div class="card em-card">
                                                <div class="card-header">
                                                    <h6>{{ __('Document Detail') }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        @php
                                                            $employeedoc = $employee
                                                                ->documents()
                                                                ->pluck('document_value', __('document_id'));
                                                        @endphp
                                                        @foreach ($document_types as $key => $document)
                                                            <div class="col-md-12">
                                                                <div class="info">
                                                                    <strong>{{ $document->name . ' :' }}</strong>
                                                                    <span><a href="{{ !empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')) . '/' . $employeedoc[$document->id] : '' }}"
                                                                            target="_blank">{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}</a></span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="employee-detail-wrap">
                                            <div class="card em-card">
                                                <div class="card-header">
                                                    <h6>{{ __('Bank Account Detail') }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ __('Account Holder Name') }}</strong>
                                                                <span>{{ $employee->account_holder_name }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info font-style">
                                                                <strong>{{ __('Account Number') }}</strong>
                                                                <span>{{ $employee->account_number }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info font-style">
                                                                <strong>{{ __('Bank Name') }}</strong>
                                                                <span>{{ $employee->bank_name }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ __('Bank Identifier Code') }}</strong>
                                                                <span>{{ $employee->bank_identifier_code }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ __('Branch Location') }}</strong>
                                                                <span>{{ $employee->branch_location }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ __('Tax Payer Id') }}</strong>
                                                                <span>{{ $employee->tax_payer_id }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col"></div>
                                <div class="col-6 text-end">
                                    <button class="btn btn-primary d-inline-flex align-items-center" type="submit" id="next-tab">{{ __('Next') }}<i
                                            class="ti ti-chevron-right ms-2"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade active" id="company-tab" role="tabpanel" aria-labelledby="pills-user-tab-2">
                        </div>
                    </div>
                    {{ Form::close() }}
                    <div class="source-div d-none">
                            <div class="row">
                                @if (\Auth::user()->type != 'employee')
                                    <div class="col-sm-6">
                                        <h5>{{ __('Company Detail') }}</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="form-group">
                                                {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                                {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::select('branch_id', $branches, isset($employee->branch_id) ? $employee->branch_id : null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('select Branch')))]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('department_id', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::select('department_id',$departments, isset($employee->department_id) ? $employee->department_id : null, ['class' => 'form-control', 'id' => 'department_id', 'required' => 'required', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department')))]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('designation_id', !empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::select('designation_id',$designations, isset($employee->designation_id) ? $employee->designation_id : null, ['class' => 'form-control', 'id' => 'designation_id', 'required' => 'required', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation')))]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('company_doj', 'Company Date Of Joining', ['class' => 'form-label']) !!}<x-required></x-required>
                                                {!! Form::date('company_doj', isset($employee->company_doj) ? $employee->company_doj : null, [
                                                    'class' => 'form-control',
                                                    'required' => 'required',
                                                    'placeholder' => __('Select Date Of Joining'),
                                                ]) !!}

                                            </div>

                                            {{-- Biometric Attendance Emp Field --}}
                                            @stack('biometric_emp_id')
                                            @if (module_is_active('CustomField') && !$customFields->isEmpty())
                                                <div class="col-md-12">
                                                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                                        @include('custom-field::formBuilder', [
                                                            'fildedata' => isset($employee->customField)
                                                                ? $employee->customField
                                                                : '',
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-6 ">
                                        <div class="employee-detail-wrap ">
                                            <div class="card em-card">
                                                <div class="card-header">
                                                    <h5>{{ __('Company Detail') }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}</strong>
                                                                <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info font-style">
                                                                <strong>{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}</strong>
                                                                <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info font-style">
                                                                <strong>{{ !empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation') }}</strong>
                                                                <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info">
                                                                <strong>{{ __('Date Of Joining') }}</strong>
                                                                <span>{{ company_date_formate($employee->company_doj) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <h5>{{ __('Hours and Rates Detail') }}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>{{ __('Hours') }}</h6>
                                            <hr>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>{{ __('Rates') }}</h6>
                                            <hr>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('hours_per_day', __('Hours Per day'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('hours_per_day', isset($employee->hours_per_day) ? $employee->hours_per_day : null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Hours Per Day')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('annual_salary', __('Annual salary'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('annual_salary', isset($employee->annual_salary) ? $employee->annual_salary : null, ['class' => 'form-control', 'placeholder' => __('Enter Annual Salary')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('days_per_week', __('Days Per week'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('days_per_week', isset($employee->days_per_week) ? $employee->days_per_week : null, ['class' => 'form-control', 'placeholder' => __('Enter Days Per Week')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('fixed_salary', __('Fixed Salary'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('fixed_salary', isset($employee->fixed_salary) ? $employee->fixed_salary : null, ['class' => 'form-control', 'placeholder' => __('Enter Fixed Salary')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('hours_per_month', __('Hours Per month'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('hours_per_month', isset($employee->hours_per_month) ? $employee->hours_per_month : null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Hours Per Month')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('rate_per_day', __('Rate per day'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('rate_per_day', isset($employee->rate_per_day) ? $employee->rate_per_day : null, ['class' => 'form-control', 'placeholder' => __('Enter Rate Per Day')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('days_per_month', __('Days per month'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('days_per_month', isset($employee->days_per_month) ? $employee->days_per_month : null, ['class' => 'form-control', 'placeholder' => __('Enter Days Per Month')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('rate_per_hour', __('Rate per hour'), ['class' => 'form-label']) }}
                                            <div class="form-icon-user">
                                                {{ Form::number('rate_per_hour', isset($employee->rate_per_hour) ? $employee->rate_per_hour : null, ['class' => 'form-control','step' => '0.01', 'placeholder' => __('Enter Rate Per Hour')]) }}
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    id="payment_requires_work_advice" name="payment_requires_work_advice"
                                                    {{ !empty($employee) ? ($employee->payment_requires_work_advice == 'on' ? 'checked' : '') : '' }}>
                                                <label class="form-check-label"
                                                    for="payment_requires_work_advice">{{ __('This employee must not be paid unless hours or days worked are advised') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary d-inline-flex align-items-center"
                                        id="previous-tab" type="button"><i
                                            class="ti ti-chevron-left me-2"></i>{{ __('Previous') }}</button>
                                </div>
                                <div class="col-6 text-end" id="savebutton">
                                    <a class="btn btn-secondary btn-submit me-1"
                                        href="{{ route('employee.index') }}">{{ __('Cancel') }}</a>
                                    <button class="btn btn-primary btn-submit" type="submit"
                                        id="submit">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>

    <script type="text/javascript">
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('hrm.employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append(
                        '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('hrm.employee.getdesignation') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    </script>

    <script>
        $("#submit").click(function() {
            $(".doc_data").each(function() {
                if (!isNaN(this.value)) {
                    var id = '#doc_validation-' + $(this).data("key");
                    $(id).removeClass('d-none')
                    return false;
                }
            });
        });
    </script>

        <script>
            // personal-details
            $(document).on('click', '#next-tab', function(event) {
                var forms = document.querySelector('#personal-details-form');
                if (forms) {
                    if (forms.checkValidity() == true) {
                        event.preventDefault();
                        event.stopPropagation();

                        var content = $('.source-div').html();
                        $('#company-tab').html(content);
                        $('.source-div').empty();
                        $('#personal-details-tab').removeClass('show active');
                        $('#company-tab').addClass('show active');

                    }
                }
                forms.classList.add('was-validated');
            });
            $(document).on('click', '#previous-tab', function() {
                var content = $('#company-tab').html();
                    $('.source-div').html(content);
                    $('#company-tab').empty();
                    // changetab('#personal-details-tab');
                    $('#company-tab').removeClass('show active');
                    $('#personal-details-tab').addClass('show active');
            });
        </script>
        <script>
            function previewFile(input, key) {
                const file = input.files[0];
                const preview = document.getElementById('preview_' + key);

                if (!file) return;

                const fileType = file.type;

                if (fileType === 'application/pdf') {
                    preview.src = "{{ asset('images/folder.png') }}";
                } else if (fileType.startsWith('image/')) {
                    preview.src = URL.createObjectURL(file);
                } else {
                    preview.src = "{{ asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}";
                }
            }
        </script>
@endpush
