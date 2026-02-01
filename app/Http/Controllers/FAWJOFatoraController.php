<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FAWJOFatoraController extends Controller
{ 
    ///////////  before
    public function index()
    {
        $user = Auth::user();

        if (!$user->business_id) {
            abort(403, "Business ID غير محدد لهذا المستخدم.");
        }

        // جلب البيانات أو إنشاء سجل جديد إذا لم يوجد
        $settings = DB::table('settings_fatora')->where('business_id', $user->business_id)->first();

        if (!$settings) {
            DB::table('settings_fatora')->insert([
                'business_id' => $user->business_id,
                'client_id' => null,
                'secret_key' => null,
                'supplier_income_source' => null,
                'tin' => null,
                'registration_name' => null,
                'crn' => null,
                'invoice_type'=> null,  /////////001
                'street_name' => null,
                'building_number' => null,
                'city_name' => null,
                'city_code' => null,
                'county' => null,
                'postal_code' => null,
                'plot_al_zone' => null,
                'vat' => null,
                'csr' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $settings = DB::table('settings_fatora')->where('business_id', $user->business_id)->first();
        }

        return view('fawjo.FWJO', ['settings' => $settings]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->business_id) {
            abort(403, "Business ID غير محدد لهذا المستخدم.");
        }

        // تحديث البيانات أو إدراجها إذا لم توجد
        $data = $request->only([
            'client_id', 'secret_key', 'supplier_income_source',
            'tin', 'registration_name', 'crn','invoice_type',
            'street_name', 'building_number', 'city_name', 'city_code',
            'county', 'postal_code', 'plot_al_zone', 'vat', 'csr'
        ]);
        // $data['updated_at'] = now();

        DB::table('settings_fatora')->updateOrInsert(
            ['business_id' => $user->business_id],
            $data
        );

        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح!');
    }
}
