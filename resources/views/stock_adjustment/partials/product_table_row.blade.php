<tr class="product_row">
    {{-- 1. الحقل المخصص الأول (أصبح في بداية السطر) --}}
    <td class="text-center custom-field-1">
        {{ $product->product->product_custom_field1 ?? ($product->product_custom_field1 ?? '-') }}
    </td>

    {{-- 2. اسم المنتج (بدون SKU ومع حل مشكلة DUMMY) --}}
    <td>
        @php
            $p_name = $product->product->name ?? ($product->product_name ?? ($product->name ?? ''));
            if(!empty($product->variation_name) && $product->variation_name != 'DUMMY') {
                $p_name .= ' ' . $product->variation_name;
            }
        @endphp
        <strong>{{ $p_name }}</strong>
        
        {{-- الحقول المخفية الأساسية --}}
        <input type="hidden" name="products[{{$row_index}}][product_id]" value="{{$product->product_id}}">
        <input type="hidden" class="variation_id" value="{{$product->id ?? $product->variation_id}}" name="products[{{$row_index}}][variation_id]">
        <input type="hidden" value="{{$product->enable_stock ?? ($product->product->enable_stock ?? 0)}}" name="products[{{$row_index}}][enable_stock]">
    </td>

    {{-- 3. الكمية --}}
    <td>
        @php
            $qty = !empty($quantity) ? $quantity : (!empty($product->quantity_ordered) ? $product->quantity_ordered : 1);
        @endphp
        <input type="text" class="form-control product_quantity input_number" 
            value="{{@format_quantity($qty)}}" 
            name="products[{{$row_index}}][quantity]">
    </td>

    {{-- 4. الحقل المخصص الثاني --}}
    <td class="text-center custom-field-2">
        {{ $product->product->product_custom_field2 ?? ($product->product_custom_field2 ?? '-') }}
    </td>

    {{-- 5. الحقل المخصص الثالث --}}
    <td class="text-center custom-field-3">
        {{ $product->product->product_custom_field3 ?? ($product->product_custom_field3 ?? '-') }}
    </td>

    {{-- 6. تكلفة الوحدة (مع حقل التكلفة الأصلية للخصم التلقائي) --}}
    <td>
        @php
            $unit_price_val = !empty($purchase_price) ? $purchase_price : ($product->default_purchase_price ?? ($product->last_purchased_price ?? 0));
        @endphp
        {{-- حقل مخفي يحمل التكلفة الأصلية للمقارنة وحساب الخصم في JS --}}
        <input type="hidden" class="original_purchase_price" value="{{$unit_price_val}}">
        
        <input type="text" name="products[{{$row_index}}][unit_price]" 
               class="form-control product_unit_price input_number" 
               value="{{@num_format($unit_price_val)}}">
    </td>

    {{-- 7. المجموع (إجمالي السطر) --}}
    <td>
        <input type="text" readonly class="form-control product_line_total" 
               value="{{@num_format($qty * $unit_price_val)}}" style="font-weight: bold;">
    </td>

    {{-- 8. زر الحذف --}}
    <td class="text-center">
        <i class="fa fa-trash remove_product_row text-danger cursor-pointer" aria-hidden="true" style="font-size: 18px; cursor:pointer;"></i>
    </td>
</tr>