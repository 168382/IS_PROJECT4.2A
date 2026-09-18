<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdminReportExport implements FromArray, WithColumnWidths, WithTitle
{
    public function __construct(private array $report) {}

    public function array(): array
    {
        $rows = [
            ['Lost & Found Administrative Report'],
            ['Generated', $this->report['generatedAt']],
            [],
            ['Summary metric', 'Count'],
        ];

        foreach ($this->report['summary'] as $label => $value) {
            $rows[] = [$label, $value];
        }

        $rows[] = [];
        $rows[] = ['Claims'];
        $rows[] = ['Claim ID', 'Status', 'Claimant', 'Found item', 'Submitted'];
        foreach ($this->report['claims'] as $claim) {
            $rows[] = [
                'CLM-'.$claim->id,
                ucfirst($claim->status),
                $claim->user?->name ?? 'User #'.$claim->user_id,
                $claim->foundItem?->item_name ?? 'Item #'.$claim->found_item_id,
                $claim->created_at,
            ];
        }

        $rows[] = [];
        $rows[] = ['Lost items'];
        $rows[] = ['Item ID', 'Item', 'Location', 'Date lost', 'Status', 'Reported by'];
        foreach ($this->report['lostItems'] as $item) {
            $rows[] = [
                'LST-'.$item->id,
                $item->item_name,
                $item->location_lost,
                $item->date_lost,
                ucfirst($item->status),
                $item->user?->name ?? 'User #'.$item->user_id,
            ];
        }

        $rows[] = [];
        $rows[] = ['Found items'];
        $rows[] = ['Item ID', 'Item', 'Location', 'Date found', 'Status', 'Reported by'];
        foreach ($this->report['foundItems'] as $item) {
            $rows[] = [
                'FND-'.$item->id,
                $item->item_name,
                $item->location_found,
                $item->date_found,
                ucfirst($item->status),
                $item->user?->name ?? 'User #'.$item->user_id,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 28, 'C' => 28, 'D' => 22, 'E' => 20, 'F' => 24];
    }

    public function title(): string
    {
        return 'Admin report';
    }
}
