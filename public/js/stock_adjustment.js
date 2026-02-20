$(document).ready(function() {
    // 1. إعداد البحث عن المنتجات (يدعم صفحة الإضافة وصفحة التعديل)
    // تم توحيد المعرفات لتعمل على المعرفين (search_product_for_s_adj و search_product_for_srock_adjustment)
    if ($('#search_product_for_s_adj').length > 0 || $('#search_product_for_srock_adjustment').length > 0) {
        let search_field = $('#search_product_for_s_adj').length > 0 ? '#search_product_for_s_adj' : '#search_product_for_srock_adjustment';
        
        $(search_field).autocomplete({
            source: function(request, response) {
                $.getJSON(
                    '/products/list',
                    { location_id: $('#location_id').val(), term: request.term },
                    response
                );
            },
            minLength: 2,
            response: function(event, ui) {
                if (ui.content.length == 1) {
                    ui.item = ui.content[0];
                    if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    }
                } else if (ui.content.length == 0) {
                    swal(LANG.no_products_found);
                }
            },
            focus: function(event, ui) {
                if (ui.item.qty_available <= 0) {
                    return false;
                }
            },
            select: function(event, ui) {
                if (ui.item.qty_available > 0) {
                    $(this).val(null);
                    stock_adjustment_product_row(ui.item.variation_id);
                } else {
                    alert(LANG.out_of_stock);
                }
            },
        }).autocomplete('instance')._renderItem = function(ul, item) {
           if (item.qty_available <= 0) {
        // حالة المنتج غير المتوفر
        var string = '<li class="ui-state-disabled">' + item.name;
        if (item.type == 'variable') {
            string += '-' + item.variation;
        }
        // حذفنا (' + item.sub_sku + ') من السطر التالي
        string += ' (Out of stock) </li>';
        return $(string).appendTo(ul);
    } else if (item.enable_stock != 1) {
        return ul;
    } else {
        // حالة المنتج المتوفر
        var string = '<div>' + item.name;
        if (item.type == 'variable') {
            string += '-' + item.variation;
        }
        // حذفنا (' + item.sub_sku + ') وأغلقنا الـ div مباشرة
        string += ' </div>';
        
        return $('<li>')
            .append(string)
            .appendTo(ul);
    }
        };
    }

    // 2. تحديث عداد الأسطر وتحديث الإجماليات عند تحميل الصفحة (مهم جداً لصفحة التعديل)
    if ($('#stock_adjustment_product_table tbody tr').length > 0) {
        let total_rows = $('#stock_adjustment_product_table tbody tr').length;
        if($('#product_row_index').length == 0){
             $('form').append('<input type="hidden" id="product_row_index" value="'+total_rows+'">');
        } else {
             $('#product_row_index').val(total_rows);
        }
        update_table_total();
    }

    // 3. تغيير الفرع/الموقع
    $('select#location_id').change(function() {
        let search_field = $('#search_product_for_s_adj').length > 0 ? '#search_product_for_s_adj' : '#search_product_for_srock_adjustment';
        if ($(this).val()) {
            $(search_field).removeAttr('disabled');
        } 
        $('table#stock_adjustment_product_table tbody').html('');
        $('#product_row_index').val(0);
        update_table_total();
    });

    // 4. مراقبة التغييرات في الكميات والأسعار
    // مراقبة الكمية (تغيير لحظي + عند الخروج من الخانة)
$(document).on('change input', 'input.product_quantity', function() {
    update_table_row($(this).closest('tr'));
});

// مراقبة سعر الوحدة (تغيير لحظي + عند الخروج من الخانة)
$(document).on('change input', 'input.product_unit_price', function() {
    update_table_row($(this).closest('tr'));
    update_table_total();
});

// مراقبة شاملة لكل أحداث الإدخال في الجدول
// مراقبة شاملة لكل أحداث الإدخال في الجدول (تصحيح السطر)
$(document).on('input change keyup', 'input.product_quantity, input.product_unit_price, input.custom_field_2', function() {
    var tr = $(this).closest('tr');
    update_table_row(tr);
    update_table_total();
});
    // 5. حذف سطر منتج
    $(document).on('click', '.remove_product_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this).closest('tr').remove();
                update_table_total();
            }
        });
    });

    // 6. إعداد التاريخ (Datetimepicker)
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    // 7. التحقق من صحة النموذج والـ DataTable
    $('form#stock_adjustment_form').validate();
    $('form#stock_adjustment_edit_form').validate();

    stock_adjustment_table = $('#stock_adjustment_table').DataTable({
        processing: true,
        serverSide: true,
        fixedHeader: false,
        
        ajax: '/stock-adjustments',
       
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
            },
        ],
        aaSorting: [[1, 'desc']],
        columns: [
        { data: 'action', name: 'action', orderable: false, searchable: false }, // 1. الإجراءات
        { data: 'transaction_date', name: 'transaction_date' },                // 2. التاريخ
        { data: 'ref_no', name: 'ref_no' },                                   // 3. الرقم المرجعي
        { data: 'location_name', name: 'BL.name' },                           // 4. الفرع
        { data: 'adjustment_type', name: 'adjustment_type' },                 // 5. النوع
        { data: 'final_total', name: 'final_total', orderable: false, searchable: false },                         // 6. عمود "ج" (الإجمالي)
        { data: 'total_amount_recovered', name: 'total_amount_recovered' },   // 7. عمود "خ" (المسترد/الخصم)
        { data: 'additional_notes', name: 'additional_notes' },               // 8. المستلم (أو الملاحظات حسب الـ Blade)
        { data: 'added_by', name: 'added_by' }                                // 9. بواسطة
    ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_adjustment_table'));
        },
    });

    // 8. حذف سند التسوية من القائمة
    $(document).on('click', 'button.delete_stock_adjustment', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            stock_adjustment_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});

