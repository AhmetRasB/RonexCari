<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $notifications = [];
        
        // Kritik stok uyarıları
        $lowStockProducts = Product::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('initial_stock', '<=', 'critical_stock')
            ->orderBy('initial_stock')
            ->limit(3)
            ->get(['id','name','initial_stock','critical_stock']);

        foreach ($lowStockProducts as $product) {
            $notifications[] = [
                'type' => 'critical_stock',
                'title' => 'Kritik Stok Uyarısı',
                'message' => "{$product->name} stok seviyesi kritik ({$product->initial_stock}/{$product->critical_stock})",
                'icon' => 'solar:danger-triangle-outline',
                'color' => 'danger',
                'time' => 'Şimdi',
                'link' => route('products.edit', $product->id) . '?focus=stock'
            ];
        }

        // Vadesi yaklaşan alış faturaları (7 gün içinde)
        $now = Carbon::today();
        $in7 = Carbon::today()->addDays(7);
        
        $duePurchases = PurchaseInvoice::whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $in7])
            ->where('payment_completed', false)
            ->with('supplier')
            ->orderBy('due_date')
            ->limit(3)
            ->get();

        foreach ($duePurchases as $invoice) {
            $daysLeft = Carbon::today()->diffInDays($invoice->due_date, false);
            $notifications[] = [
                'type' => 'due_purchase',
                'title' => 'Vadesi Yaklaşan Alış Faturası',
                'message' => $invoice->invoice_number . ' - ' . ($invoice->supplier->name ?? 'Tedarikçi') . ' (' . $daysLeft . ' gün kaldı)',
                'icon' => 'solar:calendar-outline',
                'color' => 'warning',
                'time' => $invoice->due_date->format('d.m.Y'),
                'link' => route('purchases.invoices.show', $invoice->id)
            ];
        }

        // Rastgele karıştır
        shuffle($notifications);
        
        // Maksimum 5 bildirim göster
        $notifications = array_slice($notifications, 0, 5);

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }
}