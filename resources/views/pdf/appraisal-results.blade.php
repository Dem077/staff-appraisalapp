<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Staff Appraisal Results</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.75;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #059669;
            margin-bottom: 12px;
        }
        .header-table td {
            border: none;
            padding: 0 0 10px 0;
            vertical-align: middle;
        }
        .logo {
            height: 65px;
            width: auto;
        }
        .document-title {
            font-size: 19px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 1.4;
        }
        .form-name {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            margin-top: 4px;
            line-height: 1.75;
        }
        .meta-table {
            width: 100%;
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.75;
            vertical-align: top;
            border: none;
        }
        .meta-label {
            font-weight: bold;
            color: #047857;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.75;
        }
        .data-table th {
            background-color: #047857;
            color: #ffffff;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #065f46;
            font-size: 12px;
            line-height: 1.75;
        }
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            vertical-align: top;
            background-color: #ffffff;
            line-height: 1.75;
        }
        .category-row td {
            background-color: #d1fae5;
            font-weight: bold;
            color: #047857;
            font-size: 12px;
            line-height: 1.75;
            border: 1px solid #6ee7b7;
            padding: 8px 6px;
        }
        .score-cell {
            text-align: center;
            font-weight: bold;
            color: #059669;
        }
        .indicator-en {
            margin-bottom: 6px;
        }
        .totals-row td {
            background-color: #ecfdf5;
            font-weight: bold;
            color: #047857;
            border: 1px solid #6ee7b7;
            padding: 8px 6px;
            font-size: 12px;
            line-height: 1.75;
        }
        .grand-total-row td {
            background-color: #047857;
            font-weight: bold;
            color: #ffffff;
            border: 2px solid #065f46;
            padding: 8px 6px;
            font-size: 12px;
            line-height: 1.75;
        }
        .grand-total-row .score-cell {
            color: #ffffff;
        }
        .comments-table {
            width: 100%;
            margin-top: 15px;
        }
        .comments-table td {
            border: none;
            padding: 0 0 8px 0;
            vertical-align: top;
        }
        .comment-box {
            background-color: #f8fafc;
            padding: 10px 12px;
            border-left: 4px solid #6b7280;
        }
        .comment-box-staff {
            border-left-color: #059669;
        }
        .comment-box-supervisor {
            border-left-color: #dc2626;
        }
        .comment-box-hr {
            border-left-color: #059669;
        }
        .comment-label {
            font-weight: bold;
            color: #047857;
            font-size: 12px;
            line-height: 1.75;
            margin-bottom: 6px;
        }
        .comment-text {
            color: #374151;
            font-size: 12px;
            line-height: 1.75;
        }
        .comment-text table {
            width: 100%;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-table td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 10px 8px;
            border: none;
        }
        .signature-placeholder {
            font-size: 11px;
            color: #cccccc;
            margin-bottom: 25px;
            line-height: 1.75;
        }
        .signature-line {
            border-top: 1px solid #000000;
            padding-top: 4px;
            font-weight: bold;
            font-size: 12px;
            line-height: 1.75;
        }
        .signature-date {
            font-size: 12px;
            color: #666666;
            margin-top: 4px;
            line-height: 1.75;
        }
        .status-badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            font-size: 12px;
            line-height: 1.75;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <htmlpagefooter name="appraisalFooter">
        <div style="text-align: center; font-size: 11px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 4px; line-height: 1.5;">
            <div>Generated on {{ date('F d, Y \a\t h:i A') }} | Confidential Document</div>
            <div style="margin-top: 2px;">Page {PAGENO} of {nbpg}</div>
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="appraisalFooter" value="on" />

    @php
        $logoPath = public_path('images/agrologo.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="18%" align="left" valign="middle">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                @endif
            </td>
            <td width="64%" align="center" valign="middle">
                <div class="document-title">Staff Appraisal Report</div>
                <div class="form-name">@pdfText($assigned->appraisalForm->name ?? 'Performance Appraisal Form')</div>
            </td>
            <td width="18%">&nbsp;</td>
        </tr>
    </table>

    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="50%">
                <span class="meta-label">Employee Name:</span>
                @pdfText($assigned->staff->name ?? '-')
            </td>
            <td width="50%">
                <span class="meta-label">Employee ID:</span>
                {{ $assigned->staff->emp_no ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Supervisor:</span>
                @pdfText($assigned->supervisor->name ?? '-')
            </td>
            <td>
                <span class="meta-label">Appraisal Date:</span>
                {{ $assigned->assigned_date ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Form ID:</span>
                #{{ $assigned->id }}
            </td>
            <td>
                <span class="meta-label">Status:</span>
                <span class="status-badge {{ $assigned->status->value == 'complete' ? '' : 'status-pending' }}">
                    {{ $assigned->status->getLabel() ?? ucfirst(str_replace('_', ' ', $assigned->status->value)) }}
                </span>
            </td>
        </tr>
    </table>

    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="50%">Behavioral Indicator</th>
                <th width="8%">Self Score</th>
                <th width="8%">Supervisor Score</th>
                <th width="34%">Supervisor Comment</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandStaffTotal = 0;
                $grandSupervisorTotal = 0;
                $grandCount = 0;
            @endphp
            @foreach($entries->groupBy(function ($e) {
                return $e->question?->appraisalFormKeyBehavior?->appraisalFormCategory?->name ?? 'General';
            }) as $category => $categoryEntries)
                <tr class="category-row">
                    <td colspan="4">@pdfText($category)</td>
                </tr>
                @php
                    $staffTotal = 0;
                    $supervisorTotal = 0;
                    $count = 0;
                @endphp
                @foreach($categoryEntries as $entry)
                    <tr>
                        <td>
                            <div class="indicator-en">{{ $entry->question?->behavioral_indicators ?? '' }}</div>
                            @if($entry->question?->dhivehi_behavioral_indicators)
                                @pdfThaanaIndicator($entry->question->dhivehi_behavioral_indicators)
                            @endif
                        </td>
                        <td class="score-cell">{{ $entry->staff_score ?? '-' }}</td>
                        <td class="score-cell">{{ $entry->supervisor_score ?? '-' }}</td>
                        <td>{{ $entry->supervisor_comment ?? '-' }}</td>
                    </tr>
                    @php
                        if ($entry->staff_score) {
                            $staffTotal += $entry->staff_score;
                            $supervisorTotal += $entry->supervisor_score ?? 0;
                            $count++;
                        }
                    @endphp
                @endforeach
                @if($count > 0)
                    <tr class="totals-row">
                        <td><strong>Section Total %</strong></td>
                        <td class="score-cell">{{ number_format(($staffTotal / ($count * 5)) * 100, 1) }}%</td>
                        <td class="score-cell">{{ number_format(($supervisorTotal / ($count * 5)) * 100, 1) }}%</td>
                        <td></td>
                    </tr>
                    @php
                        $grandStaffTotal += $staffTotal;
                        $grandSupervisorTotal += $supervisorTotal;
                        $grandCount += $count;
                    @endphp
                @endif
            @endforeach
            @if($grandCount > 0)
                <tr class="grand-total-row">
                    <td><strong>GRAND TOTAL %</strong></td>
                    <td class="score-cell">{{ number_format(($grandStaffTotal / ($grandCount * 5)) * 100, 1) }}%</td>
                    <td class="score-cell">{{ number_format(($grandSupervisorTotal / ($grandCount * 5)) * 100, 1) }}%</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="comments-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="comment-box comment-box-staff">
                    <div class="comment-label">Employee Comments:</div>
                    <div class="comment-text">@pdfThaana($assigned->staff_comment ?? 'No comments provided.')</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="comment-box comment-box-supervisor">
                    <div class="comment-label">Supervisor Comments:</div>
                    <div class="comment-text">@pdfThaana($assigned->supervisor_comment ?? 'No comments provided.')</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="comment-box comment-box-hr">
                    <div class="comment-label">HR Comments:</div>
                    <div class="comment-text">@pdfThaana($assigned->hr_comment ?? 'No comments provided.')</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="signature-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="signature-placeholder">Signature</div>
                <div class="signature-line">@pdfText($assigned->staff->name ?? '')</div>
                <div class="signature-date">Date: ______________</div>
            </td>
            <td>
                <div class="signature-placeholder">Signature</div>
                <div class="signature-line">@pdfText($assigned->supervisor->name ?? '')</div>
                <div class="signature-date">Date: ______________</div>
            </td>
            <td>
                <div class="signature-placeholder">Signature</div>
                <div class="signature-line">HR Signature</div>
                <div class="signature-date">Date: ______________</div>
            </td>
        </tr>
    </table>
</body>
</html>
