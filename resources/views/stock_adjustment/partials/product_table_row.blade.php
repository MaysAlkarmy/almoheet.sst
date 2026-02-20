<tr class="product_row">
    @php
        // تعريف كائن المنتج لسهولة الوصول
        $p_obj = $product->product ?? $product; 
        
        // جلب السعر الأصلي (التكلفة) بدقة
        $original_price = $p_obj->default_purchase_price ?? ($product->last_purchased_price ?? 0);
        
        // جلب سعر السند الحالي
        $display_price = $purchase_price ?? ($unit_price ?? $original_price);
    @endphp

    {{-- 1. مخصص 1 --}}
    <td class="text-center">
        {{ $p_obj->product_custom_field1 ?? '-' }}
    </td>

    {{-- 2. اسم المنتج --}}
    <td>
        <strong>{{ $p_obj->name ?? $p_obj->product_name }}</strong>
        @if(!empty($product->name) && $product->name != 'DUMMY' && $product->name != $p_obj->name)
            - {{ $product->name }}
        @endif
        
        <input type="hidden" class="variation_id" value="{{ $product->id ?? $product->variation_id }}" name="products[{{$row_index}}][variation_id]">
        <input type="hidden" name="products[{{$row_index}}][product_id]" value="{{ $p_obj->id ?? $product->product_id }}">
        
        {{-- حقل السعر الأصلي للمقارنة - بدونه لن يحسب الخصم --}}
        <input type="hidden" class="original_purchase_price" value="{{ $product->product->default_purchase_price ?? ($product->default_purchase_price ?? ($product->last_purchased_price ?? 0)) }}">
    </td>

    {{-- 3. الكمية --}}
    <td>
        <input type="text" class="form-control product_quantity input_number" 
               value="{{ @format_quantity($quantity ?? 1) }}" 
               name="products[{{$row_index}}][quantity]">
    </td>

   {{-- 4. مخصص 2 (التعبئة) --}}
<td class="text-center">
    {{-- نضع القيمة داخل input مخفي أو ظاهر ليقرأه الجافاسكريبت --}}
    <input type="text" 
           name="products[{{$row_index}}][custom_field_2]" 
           class="form-control custom_field_2 input_number text-center" 
           value="{{ $p_obj->product_custom_field2 ?? 1 }}" 
           style="width: 70px; display: inline-block;">
</td>

    {{-- 5. مخصص 3 --}}
    <td class="text-center">
        {{ $p_obj->product_custom_field3 ?? '-' }}
    </td>

    {{-- 6. السعر --}}
    <td>
        <input type="text" name="products[{{$row_index}}][unit_price]" 
               class="form-control product_unit_price input_number" 
               value="{{ @num_format($display_price) }}">
    </td>

    {{-- 7. المجموع --}}
    <td class="text-center">
        {{-- حقل مخفي لإرسال القيمة للسيرفر --}}
        <input type="hidden" class="product_line_total" name="products[{{$row_index}}][row_total]" value="0">
        
        {{-- عرض النص للمستخدم بشكل عريض --}}
        <span class="product_line_total_text tw-font-bold">0.00</span>
    </td>

    {{-- 8. حذف --}}
    <td class="text-center">
        <i class="fa fa-trash remove_product_row text-danger cursor-pointer"></i>
    </td>
</tr>