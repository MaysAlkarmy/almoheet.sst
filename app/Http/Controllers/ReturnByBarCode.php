<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction; // مهم جداً
use App\Utils\TransactionUtil; // مهم جداً

use Illuminate\Support\Facades\DB;


class ReturnByBarCode extends Controller

{

protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

   public function searchInvoicesByProduct($sku)
{
    try {
        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->get('location_id'); 

        $query = \App\TransactionSellLine::join('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
            ->join('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->join('products as p', 'v.product_id', '=', 'p.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where(function($q) use ($sku) {
                $q->where('p.sku', $sku)
                  ->orWhere('v.sub_sku', $sku); // البحث بالـ SKU أو الباركود
            });

        // شرط الفرع الحالي لضمان دقة البيانات
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $invoices = $query->select([
        't.id as transaction_id',
        't.transaction_date', 
        't.invoice_no', 
        'c.name as customer_name', 
        'p.name as product_name',
        'p.sku', // <--- تأكد من إضافة هذا السطر هنا
        'bl.name as location_name',
                // جمع الكمية في حال تكرر المنتج في نفس الفاتورة
                \DB::raw("SUM(transaction_sell_lines.quantity) as total_qty"),
                'transaction_sell_lines.unit_price_inc_tax as unit_price',
                \DB::raw("SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price_inc_tax) as line_total"),
                't.final_total as invoice_total',
                // جلب الدفعات عبر Subqueries
                \DB::raw("(SELECT SUM(amount) FROM transaction_payments WHERE transaction_id = t.id AND method = 'cash') as total_cash"),
                \DB::raw("(SELECT SUM(amount) FROM transaction_payments WHERE transaction_id = t.id AND method = 'card') as total_card"),
                \DB::raw("(SELECT SUM(amount) FROM transaction_payments WHERE transaction_id = t.id AND method NOT IN ('cash', 'card')) as total_other")
            ])
            ->groupBy('t.id', 'p.id') // تجميع حسب الفاتورة والمنتج
            ->orderBy('t.transaction_date', 'desc')
            ->get();

        if ($invoices->count() > 0) {
            return response()->json(['success' => true, 'invoices' => $invoices]);
        } else {
            return response()->json(['success' => false, 'msg' => 'هذا الصنف لم يبع في هذا الفرع مسبقاً.']);
        }
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => 'خطأ: ' . $e->getMessage()]);
    }
}

 public function return($id, $sku = null)
{
    // 1. التحقق من صلاحيات المستخدم
    if (!auth()->user()->can('access_sell_return') && !auth()->user()->can('access_own_sell_return')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');
    
    // 2. جلب الفاتورة مع كافة العلاقات الضرورية بما فيها سطر المبيعات والمنتجات
    $sell = \App\Transaction::where('business_id', $business_id)
        ->with([
            'sell_lines', 
            'location', 
            'return_parent', 
            'contact', 
            'tax', 
            'sell_lines.sub_unit', 
            'sell_lines.product', 
            'sell_lines.product.unit', 
            'sell_lines.variations'
        ])
        ->find($id);

    if (!$sell) {
        abort(404, 'Invoice not found.');
    }

    // تهيئة أداة العمليات (TransactionUtil)
    $transactionUtil = new \App\Utils\TransactionUtil();

    // 3. معالجة أسطر الفاتورة لحساب الكمية المتاحة للإرجاع
    foreach ($sell->sell_lines as $key => $value) {
        // حساب الكمية المتاحة = (الكمية الأصلية - الكمية التي تم إرجاعها سابقاً)
        $quantity_available = $value->quantity - $value->quantity_returned;
        $sell->sell_lines[$key]->quantity_available_for_return = $quantity_available;

        // إعادة حساب الإجماليات إذا كانت هناك وحدات فرعية
        if (!empty($value->sub_unit_id)) {
            $formated_sell_line = $transactionUtil->recalculateSellLineTotals($business_id, $value);
            $sell->sell_lines[$key] = $formated_sell_line;
        }

        // تنسيق الكمية للعرض
        $sell->sell_lines[$key]->formatted_qty = $transactionUtil->num_f($value->quantity, false, null, true);
    }

    // 4. تمرير البيانات للـ View
    // الـ SKU سيستخدم في الـ Blade لإخفاء العناصر غير المطلوبة أو التي استنفذت كميتها
    return view('sell_return.return_by_barcode')->with(compact('sell', 'sku'));
}

}
