<?php

namespace App\Http\Controllers;

use App\Models\Kemas;
use App\Services\KemasReaderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KemasController extends Controller
{
    public function index()
    {
        $kemas = Kemas::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Kemas/Index', [
            'kemas' => $kemas
        ]);
    }

    public function importPage()
    {
        return Inertia::render('Kemas/Import');
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->store('temp');
        $fullPath = storage_path('app/' . $path);

        $readerService = new KemasReaderService();
        $rows = $readerService->processKemasData($fullPath);

        // Clean up temporary file
        unlink($fullPath);

        return response()->json(['data' => $rows]);
    }

    public function saveImported(Request $request)
    {
        $data = $request->validate([
            'rows' => 'required|array',
        ]);
        $saved = [];
        foreach ($data['rows'] as $row) {
            $saved[] = Kemas::create($row);
        }
        return response()->json(['success' => true, 'saved' => $saved]);
    }

    public function destroy($id)
    {
        try {
            $kemas = Kemas::findOrFail($id);
            $kemas->delete();
            
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
