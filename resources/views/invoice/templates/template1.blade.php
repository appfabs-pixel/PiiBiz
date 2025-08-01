<!DOCTYPE html>
<html lang="en" dir="{{ $settings['site_rtl'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }}
        |
        {{ !empty(company_setting('title_text', $invoice->created_by, $invoice->workspace)) ? company_setting('title_text', $invoice->created_by, $invoice->workspace) : (!empty(admin_setting('title_text')) ? admin_setting('title_text') : 'WorkDo') }}
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <style type="text/css">
        :root {
            --theme-color: {{ $color }};
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Lato', sans-serif;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 12px;
        }

        .invoice-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .invoice-logo {
            max-width: 200px;
            width: 100%;
        }

        .invoice-header table td {
            padding: 15px 30px;
        }

        .text-right {
            text-align: right;
        }

        .no-space tr td {
            padding: 0;
            white-space: nowrap;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .view-qrcode {
            max-width: 139px;
            height: 139px;
            width: 100%;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white);
            padding: 13px;
            border-radius: 10px;
        }

        .view-qrcode img {
            width: 100%;
            height: 100%;
        }

        .invoice-body {
            padding: 30px 25px 0;
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color);
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color);
        }

        .total-table tr:first-of-type td {
            padding-top: 0;
        }

        .total-table tr:first-of-type {
            border-top: 0;
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0;
        }

        .border-0 {
            border: none !important;
        }

        .invoice-summary td,
        .invoice-summary th {
            font-size: 13px;
            font-weight: 600;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        .invoice-footer {
            padding: 15px 20px;
        }

        .itm-description td {
            padding-top: 0;
        }

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {
            text-align: right;
        }

        html[dir="rtl"] .text-right {
            text-align: left;
        }

        html[dir="rtl"] .view-qrcode {
            margin-left: 0;
            margin-right: auto;
        }

        p:not(:last-of-type) {
            margin-bottom: 15px;
        }

        .invoice-summary p {
            margin-bottom: 0;
        }

        .wid-75 {
            width: 75px;
        }
    </style>
</head>

<body>
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="background-color: var(--theme-color); color: {{ $font_color }};">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <img class="invoice-logo" src="{{ $img }}" alt="">
                        </td>
                        <td class="text-right">
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold; ">
                                {{ __('INVOICE') }}</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                @if (!empty($settings['company_name']))
                                    {{ $settings['company_name'] }}
                                @endif
                                <br>
                                @if (!empty($settings['company_email']))
                                    {{ $settings['company_email'] }}
                                @endif
                                <br>
                                @if (!empty($settings['company_telephone']))
                                    {{ $settings['company_telephone'] }}
                                @endif
                                <br>
                                @if (!empty($settings['company_address']))
                                    {{ $settings['company_address'] }}
                                @endif
                                @if (!empty($settings['company_city']))
                                    <br> {{ $settings['company_city'] }},
                                @endif
                                @if (!empty($settings['company_state']))
                                    {{ $settings['company_state'] }}
                                @endif
                                @if (!empty($settings['company_country']))
                                    <br>{{ $settings['company_country'] }}
                                @endif
                                @if (!empty($settings['company_zipcode']))
                                    - {{ $settings['company_zipcode'] }}
                                @endif
                                <br>
                                @if (!empty($settings['registration_number']))
                                    {{ __('Registration Number') }} : {{ $settings['registration_number'] }}
                                @endif
                                <br>
                                @if (!empty($settings['tax_type']) && !empty($settings['vat_number']))
                                    {{ $settings['tax_type'] . ' ' . __('Number') }} : {{ $settings['vat_number'] }}
                                    <br>
                                @endif
                            </p>
                        </td>
                        <td style="width: 60%;">
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number: ') }}</td>
                                        <td class="text-right">
                                            {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date:') }}</td>
                                        <td class="text-right">
                                            {{ company_date_formate($invoice->issue_date, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Due Date') }}:</td>
                                        <td class="text-right">
                                            {{ company_date_formate($invoice->due_date, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    @if (!empty($customFields) && count($invoice->customField) > 0)
                                        @foreach ($customFields as $field)
                                            <tr>
                                                <td>{{ $field->name }}:</td>
                                                <td class="text-right" style="white-space: normal;">
                                                    @if ($field->type == 'attachment')
                                                        <a href="{{ get_file($invoice->customField[$field->id]) }}"
                                                            target="_blank">
                                                            <img src=" {{ get_file($invoice->customField[$field->id]) }} "
                                                                class="wid-75 rounded me-3">
                                                        </a>
                                                    @else
                                                        <p>{{ !empty($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}
                                                        </p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($settings['invoice_qr_display']) && $settings['invoice_qr_display'] == 'on')
                                        <tr>
                                            @if (module_is_active('Zatca', $invoice->created_by))
                                                <td colspan="2">
                                                    <div class="view-qrcode">
                                                        @include('zatca::zatca_qr_code', [
                                                            'invoice_id' => $invoice->invoice_id,
                                                        ])
                                                    </div>
                                                </td>
                                            @else
                                                <td colspan="2">
                                                    <div class="view-qrcode">
                                                        {!! DNS2D::getBarcodeHTML(
                                                            route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                                            'QRCODE',
                                                            2,
                                                            2,
                                                        ) !!}
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="invoice-body">
            <table>
                <tbody>
                    @if ($invoice->invoice_module != 'Fleet')
                        <tr>
                            <td>
                                <p class="mb-3">
                                    <strong class="h5 mb-1">{{ __('Name ') }}:
                                    </strong>{{ !empty($customer->name) ? $customer->name : '' }}
                                </p>
                                @if (!empty($customer->billing_name) && !empty($customer->billing_address) && !empty($customer->billing_zip))
                                    <strong style="margin-bottom: 10px; display:block;">{{ __('Bill To') }}:</strong>
                                    <p>
                                        {{ !empty($customer->billing_name) ? $customer->billing_name : '' }}<br>
                                        {{ !empty($customer->billing_address) ? $customer->billing_address : '' }}<br>
                                        {{ !empty($customer->billing_city) ? $customer->billing_city . ' ,' : '' }}
                                        {{ !empty($customer->billing_state) ? $customer->billing_state . ' ,' : '' }}
                                        {{ !empty($customer->billing_zip) ? $customer->billing_zip : '' }}<br>
                                        {{ !empty($customer->billing_country) ? $customer->billing_country : '' }}<br>
                                        {{ !empty($customer->billing_phone) ? $customer->billing_phone : '' }}<br>
                                    </p>
                                @endif
                            </td>
                            <td class="text-right">
                                <p class="mb-3">
                                    <strong class="h5 mb-1">{{ __('Email ') }}:
                                    </strong>{{ !empty($customer->email) ? $customer->email : '' }}
                                </p>
                                @if ($settings['shipping_display'] == 'on')
                                    @if (!empty($customer->shipping_name) && !empty($customer->shipping_address) && !empty($customer->shipping_zip))
                                        <strong
                                            style="margin-bottom: 10px; display:block;">{{ __('Ship To') }}:</strong>
                                        <p>
                                            {{ !empty($customer->shipping_name) ? $customer->shipping_name : '' }}<br>
                                            {{ !empty($customer->shipping_address) ? $customer->shipping_address : '' }}<br>
                                            {{ !empty($customer->shipping_city) ? $customer->shipping_city . ' ,' : '' }}
                                            {{ !empty($customer->shipping_state) ? $customer->shipping_state . ' ,' : '' }}
                                            {{ !empty($customer->shipping_zip) ? $customer->shipping_zip : '' }}<br>
                                            {{ !empty($customer->shipping_country) ? $customer->shipping_country : '' }}<br>
                                            {{ !empty($customer->shipping_phone) ? $customer->shipping_phone : '' }}<br>
                                        </p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endif

                    @if ($invoice->invoice_module == 'Fleet')
                        <tr>
                            <td style="font-size: 13px;">
                                <label class="form-label" for="customer_name"
                                    class="form-label">{{ __('Name : ') }}</label>
                                {{ $commonCustomer['name'] }}
                            </td>
                            <td style="font-size: 13px;">
                                <label class="form-label" for="customer_name"
                                    class="form-label">{{ __('Email : ') }}</label>
                                {{ $commonCustomer['email'] }}
                            </td>
                        </tr>
                    @endif
                    @if ($invoice->invoice_module == 'sales')
                        <tr>
                            <td>
                                <strong style="margin-bottom: 10px; display:block;">{{ __('Bill To') }}:</strong>
                                <p>
                                    {{ !empty($sales_invoice->contacts->name ?? '') ? $sales_invoice->contacts->name ?? '' : '' }}<br>
                                    {{ !empty($sales_invoice->contacts->email ?? '') ? $sales_invoice->contacts->email ?? '' : '' }}<br>
                                    {{ !empty($sales_invoice->contacts->phone ?? '') ? $sales_invoice->contacts->phone ?? '' : '' }}<br>
                                    {{ !empty($sales_invoice->billing_address) ? $sales_invoice->billing_address : '' }}<br>
                                    {{ !empty($sales_invoice->billing_city) ? $sales_invoice->billing_city : '' . ', ' }}
                                    {{ !empty($sales_invoice->billing_state) ? $sales_invoice->billing_state : '' }},{{ !empty($sales_invoice->billing_country) ? $sales_invoice->billing_country : '' }}<br>
                                    {{ !empty($sales_invoice->billing_postalcode) ? $sales_invoice->billing_postalcode : '' }}
                                </p>
                            </td>
                            @if ($settings['shipping_display'] == 'on')
                                <td class="text-right">
                                    <strong style="margin-bottom: 10px; display:block;">{{ __('Ship To') }}:</strong>
                                    <p>
                                        {{ !empty($sales_invoice->shipping_contacts->name ?? '') ? $sales_invoice->shipping_contacts->name ?? '' : '' }}<br>
                                        {{ !empty($sales_invoice->shipping_contacts->email ?? '') ? $sales_invoice->shipping_contacts->email ?? '' : '' }}<br>
                                        {{ !empty($sales_invoice->shipping_contacts->phone ?? '') ? $sales_invoice->shipping_contacts->phone ?? '' : '' }}<br>
                                        {{ !empty($sales_invoice->shipping_address) ? $sales_invoice->shipping_address : '' }}<br>
                                        {{ !empty($sales_invoice->shipping_city) ? $sales_invoice->shipping_city : '' . ', ' }},{{ !empty($sales_invoice->shipping_state) ? $sales_invoice->shipping_state : '' }},{{ !empty($sales_invoice->shipping_country) ? $sales_invoice->shipping_country : '' }}<br>
                                        {{ !empty($sales_invoice->shipping_postalcode) ? $sales_invoice->shipping_postalcode : '' }}
                                    </p>
                                </td>
                            @endif
                        </tr>
                    @endif
                </tbody>
            </table>
            <table class="add-border invoice-summary" style="margin-top: 30px;">
                <thead style="background-color: var(--theme-color);color: {{ $font_color }};">
                    <tr>
                        @if ($invoice->invoice_module == 'account')
                            <th>{{ __('Item Type') }}</th>
                        @endif
                        @if ($invoice->invoice_module == 'Fleet')
                            <th>{{ __('Distance') }}</th>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            <th>{{ __('Item') }}</th>
                            <th>{{ __('Quantity') }}</th>
                        @endif
                        <th>{{ __('Rate') }}</th>
                        @if ($invoice->invoice_module == 'Fleet')
                            <th>{{ __('Discription') }}</th>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Tax') }} (%)</th>
                        @endif
                        <th>{{ __('Price') }}<small>{{ __('After discount & tax') }}</small></th>

                    </tr>
                </thead>
                <tbody>
                    @if (isset($invoice->itemData) && count($invoice->itemData) > 0)
                        @foreach ($invoice->itemData as $key => $item)
                            <tr>
                                @if ($invoice->invoice_module == 'account')
                                    <td>{{ !empty($item->product_type) ? Str::ucfirst($item->product_type) : '--' }}
                                    </td>
                                @endif
                                <td>{{ $item->name }}</td>
                                @if ($invoice->invoice_module != 'Fleet')
                                    <td>{{ $item->quantity }}</td>
                                @endif
                                <td>{{ currency_format_with_sym($item->price, $invoice->created_by, $invoice->workspace) }}
                                </td>
                                @if ($invoice->invoice_module == 'Fleet')
                                    <th>{{ $item->description }}</th>
                                @endif
                                @if ($invoice->invoice_module != 'Fleet')
                                    <td>{{ $item->discount != 0 ? currency_format_with_sym($item->discount, $invoice->created_by, $invoice->workspace) : '-' }}
                                    </td>
                                    <td>
                                        @if (!empty($item->itemTax))
                                            @foreach ($item->itemTax as $taxes)
                                                <span>{{ $taxes['name'] }} </span><span> ({{ $taxes['rate'] }})
                                                </span>
                                                <span>{{ $taxes['price'] }}</span>
                                            @endforeach
                                        @else
                                            <p>-</p>
                                        @endif
                                    </td>
                                @endif

                                @if ($invoice->invoice_module == 'Fleet')
                                    @php
                                        $distance = !empty($item->name) ? $item->name : 0;
                                        $price = $item->price * $item->name;
                                    @endphp
                                    <td>{{ currency_format_with_sym($price, $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                @else
                                    <td>{{ currency_format_with_sym($item->price * $item->quantity - $item->discount + (isset($item->tax_price) ? $item->tax_price : 0), $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                @endif
                                @if ($invoice->invoice_module != 'Fleet')
                                    @if ($item->description != null)
                            <tr class="border-0 itm-description ">
                                <td colspan="6">{{ $item->description }} </td>
                            </tr>
                        @endif
                    @endif
                    @endforeach
                @else
                    <tr>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            <p>-</p>
                            <p>-</p>
                        </td>
                        <td>-</td>
                        <td>-</td>
                    <tr class="border-0 itm-description ">
                        <td colspan="6">-</td>
                    </tr>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        @if ($invoice->invoice_module == 'account')
                            <td></td>
                        @endif
                        <td>{{ __('Total') }}</td>
                        @if ($invoice->invoice_module == 'Fleet')
                            <td><b>{{ currency_format_with_sym($invoice->totalRate, $invoice->created_by, $invoice->workspace) }}</b>
                            </td>
                            <td></td>
                        @else
                            <td>{{ $invoice->totalQuantity }}</td>

                            <td>{{ currency_format_with_sym($invoice->totalRate, $invoice->created_by, $invoice->workspace) }}
                            </td>
                            <td>{{ currency_format_with_sym($invoice->totalDiscount, $invoice->created_by, $invoice->workspace) }}
                            </td>
                            <td>{{ currency_format_with_sym($invoice->totalTaxPrice, $invoice->created_by, $invoice->workspace) }}
                            </td>
                            <td>{{ currency_format_with_sym($invoice->getSubTotal() - $invoice->getTotalDiscount() + $invoice->getTotalTax(), $invoice->created_by, $invoice->workspace) }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        @php
                            $colspan = 4;
                            if ($invoice->invoice_module == 'account') {
                                $colspan = 5;
                            }
                        @endphp
                        <td colspan="{{ $colspan }}"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                @if ($invoice->invoice_module != 'Fleet')
                                    <tr>
                                        <td>{{ __('Subtotal') }}:</td>
                                        <td>{{ currency_format_with_sym($invoice->getSubTotal(), $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    @if ($invoice->getTotalDiscount())
                                        <tr>
                                            <td>{{ __('Discount') }}:</td>
                                            <td>{{ currency_format_with_sym($invoice->getTotalDiscount(), $invoice->created_by, $invoice->workspace) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                @if (!empty($invoice->taxesData))
                                    @foreach ($invoice->taxesData as $taxName => $taxPrice)
                                        <tr>
                                            <td>{{ $taxName }} :</td>
                                            <td>{{ currency_format_with_sym($taxPrice, $invoice->created_by, $invoice->workspace) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr>
                                    <td>{{ __('Total') }}:</td>
                                    @if ($invoice->invoice_module == 'Fleet')
                                        <td>{{ currency_format_with_sym($invoice->getFleetSubTotal(), $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    @else
                                        <td>{{ currency_format_with_sym($invoice->getSubTotal() - $invoice->getTotalDiscount() + $invoice->getTotalTax(), $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    @endif
                                </tr>
                                <tr>
                                    <td>{{ __('Paid') }}:</td>
                                    <td>{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote(), $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Credit Note') }}:</td>
                                    <td>{{ currency_format_with_sym($invoice->invoiceTotalCreditNote(), $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Due Amount') }}:</td>
                                    <td>{{ currency_format_with_sym($invoice->getDue(), $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <table class="add-border bank-details" style="margin-top: 30px;">
                <thead style="background-color: var(--theme-color);color: {{ $font_color }};">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Bank') }}</th>
                        <th>{{ __('Account Number') }}</th>
                        <th>{{ __('Contact Number') }}</th>
                        <th>{{ __('Bank Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($bank_details_list) && count($bank_details_list) > 0)
                        @foreach ($bank_details_list as $key => $bank)
                            <tr>
                                <td>{{ $bank->holder_name }}</td>
                                <td>{{ $bank->bank_name }}</td>
                                <td>{{ $bank->account_number }}</td>
                                <td>{{ $bank->contact_number }}</td>
                                <td>{{ $bank->bank_address }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="invoice-footer">
                <p> {{ $settings['footer_title'] }} <br>
                    {{ $settings['footer_notes'] }} </p>
            </div>
        </div>
    </div>
    @if (!isset($preview))
        @include('invoice.script');
    @endif
</body>

</html>
