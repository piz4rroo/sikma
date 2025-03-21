<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use App\Http\Requests\PromoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromoController extends Controller
{
    /**
     * Display a listing of promotions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promos = Promo::latest()->paginate(10);
        return view('admin.promos.index', compact('promos'));
    }

    /**
     * Show the form for creating a new promotion.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.promos.create');
    }

    /**
     * Store a newly created promotion.
     *
     * @param  \App\Http\Requests\PromoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PromoRequest $request)
    {
        DB::beginTransaction();
        try {
            $promo = new Promo();
            $promo->title = $request->title;
            $promo->code = $request->code ?? Str::upper(Str::random(8));
            $promo->description = $request->description;
            $promo->discount_type = $request->discount_type;
            $promo->discount_value = $request->discount_value;
            $promo->start_date = $request->start_date;
            $promo->end_date = $request->end_date;
            $promo->usage_limit = $request->usage_limit;
            $promo->is_active = $request->is_active ?? 0;
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/promos'), $imageName);
                $promo->image = 'images/promos/' . $imageName;
            }
            
            $promo->save();
            
            DB::commit();
            return redirect()->route('admin.promos.index')
                ->with('success', 'Promotion created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error creating promotion: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified promotion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $promo = Promo::findOrFail($id);
        return view('admin.promos.show', compact('promo'));
    }

    /**
     * Show the form for editing the specified promotion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('admin.promos.edit', compact('promo'));
    }

    /**
     * Update the specified promotion.
     *
     * @param  \App\Http\Requests\PromoRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PromoRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $promo = Promo::findOrFail($id);
            $promo->title = $request->title;
            $promo->code = $request->code;
            $promo->description = $request->description;
            $promo->discount_type = $request->discount_type;
            $promo->discount_value = $request->discount_value;
            $promo->start_date = $request->start_date;
            $promo->end_date = $request->end_date;
            $promo->usage_limit = $request->usage_limit;
            $promo->is_active = $request->is_active ?? $promo->is_active;
            
            if ($request->hasFile('image')) {
                // Remove old image if exists
                if ($promo->image && file_exists(public_path($promo->image))) {
                    unlink(public_path($promo->image));
                }
                
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/promos'), $imageName);
                $promo->image = 'images/promos/' . $imageName;
            }
            
            $promo->save();
            
            DB::commit();
            return redirect()->route('admin.promos.index')
                ->with('success', 'Promotion updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error updating promotion: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified promotion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $promo = Promo::findOrFail($id);
            
            // Remove image if exists
            if ($promo->image && file_exists(public_path($promo->image))) {
                unlink(public_path($promo->image));
            }
            
            $promo->delete();
            
            DB::commit();
            return redirect()->route('admin.promos.index')
                ->with('success', 'Promotion deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error deleting promotion: ' . $e->getMessage());
        }
    }

    /**
     * Toggle promotion status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->is_active = !$promo->is_active;
        $promo->save();
        
        return redirect()->back()
            ->with('success', 'Promotion status updated successfully');
    }

    /**
     * Validate promo code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validatePromo(Request $request)
    {
        $code = $request->code;
        $total = $request->total ?? 0;
        
        $promo = Promo::where('code', $code)
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
        
        if (!$promo) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired promo code'
            ]);
        }
        
        // Check usage limit
        if ($promo->usage_limit > 0 && $promo->usage_count >= $promo->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo code usage limit reached'
            ]);
        }
        
        // Calculate discount
        $discount = 0;
        if ($promo->discount_type == 'percentage') {
            $discount = ($total * $promo->discount_value) / 100;
        } else {
            $discount = $promo->discount_value;
        }
        
        return response()->json([
            'valid' => true,
            'message' => 'Promo code applied successfully',
            'discount' => $discount,
            'promo' => $promo
        ]);
    }
}