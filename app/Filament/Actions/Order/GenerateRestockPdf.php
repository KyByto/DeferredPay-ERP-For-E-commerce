<?php

namespace App\Filament\Actions\Order;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateRestockPdf
{
    public function __invoke(Collection $orders): StreamedResponse
    {
        $pdf = Pdf::loadView('pdf.restock-list', ['orders' => $orders]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'restock-list-' . now()->format('Y-m-d-His') . '.pdf');
    }
}
