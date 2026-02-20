@php 
    $colspan = 15;
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
@extends('layouts.app')
@section('title', __('stock_adjustment.add'))

@section('content')
    <section class="content-header">
        <br>
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('stock_adjustment.add')</h1>
    </section>

    <section class="content no-print">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\StockAdjustmentController::class, 'store']),
            'method' => 'post',
            'id' => 'stock_adjustment_form',
            'files' => true 
        ]) !!}

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                 <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('location_id', __('quantity_entry.location')) !!}
            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'required']) !!}
        </div>
    </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                        {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('adjustment_type', __('stock_adjustment.out_type') . ':*') !!}
                        {!! Form::select('adjustment_type', ['normal' => __('stock_adjustment.normal'), 
                                                             'abnormal' => __('stock_adjustment.abnormal'),
                                                             'from_warehouse_to_branch' => __('stock_adjustment.from_warehouse_to_branch'),
                                                             'from_branch_to_branch' => __('stock_adjustment.from_branch_to_branch'),
                                                             'from_warehouse_to_recipient' => __('stock_adjustment.from_warehouse_to_recipient')
                                                             ], null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']) !!}
                                                             
                    </div> 
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    {!! Form::text('search_product', null, [
                                        'class' => 'form-control',
                                        'id' => 'search_product_for_srock_adjustment',
                                        'placeholder' => __('stock_adjustment.search_product'),
                                       
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm tw-w-full" 
                                    style="height: 34px; width: 100%;" 
                                    data-toggle="modal" 
                                    data-target="#export_quantity_products_modal">
                                <i class="fa fa-file-excel-o"></i> @lang('stock_adjustment.export')
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <input type="hidden" id="product_row_index" value="0">
                    <input type="hidden" id="total_amount" name="final_total" value="0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed" id="stock_adjustment_product_table">
                          <thead>
    <tr>
        <th class="text-center" id="cf_1">{{ $custom_labels['product']['custom_field_1'] ?? '' }}</th>
        <th class="text-center">الوصف</th>
        <th class="text-center">@lang('sale.qty')</th>
        <th class="text-center" id="cf_2">{{ $custom_labels['product']['custom_field_2'] ?? '' }}</th>
        <th class="text-center" id="cf_3">{{ $custom_labels['product']['custom_field_3'] ?? '' }}</th>
        <th class="text-center"> س </th>
        <th class="text-center">ج</th>
        <th class="text-center"><i class="fa fa-trash"></i></th>
    </tr>
</thead>
                            <tbody>
                                {{-- سيتم إضافة الأسطر هنا بواسطة JS --}}
                            </tbody>
                          <tfoot>
    <tr class="text-center">
        {{-- الآن لدينا 6 أعمدة قبل خانة المجموع --}}
        <td colspan="6"></td>
        <td>
            <b>ج:</b> 
            <span id="total_adjustment">0.00</span>
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
        {!! Form::text('total_amount_recovered', 0, [
            'class' => 'form-control input_number', 
            'id' => 'total_amount_recovered', 
            'readonly' => 'readonly', // منع المستخدم من الكتابة
            'style' => 'background-color: #eee; font-weight: bold; color: #d9534f;' // تمييز الحقل بصرياً
        ]) !!}
    </div>
</div>
               
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('additional_notes', __('stock_adjustment.recipient') . ':') !!}
                        {!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-12 text-center">
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.save')</button>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}
    </section>

    @include('stock_adjustment.partials.export_quantity_products_modal')

@stop

@section('javascript')
    <script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#stock_adjustment_form');

            // دالة لإخفاء الأعمدة التي ليس لها مسمى في الإعدادات
            function adjust_custom_fields_visibility() {
                for (var i = 1; i <= 4; i++) {
                    var header = $('#cf_' + i);
                    if (header.length > 0) {
                        if (header.text().trim().length == 0) {
                            header.hide();
                            // إخفاء الخلايا المقابلة في الـ tbody (تستخدم Class محدد في Row)
                            $('.custom-field-' + i).hide();
                        } else {
                            header.show();
                            $('.custom-field-' + i).show();
                        }
                    }
                }
            }

            // تنفيذ الإخفاء عند تحميل الصفحة
            adjust_custom_fields_visibility();

            // تنفيذ الإخفاء عند إضافة أي منتج جديد للجدول (مهم جداً)
            $('#stock_adjustment_product_table').on('DOMNodeInserted', 'tr', function() {
                adjust_custom_fields_visibility();
            });
        });
    </script>
@endsection