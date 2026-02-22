<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('quantity_entry.quantity_entry_details')</h4>
        </div>
        <div class="modal-body">
           <style>
    /* تصغير الخط العام للطباعة */
    body {
        font-size: 12px;
        color: #000;
    }
    
    /* جعل الجدول مضغوطاً (Compact) */
    .table-bold-border {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .table-bold-border th, .table-bold-border td {
        border: 1px solid #000 !important;
        padding: 3px 5px !important; /* تقليل الفراغات الداخلية لتصغير الطول */
        font-size: 11px; /* تصغير خط البيانات */
    }

    .table-bold-border thead th {
        background-color: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
        font-size: 12px;
    }

    /* تنسيق ترويسة الفاتورة */
    .invoice-info {
        font-size: 13px;
        margin-bottom: 10px;
    }

    /* إخفاء أي عناصر غير ضرورية عند الطباعة */
    @media print {
        .no-print, .modal-header, .modal-footer, .row.no-print {
            display: none !important;
        }
        .table-bold-border {
            width: 100% !important;
        }
    }
</style>

            <div class="row no-print" style="margin-bottom: 15px; background: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                <div class="col-sm-12">
                    <strong style="margin-right: 15px;">إظهار/إخفاء أعمدة:</strong>
                    <label style="margin-right: 10px; cursor: pointer;"><input type="checkbox" class="toggle-col" data-col="col-sku" checked> SKU</label>
                    <label style="margin-right: 10px; cursor: pointer;"> <input type="checkbox" class="toggle-col" data-col="col-product" checked> المنتج</label>
                    <label style="margin-right: 10px; cursor: pointer;"><input type="checkbox" class="toggle-col" data-col="col-price" checked> السعر</label>
                    <label style="margin-right: 10px; cursor: pointer;"><input type="checkbox" class="toggle-col" data-col="col-subtotal" checked> الإجمالي</label>
                </div>
            </div>

            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    <b>@lang('purchase.ref_no'):</b> #{{ $quantity_entry->ref_no }}<br/>
                    <b>@lang('messages.date'):</b> {{ @format_datetime($quantity_entry->transaction_date) }}<br/>
                    <b>@lang('business.location'):</b> {{ $quantity_entry->location->name }}
                </div>
                
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bold-border" id="quantity_show_table">
                            <thead>
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th class="col-sku">SKU</th>
                                    <th class="text-center col-product">@lang('sale.product')</th>
                                    <th class="text-center">@lang('sale.qty')</th>
                                    <th class="text-center col-price @cannot('view_purchase_price') hide @endcan">@lang('lang_v1.cost')</th>
                                    <th class="text-center col-subtotal @cannot('view_purchase_price') hide @endcan">@lang('quantity_entry.total')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $quantity_entry->purchase_lines as $line )
                                  <tr>
        <td>{{ $loop->iteration }}</td>
        <td class="col-sku">{{ $line->variations->sub_sku ?? '' }}</td>
        <td class="col-product">
            {{ $line->product->name }}
            @if($line->variations->name != 'DUMMY')
                - {{ $line->variations->name }}
            @endif
        </td>
        <td class="text-center">{{ @format_quantity($line->quantity) }}</td>
        <td class="text-center col-price @cannot('view_purchase_price') hide @endcan">{{ @num_format($line->purchase_price) }}</td>
        <td class="text-center col-subtotal @cannot('view_purchase_price') hide @endcan">{{ @num_format($line->purchase_price * $line->quantity) }}</td>
    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

           <div class="row">
    <div class="col-md-4 col-md-offset-8 col-sm-6 col-sm-offset-6">
        <table class="table no-border table-condensed" style="width: 100%; font-size: 11px; margin-top: 5px;">
            <style>
                /* تصغير المسافات بين الأسطر داخل جدول الإجماليات */
                .table-totals td, .table-totals th {
                    padding: 2px 5px !important;
                    line-height: 1.2 !important;
                }
            </style>
            <tbody class="table-totals">
                <tr>
                    <th style="width: 20%;" class="text-right">@lang('quantity_entry.total_of_quantity'):</th>
                    <td style="width: 40%;">
                        <span class="pull-right">
                            {{ @format_quantity($total_quantity) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="text-right">@lang('quantity_entry.total'):</th>
                    <td>
                        <span class="display_currency pull-right" data-currency_symbol="true">
                            {{ $quantity_entry->final_total }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.toggle-col').off('change').on('change', function() {
            var colClass = $(this).data('col');
            if($(this).is(':checked')) {
                $('.' + colClass).show();
            } else {
                $('.' + colClass).hide();
            }
        });
    });
</script>