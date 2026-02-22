<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('quantity_entry.quantity_entry_details')</h4>
        </div>
        <div class="modal-body">
            <style>
                /* الحفاظ على ألوان الجدول الأصلية مع تصغير الحجم */
                #quantity_show_table {
                    width: 100% !important;
                    margin-top: 10px;
                }
                #quantity_show_table th, #quantity_show_table td {
                    border: 1px solid #d2d6de !important; /* لون حدود النظام الأصلي */
                    padding: 5px !important;
                    font-size: 13px; /* حجم خط متوازن */
                }
                /* ضمان بقاء لون خلفية العنوان أخضر والخط أبيض */
                #quantity_show_table thead tr.bg-green th {
                    background-color: #00a65a !important; 
                    color: #fff !important;
                    text-align: center;
                    font-weight: bold;
                }
                /* تنسيق جدول الإجماليات ليكون صغيراً ومحاذياً لليمين */
               
                @media print {
                    .no-print { display: none !important; }
                    #quantity_show_table th, #quantity_show_table td {
                        border: 1px solid #000 !important; /* حدود سوداء قوية عند الطباعة فقط */
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

            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-condensed" id="quantity_show_table">
                            <thead>
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th class="col-sku">SKU</th>
                                    <th class="col-product">@lang('sale.product')</th>
                                    <th>@lang('sale.qty')</th>
                                    <th class="col-price @cannot('view_purchase_price') hide @endcan">@lang('lang_v1.cost')</th>
                                    <th class="col-subtotal @cannot('view_purchase_price') hide @endcan">@lang('quantity_entry.total')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $quantity_entry->purchase_lines as $line )
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
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
    <div class="col-md-6 col-md-offset-6 col-sm-12">
        <table class="table no-border">
            <tbody class="table-totals">
                <tr>
                    <th style="width: 20%; vertical-align: middle;" class="text-right">
                        @lang('quantity_entry.total_of_quantity'):
                    </th>
                    <td style="width: 40%; vertical-align: middle;">
                        <span class="pull-right">
                            {{ @format_quantity($total_quantity) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="text-right" style="vertical-align: middle;">
                        @lang('quantity_entry.total'):
                    </th>
                    <td style="vertical-align: middle;">
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
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print btn-print-now" 
                data-href="{{ action([\App\Http\Controllers\QuantityEntryController::class, 'printInvoice'], [$quantity_entry->id]) }}">
                <i class="fa fa-print"></i> @lang( 'messages.print' )
            </button>
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