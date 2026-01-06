<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        return view('setting.index');
    }

    public function show()
    {
        return Setting::first();
    }

    public function update(Request $request)
    {
        // Validate the token
        $token = $request->input('_token');
        if (!$this->validateToken($token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the first setting record
        $setting = Setting::first();
        
        if (!$setting) {
            $setting = new Setting();
        }

        // Update columns according to your payload field names
        $setting->company_name      = $request->input('nama_perusahaan');
        $setting->phone             = $request->input('telepon');
        $setting->address           = $request->input('alamat');
        $setting->discount          = $request->input('diskon', 0); // default 0
        $setting->receipt_type      = $request->input('tipe_nota');

        // Handle logo upload - expecting base64 or binary data
        if ($request->has('path_logo')) {
            $logoData = $request->input('path_logo');
            
            // Check if it's base64 encoded
            if (preg_match('/^data:image\/(\w+);base64,/', $logoData, $matches)) {
                $imageData = substr($logoData, strpos($logoData, ',') + 1);
                $imageData = base64_decode($imageData);
                $extension = $matches[1];
            } else {
                // Assume it's raw binary data
                $imageData = $logoData;
                $extension = 'png'; // default extension
            }
            
            $filename = 'logo-' . now()->format('YmdHis') . '.' . $extension;
            file_put_contents(public_path('img/' . $filename), $imageData);
            $setting->logo_path = "/img/$filename";
        }

        // Handle member card upload - expecting base64 or binary data
        if ($request->has('path_kartu_member')) {
            $cardData = $request->input('path_kartu_member');
            
            // Check if it's base64 encoded
            if (preg_match('/^data:image\/(\w+);base64,/', $cardData, $matches)) {
                $imageData = substr($cardData, strpos($cardData, ',') + 1);
                $imageData = base64_decode($imageData);
                $extension = $matches[1];
            } else {
                // Assume it's raw binary data
                $imageData = $cardData;
                $extension = 'png'; // default extension
            }
            
            $filename = 'member-card-' . now()->format('YmdHis') . '.' . $extension;
            file_put_contents(public_path('img/' . $filename), $imageData);
            $setting->member_card_path = "/img/$filename";
        }

        // Save changes
        $setting->save();

        return response()->json(['message' => 'Data saved successfully'], 200);
    }

    // Helper function to validate token
    private function validateToken($token)
    {
        // Implement your token validation logic here
        // For example, check against a stored token or use Laravel's built-in CSRF
        return $token === 'XQb6VBgafqS6D4ABMBAY3jKT66bkwixidHuOGLIC';
    }
}
