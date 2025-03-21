<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Promo;

class HomeController extends Controller
{
    /**
     * Display the customer homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get featured products
        $featuredProducts = Product::where('is_featured', 1)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
            
        // Get latest products
        $latestProducts = Product::where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();
            
        // Get active categories
        $categories = Category::where('is_active', 1)
            ->orderBy('position', 'asc')
            ->get();
            
        // Get active promotions
        $promos = Promo::where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->take(3)
            ->get();
            
        return view('customer.home', compact(
            'featuredProducts',
            'latestProducts',
            'categories',
            'promos'
        ));
    }
    
    /**
     * Display about us page.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        return view('customer.about');
    }
    
    /**
     * Display contact page.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact()
    {
        return view('customer.contact');
    }
    
    /**
     * Handle contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        // Process the contact form (send email, save to database, etc.)
        // This is just a placeholder - implement according to your needs
        
        return redirect()->back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }
    
    /**
     * Search products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $products = Product::where('is_active', 1)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->paginate(12);
            
        return view('customer.search-results', compact('products', 'query'));
    }
}