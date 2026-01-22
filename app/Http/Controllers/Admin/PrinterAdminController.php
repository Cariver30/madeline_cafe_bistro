<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\PrintTemplate;
use App\Models\PrinterRoute;
use Illuminate\Http\Request;

class PrinterAdminController extends Controller
{
    public function storePrinter(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Printer::create([
            'name' => $data['name'],
            'model' => $data['model'] ?? null,
            'location' => $data['location'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function updatePrinter(Request $request, Printer $printer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $printer->update([
            'name' => $data['name'],
            'model' => $data['model'] ?? null,
            'location' => $data['location'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function destroyPrinter(Printer $printer)
    {
        $printer->delete();

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:ticket,label'],
            'body' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PrintTemplate::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'body' => $data['body'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function updateTemplate(Request $request, PrintTemplate $printTemplate)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:ticket,label'],
            'body' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $printTemplate->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'body' => $data['body'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function destroyTemplate(PrintTemplate $printTemplate)
    {
        $printTemplate->delete();

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function storeRoute(Request $request)
    {
        $data = $request->validate([
            'printer_id' => ['required', 'exists:printers,id'],
            'print_template_id' => ['required', 'exists:print_templates,id'],
            'category_scope' => ['required', 'string', 'in:all,menu,cocktails,wines'],
            'category_id' => ['nullable', 'integer'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        PrinterRoute::create([
            'printer_id' => $data['printer_id'],
            'print_template_id' => $data['print_template_id'],
            'category_scope' => $data['category_scope'],
            'category_id' => $data['category_id'] ?? null,
            'enabled' => $data['enabled'] ?? true,
        ]);

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }

    public function destroyRoute(PrinterRoute $printerRoute)
    {
        $printerRoute->delete();

        return redirect()->route('admin.new-panel', ['section' => 'printers']);
    }
}
