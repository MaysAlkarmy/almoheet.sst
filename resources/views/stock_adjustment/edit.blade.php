@extends('layouts.app')
@section('title', __('stock_adjustment.edit'))

@section('content')
<section class="content-header">
    <h1>@lang('stock_adjustment.edit')</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\StockAdjustmentController::class, 'update'], [$stock_adjustment->id]), 'method' => 'PUT', 'id' => 'stock_adjustment_edit_form' ]) !!}
    
    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location').':*') !!}
                    {!! Form::select('location_id', $business_locations, $stock_adjustment->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'disabled']); !!}
                    {!! Form::hidden('location_id', $stock_adjustment->location_id, ['id' => 'location_id']) !!}
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                    {!! Form::text('ref_no', $stock_adjustment->ref_no, ['class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('transaction_date', __('messages.date').':*') !!}
                    {!! Form::text('transaction_date', @format_datetime($stock_adjustment->transaction_date), ['class' => 'form-control', 'readonly', 'required']); !!}
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('adjustment_type', __('stock_adjustment.out_type') . ':*') !!}
                    {!! Form::select('adjustment_type', [
                        'normal' => __('stock_adjustment.normal'), 
                        'abnormal' => __('stock_adjustment.abnormal'),
                        'from_warehouse_to_branch' => __('stock_adjustment.from_warehouse_to_branch'),
                        'from_branch_to_branch' => __('stock_adjustment.from_branch_to_branch'),
                        'from_warehouse_to_recipient' => __('stock_adjustment.from_warehouse_to_recipient')
                    ], $stock_adjustment->adjustment_type, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']) !!}
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        {{-- تم توحيد الـ ID هنا ليتطابق مع الـ JS --}}
                        {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_s_adj', 'placeholder' => __('stock_adjustment.search_products')]); !!}
                    </div>
                </div>
            </div>
        </div>
        
        @php
            $business_id = $stock_adjustment->business_id;
            $business = \App\Business::find($business_id);
            $custom_labels = json_decode($business->custom_labels, true);
            $p_labels = $custom_labels['product'] ?? [];
        @endphp

        <div class="row">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed" id="stock_adjustment_product_table">
                        <thead>
                            <tr>
                                <th class="text-center" id="cf_1">{{ $p_labels['custom_field1'] ?? ($p_labels['custom_field_1'] ?? '') }}</th>
                                <th class="text-center">الوصف</th>
                                <th class="text-center">@lang('sale.qty')</th>
                                <th class="text-center" id="cf_2">{{ $p_labels['custom_field2'] ?? ($p_labels['custom_field_2'] ?? '') }}</th>
                                <th class="text-center" id="cf_3">{{ $p_labels['custom_field3'] ?? ($p_labels['custom_field_3'] ?? '') }}</th>
                                <th class="text-center"> س </th>
                                <th class="text-center"> ج </th>
                                <th class="text-center"><i class="fa fa-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stock_adjustment->stock_adjustment_lines as $line)
                                @include('stock_adjustment.partials.product_table_row', [
                                    'product' => $line->variation, 
                                    'row_index' => $loop->index, 
                                    'quantity' => $line->quantity, 
                                    'purchase_price' => $line->unit_price, {{-- السعر المخزن في السند --}}
                                    'unit_price' => $line->unit_price
                                ])
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="text-center">
                                @php
                                    $initial_cols = 2; // وصف + كمية
                                    if(!empty($p_labels['custom_field1']) || !empty($p_labels['custom_field_1'])) $initial_cols++;
                                    if(!empty($p_labels['custom_field2']) || !empty($p_labels['custom_field_2'])) $initial_cols++;
                                    if(!empty($p_labels['custom_field3']) || !empty($p_labels['custom_field_3'])) $initial_cols++;
                                @endphp
                                <td colspan="{{ $initial_cols }}"></td>
                                <td><b>ج الكلي:</b></td>
                                <td>
                                    {{-- حقل الإجمالي المخفي الذي يحتاجه الـ JS --}}
                                    <input type="hidden" name="final_total" id="total_adjustment_value" value="{{$stock_adjustment->final_total}}">
                                    <span id="total_adjustment">{{@num_format($stock_adjustment->final_total)}}</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('total_amount_recovered', __('خ') . ' (تلقائي):') !!}
                    {!! Form::text('total_amount_recovered', @num_format($stock_adjustment->total_amount_recovered), [
                        'class' => 'form-control input_number', 
                        'id' => 'total_amount_recovered', 
                        'readonly' => 'readonly', 
                        'style' => 'background-color: #eee; font-weight: bold; color: #d9534f;'
                    ]); !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('additional_notes', __('stock_adjustment.recipient') . ':') !!}
                    {!! Form::textarea('additional_notes', $stock_adjustment->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-center">
                <button type="submit" class="btn btn-primary btn-big">@lang('messages.update')</button>
            </div>
        </div>
    @endcomponent
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
    <script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // تشغيل الحسبة فور تحميل الصفحة لضمان ظهور الخصم المخزن
            update_table_total();

            // دالة لإخفاء الأعمدة المخصصة إذا كانت فارغة في الإعدادات
            function adjust_custom_fields_visibility() {
                for (var i = 1; i <= 3; i++) {
                    var header = $('#cf_' + i);
                    if (header.length > 0 && header.text().trim() == "") {
                        header.hide();
                        $('.custom-field-' + i).hide();
                    }
                }
            }
            adjust_custom_fields_visibility();
        });
    </script>
@endsection