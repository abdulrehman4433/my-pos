<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_type',
        'module_id',
        'file_name',
        'file_path',
        'file_type',
        'file_extension',
    ];

    /**
     * Polymorphic relationship to any module
     */
    public function module()
    {
        return $this->morphTo(null, 'module_type', 'module_id');
    }
}


// use App\Models\Attachment;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// public function uploadAttachment(Request $request)
// {
//     $request->validate([
//         'file' => 'required|file|max:10240', // max 10MB
//         'module_type' => 'required|string',
//         'module_id' => 'required|integer',
//     ]);

//     $file = $request->file('file');

//     $path = $file->store('attachments'); // stores in storage/app/attachments

//     $attachment = Attachment::create([
//         'module_type' => $request->module_type,
//         'module_id' => $request->module_id,
//         'file_name' => $file->getClientOriginalName(),
//         'file_path' => $path,
//         'file_type' => $file->getMimeType(),
//         'file_extension' => $file->getClientOriginalExtension(),
//     ]);

//     return response()->json(['success' => true, 'attachment' => $attachment]);
// }

// //Retrieving attachments for a module
// $attachments = Attachment::where('module_type', 'product')
//                          ->where('module_id', $product->id)
//                          ->get();


//Optional: Polymorphic Relationship in Modules
// public function attachments()
// {
//     return $this->morphMany(Attachment::class, 'module', 'module_type', 'module_id');
// }
// $product->attachments; // all attachments for this product
