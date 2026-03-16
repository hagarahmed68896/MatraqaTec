<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            __('Order Number'),
            __('Customer Name'),
            __('Customer Phone'),
            __('Service'),
            __('Service Category'),
            __('Technician'),
            __('Maintenance Company'),
            __('Status'),
            __('Total Price'),
            __('Created At'),
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->user->name ?? '-',
            $order->user->phone ?? '-',
            $order->service->name_ar ?? '-',
            $order->service->parent->name_ar ?? '-',
            $order->technician->user->name ?? '-',
            $order->maintenanceCompany->user->name ?? '-',
            __('order.' . $order->status),
            $order->total_price,
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}
