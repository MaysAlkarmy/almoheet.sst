 

@extends('layouts.app')
@section('title', __('stock_adjustment.edit'))

@section('content')
<section class="content-header">
    <h1>@lang('stock_adjustment.edit')</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\StockAdjustmentController::class, 'update'], [$stock_adjustment->id]), 'method' => 'PUT', 'id' => 'stock_adjustment_edit_form' ]) !!}
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':*') !!}
                        {!! Form::select('location_id', $business_locations, $stock_adjustment->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'disabled']); !!}
                        {{-- نرسله مخفي لأن الموقع لا يمكن تعديله بعد الحفظ لضمان سلامة المخزون --}}
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
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">{{ __('stock_adjustment.search_products') }}</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_s_adj', 'placeholder' => __('stock_adjustment.search_products')]); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed" id="stock_adjustment_product_table">
                            <<thead>
    <tr>
        <th class="text-center"><i class="fa fa-image"></i></th> {{-- عمود الصورة --}}
        <th class="text-center">@lang('sale.product')</th>
        <th class="text-center">@lang('report.current_stock')</th>
        
        {{-- جلب مسميات الحقول المخصصة من الإعدادات --}}
        @php
                // محاولة جلب الأسماء من الجلسة، وإذا فشلت نجلبها من قاعدة البيانات مباشرة
                $business_id = $stock_adjustment->business_id;
                $business = \App\Business::find($business_id);
                $custom_labels = json_decode($business->custom_labels, true);
                $p_labels = $custom_labels['product'] ?? [];
            @endphp 
         <th class="text-center col-cf1">{{ $p_labels['custom_field1'] ?? ($p_labels['custom_field_1'] ?? 'مخصص 1') }}</th>
                                    <th class="text-center col-cf2">{{$p_labels['custom_field2'] ?? ($p_labels['custom_field_2'] ?? 'مخصص 2')  }}</th>
                                    <th class="text-center col-cf3">{{ $p_labels['custom_field3'] ?? ($p_labels['custom_field_3'] ?? 'مخصص 3')  }}</th>

        <th class="text-center">@lang('sale.qty')</th>
        <th class="text-center">@lang('sale.unit_price')</th>
        <th class="text-center">@lang('sale.subtotal')</th>
        <th class="text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
    </tr>
</thead>
                          <tbody>
    @foreach($stock_adjustment->stock_adjustment_lines as $line)
        @include('stock_adjustment.partials.product_table_row', [
            'product' => $line->variation, // نمرر الـ variation الذي يحتوي على علاقة الـ product
            'row_index' => $loop->index, 
            'quantity' => $line->quantity, 
            'purchase_price' => $line->unit_price
        ])
    @endforeach
</tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 text-right">
                    <b>@lang('stock_adjustment.total_amount'):</b>
                    <input type="hidden" name="final_total" id="total_adjustment_value" value="{{$stock_adjustment->final_total}}">
                    <span id="total_adjustment">{{@num_format($stock_adjustment->final_total)}}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('total_amount_recovered', __('stock_adjustment.total_amount_recovered') . ':') !!}
                        {!! Form::text('total_amount_recovered', @num_format($stock_adjustment->total_amount_recovered), ['class' => 'form-control input_number', 'placeholder' => __('stock_adjustment.total_amount_recovered')]); !!}
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
        </div>
    </div>
    {!! Form::close() !!}
</section>
@endsection