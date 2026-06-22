<?php

namespace App\Services;

use App\Models\AppraisalFormAssignedToStaff;
use Illuminate\Support\Collection;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

class AppraisalPdfService
{
    public function download(AppraisalFormAssignedToStaff $assigned, Collection $entries, array $summary, bool $asAttachment = false): \Symfony\Component\HttpFoundation\Response
    {
        $html = view('pdf.appraisal-results', [
            'assigned' => $assigned,
            'entries' => $entries,
            'summary' => $summary,
        ])->render();

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html, HTMLParserMode::DEFAULT_MODE);

        $filename = 'appraisal-results-'.$assigned->id.'.pdf';
        $disposition = $asAttachment ? 'attachment' : 'inline';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }

    protected function createMpdf(): Mpdf
    {
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 18,
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'faruma' => [
                    'R' => 'Faruma.ttf',
                ],
            ],
            'default_font' => 'dejavusans',
            'biDirectional' => true,
            'shrink_tables_to_fit' => 1,
            'keep_table_proportions' => true,
            'use_kwt' => true,
        ]);
    }
}