// 9. الدوال المساعدة (Functions)
function stock_adjustment_product_row(variation_id) {
    var row_index = parseInt($('#product_row_index').val()) || 0;
    var location_id = $('select#location_id').val();
    $.ajax({
        method: 'POST',
        url: '/stock-adjustments/get_product_row',
        data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
        dataType: 'html',
        success: function(result) {
            $('table#stock_adjustment_product_table tbody').append(result);
            update_table_total();
            // نجلب آخر سطر تم إضافته ونحدث الحسبة فيه فوراً
            var last_tr = $('table#stock_adjustment_product_table tbody tr').last();
            update_table_row(last_tr); 
            update_table_total();
            $('#product_row_index').val(row_index + 1);
        },
    });
}

function update_table_total() {
    var table_total = 0;
    var total_diff = 0;

    $('table#stock_adjustment_product_table tbody tr').each(function() {
        var row = $(this);
        var qty = parseFloat(__read_number(row.find('input.product_quantity'))) || 0;
        var unit_price = parseFloat(__read_number(row.find('input.product_unit_price'))) || 0;
        var packing = parseFloat(__read_number(row.find('input.custom_field_2'))) || 0;

        // تطبيق نفس المعادلة في الجمع الكلي
        var row_total = (unit_price / 12) * (qty * packing);
        table_total += row_total;

        // حساب الخصم (اختياري حسب حاجتك)
        var original_price = parseFloat(row.find('input.original_purchase_price').val()) || 0;
        if (original_price > 0) {
            total_diff += (original_price - unit_price) * qty;
        }
    });

    $('span#total_adjustment').text(__number_f(table_total));
    $('#total_amount').val(table_total); // هذا الحقل الذي يذهب للـ Controller
    
    if ($('#total_amount_recovered').length > 0) {
        __write_number($('#total_amount_recovered'), total_diff);
    }
}
function update_table_row(tr) {
   var quantity = parseFloat(__read_number(tr.find('input.product_quantity'))) || 0;
    var unit_price = parseFloat(__read_number(tr.find('input.product_unit_price'))) || 0;
    var packing = parseFloat(__read_number(tr.find('input.custom_field_2'))) || 0; // التعبئة
    
    // 2. تطبيق المعادلة المطلوبة
    var row_total = (unit_price / 12) * (quantity * packing);
    
    // 3. تحديث خانة المجموع المخفية (التي تذهب لقاعدة البيانات)
    tr.find('input.product_line_total').val(row_total);
    
    // 4. تحديث النص الظاهر للمستخدم
    if(tr.find('.product_line_total_text').length > 0){
        tr.find('.product_line_total_text').text(__number_f(row_total));
    }
}

