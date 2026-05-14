<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    }

    public function collection()
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');
        
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Customer Name',
            'Customer Email',
            'Total Price',
            'Status',
            'Payment Method',
            'Date Created',
        ];
    }

    public function map($order): array
    {
        return [
            (string) ($order->_id ?? $order->id),
            $order->user->name ?? 'Guest',
            $order->user->email ?? 'N/A',
            $order->total_price,
            ucfirst($order->status),
            $order->payment_method ?? 'N/A',
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
