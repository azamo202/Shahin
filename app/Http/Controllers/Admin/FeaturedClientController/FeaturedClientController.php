<?php

namespace App\Http\Controllers\Admin\FeaturedClientController;

use App\Http\Controllers\Controller;
use App\Models\FeaturedClient;
use Illuminate\Http\Request;

class FeaturedClientController extends Controller
{
    // جلب كل العملاء
    public function index()
    {
        $clients = FeaturedClient::latest()->get();
        $clients->transform(function ($client) {
            $client->logo = asset('storage/' . $client->logo);
            return $client;
        });
        return response()->json($clients);
    }

    // إضافة عميل جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'logo' => 'required|image',
            'website' => 'nullable|url',
        ]);

        $path = $request->file('logo')->store('clients', 'public');

        $client = FeaturedClient::create([
            'name' => $request->name,
            'logo' => $path,
            'website' => $request->website,
        ]);

        $client->logo = asset('storage/' . $path);

        return response()->json($client, 201);
    }

    // حذف عميل
    public function destroy($id)
    {
        $client = FeaturedClient::findOrFail($id);
        $client->delete();

        return response()->json(['message' => 'تم حذف العميل بنجاح']);
    }
}