// 10. استيراد المنتجات من إكسل
$(document).on('submit', '#export_quantity_products_modal form', function(e) {
    e.preventDefault();
    let formData = new FormData(this); 
    let url = $(this).attr('action');

    let currentRows = parseInt($('#product_row_index').val()) || 0;
    formData.append('location_id', $('#location_id').val()); 
    formData.append('row_count', currentRows); 

    let btn = $(this).find('button[type="submit"]');
    let btn_text = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        method: 'POST',
        url: url,
        data: formData,
        dataType: 'json',
        processData: false, 
        contentType: false, 
        success: function(result) {
            btn.prop('disabled', false).html(btn_text);
            if (result.success) {
                if (result.html && result.html.trim() !== '') {
                    let $wrappedHtml = $('<table><tbody>' + result.html + '</tbody></table>');
                    let $newRows = $wrappedHtml.find('tr.product_row');

                    $newRows.each(function() {
                        let $currentRow = $(this).clone(); 
                        let variation_id = $currentRow.find('.variation_id').val();
                        let new_qty = parseFloat($currentRow.find('.product_quantity').val()) || 0;

                        let existingRow = $('#stock_adjustment_product_table tbody')
                                            .find('.variation_id[value="' + variation_id + '"]')
                                            .closest('tr');

                        if (existingRow.length > 0) {
                            // إذا المنتج موجود مسبقاً، نجمع الكميات
                            let current_qty = parseFloat(__read_number(existingRow.find('.product_quantity'))) || 0;
                            let total_qty = current_qty + new_qty;
                            __write_number(existingRow.find('.product_quantity'), total_qty);
                            
                            // تحديث السطر (هنا يتم ربط السعر بالخصم)
                            update_table_row(existingRow);
                        } else {
                            // إذا منتج جديد، نضيف السطر للجدول
                            $('#stock_adjustment_product_table tbody').append($currentRow);
                            
                            // تشغيل الحسبة للسطر الجديد فور إضافته
                            update_table_row($currentRow);
                            currentRows++;
                        }
                    });

                    // تحديث العداد الكلي وتحديث إجمالي السند والخصم
                    $('#product_row_index').val(currentRows);
                    update_table_total();
                }

                // إدارة رسائل التنبيه والملفات المرفوضة
                if (result.skipped_count > 0) {
                    let downloadLink = '';
                    if (result.download_url) {
                        downloadLink = '<br><a href="' + result.download_url + '" target="_blank" style="color: #fff; font-weight: bold; text-decoration: underline;">' +
                                       '<i class="fa fa-download"></i> تحميل ملف المنتجات المرفوضة</a>';
                    }
                    toastr.warning(result.msg + downloadLink, "تقرير الاستيراد", {
                        "timeOut": 0, "extendedTimeOut": 0, "closeButton": true, "tapToDismiss": false
                    });
                } else {
                    toastr.success(result.msg);
                }

                $('#export_quantity_products_modal').modal('hide');
                $('#export_quantity_products_modal form')[0].reset();
            } else {
                toastr.error(result.msg);
            }
        },
        error: function(e) {
            btn.prop('disabled', false).html(btn_text);
            toastr.error("حدث خطأ أثناء الرفع");
        }
    });
});

$(document).on('shown.bs.modal', '.view_modal', function() {
    __currency_convert_recursively($('.view_modal'));
});