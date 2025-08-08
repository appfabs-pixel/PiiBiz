@extends('layouts.main')

@section('page-title')
    {{ __('Production') }}
@endsection

@section('page-breadcrumb')
    {{ __('Production') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            {{-- First Panel: Raw Material Selection --}}
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Select Raw Material')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="purchase_category" class="form-label">{{__('Purchase Category')}}</label>
                                <select class="form-control" id="purchase_category">
                                    @foreach($purchaseCategories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="item_category" class="form-label">{{__('Item Category')}}</label>
                                <select class="form-control" id="item_category">
                                    @foreach($itemCategories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="available_quantity" class="form-label">{{__('Available Quantity')}}</label>
                                <input type="text" class="form-control" id="available_quantity" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="usage_quantity" class="form-label">{{__('Usage Quantity')}}</label>
                                <input type="text" class="form-control" id="usage_quantity">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Panel: Products --}}
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Products')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0" id="production_table">
                            <thead>
                                <tr>
                                    <th>{{__('Product Name')}}</th>
                                    <th>{{__('Quantity')}}</th>
                                    <th class="text-end">{{__('Action')}}</th> {{-- For the remove button --}}
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Table body will be populated here by JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" id="add_row">+</button>
                    <button class="btn btn-primary" id="save_production">{{__('Save')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var i = 0; // Start index from 0 for unique IDs
        var productServices = @json($productServices); // All product services
        var totalCategoryStock = 0; // Total stock for the selected item category
        var initialUsageAmount = 0; // The initial value entered in usage_quantity
        var $availableQuantityInput = $('#available_quantity');
        var $usageQuantityInput = $('#usage_quantity');
        var $productionTableBody = $('#production_table tbody');

        // Function to calculate total allocated quantity from product rows
        function calculateTotalAllocated() {
            var allocated = 0;
            $productionTableBody.find('.product-quantity-input').each(function() {
                allocated += parseFloat($(this).val()) || 0;
            });
            return allocated;
        }

        // Function to update both available_quantity and usage_quantity displays
        function updateQuantitiesDisplay() {
            var allocated = calculateTotalAllocated();

            // Available Quantity: total stock - initial usage (does not change with product allocation)
            var currentAvailable = totalCategoryStock - initialUsageAmount;
            $availableQuantityInput.val(currentAvailable);

            // Usage Quantity: initial usage - allocated product quantities
            var remainingUsage = initialUsageAmount - allocated;
            $usageQuantityInput.val(remainingUsage);

            // Check for "Product Finished" based on remaining usage
            if (remainingUsage <= 0 && initialUsageAmount > 0) {
                $usageQuantityInput.addClass('product-finished').attr('title', 'Product Finished');
                alert('Product Finished! All usage quantity has been allocated.');
            } else {
                $usageQuantityInput.removeClass('product-finished').removeAttr('title');
            }
        }

        // Event listener for item_category change
        $('#item_category').change(function() {
            var selectedCategoryId = $(this).val();
            totalCategoryStock = 0;
            initialUsageAmount = 0; // Reset initial usage amount
            $usageQuantityInput.val(''); // Clear usage quantity input
            $productionTableBody.empty(); // Clear product rows

            if (selectedCategoryId) {
                var filteredProducts = productServices.filter(function(product) {
                    return product.category_id == selectedCategoryId;
                });

                filteredProducts.forEach(function(product) {
                    totalCategoryStock += product.quantity;
                });
            }
            updateQuantitiesDisplay(); // Update quantities display
        });

        // Event listener for usage_quantity input (initial entry)
        $usageQuantityInput.on('keyup change', function() {
            initialUsageAmount = parseFloat($(this).val()) || 0;
            updateQuantitiesDisplay();
        });

        // Add row button click
        $("#add_row").click(function() {
            i++;
            var newRow = '<tr id="row' + i + '">' +
                         '<td><select name="product_name[]" class="form-control product_select">' +
                         '<option value="">Select Product</option>' +
                         productServices.map(function(product) {
                             return '<option value="' + product.id + '">' + product.name + '</option>';
                         }).join('') +
                         '</select></td>' +
                         '<td><input type="number" name="quantity[]" placeholder="Enter Quantity" class="form-control product-quantity-input" data-row-id="' + i + '" min="0" /></td>' +
                         '<td class="text-end"><button type="button" name="remove" id="' + i + '" class="btn btn-sm btn-danger btn_remove">X</button></td>' +
                         '</tr>';
            $productionTableBody.append(newRow);
        });

        // Remove row button click
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id).remove();
            updateQuantitiesDisplay(); // Recalculate after removing a row
        });

        // Event listener for quantity input in product rows
        $(document).on('keyup change', '.product-quantity-input', function() {
            updateQuantitiesDisplay(); // Update quantities based on new allocation
        });

        // Save production button functionality (existing)
        $("#save_production").click(function() {
            var purchaseCategory = $('#purchase_category').val();
            var itemCategory = $('#item_category').val();
            var usageQuantity = $('#usage_quantity').val();

            var productNames = [];
            var quantities = [];

            $('#production_table tbody tr').each(function() {
                productNames.push($(this).find('.product_select').val());
                quantities.push($(this).find('.product-quantity-input').val());
            });

            var data = {
                _token: '{{ csrf_token() }}',
                purchase_category: purchaseCategory,
                item_category: itemCategory,
                usage_quantity: usageQuantity,
                product_name: productNames,
                quantity: quantities
            };

            console.log('Sending AJAX request...');
            console.log('Data:', data);

            $.ajax({
                url: '{{ route('production.store') }}',
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        show_toastr('Success', response.message, 'success');
                        location.reload();
                    } else {
                        show_toastr('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr.status, xhr.statusText, xhr.responseJSON);
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    for (var key in errors) {
                        errorMessage += errors[key][0] + '\n';
                    }
                    show_toastr('Error', errorMessage, 'error');
                }
            });
        });

        // Trigger change on page load if a category is pre-selected
        $('#item_category').trigger('change');
    });
</script>
@endpush