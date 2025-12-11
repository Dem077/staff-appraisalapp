<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Staff Appraisal Results</title>
    <style>
        @font-face {
            font-family: 'faruma';
            src: url('{{ public_path("fonts/Faruma.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .arabic-text {
            font-family: 'faruma', DejaVu Sans, Arial, sans-serif;
            direction: rtl;
            font-size: 14px;
            color: #666;
            margin-top: 4px;
        }
        .header {
            display: block;
            margin-bottom: 20px;
            padding-bottom: 15px;
            padding-top: 0;
            margin-top: 0;
            border-bottom: 3px solid #059669;
            position: relative;
            min-height: 80px;
        }
        .logo-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 150px;
        }
        .logo {
            max-height: 70px;
            margin: 0;
            padding: 0;
            display: block;
            width: auto;
        }
        .header-content {
            text-align: center;
            padding-top: 5px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #047857;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-name {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }
        .meta-section {
            background: #f0fdf4;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 4px solid #059669;
        }
        .meta-row {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #047857;
            display: inline-block;
            width: 120px;
            font-size: 11px;
        }
        .meta-value {
            color: #374151;
            font-size: 11px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 300px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        th {
            background: #047857;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #065f46;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            vertical-align: top;
            background: white;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .category-row {
            background: #d1fae5;
            font-weight: bold;
            color: #047857;
            font-size: 11px;
        }
        .category-row td {
            padding: 8px 6px;
            border: 1px solid #6ee7b7;
        }
        .score-cell {
            text-align: center;
            font-weight: bold;
            color: #059669;
            font-size: 11px;
        }
        .comments-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .comment-box {
            background: #f8fafc;
            padding: 10px 12px;
            margin-bottom: 10px;
            border-radius: 4px;
            border-left: 4px solid #6b7280;
        }
        .comment-box.staff-comment {
            border-left-color: #059669;
        }
        .comment-box.supervisor-comment {
            border-left-color: #dc2626;
        }
        .comment-box.hr-comment {
            border-left-color: #059669;
        }
        .comment-label {
            font-weight: bold;
            color: #047857;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .comment-text {
            color: #374151;
            line-height: 1.6;
            font-size: 11px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-complete {
            background: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .totals-row {
            background: #ecfdf5;
            font-weight: bold;
            color: #047857;
            font-size: 11px;
            border: 1px solid #6ee7b7;
        }
        .totals-row td {
            padding: 8px 6px;
            border: 1px solid #6ee7b7;
            background: #ecfdf5;
        }
        .grand-total-row {
            background: #047857;
            font-weight: bold;
            color: white;
            font-size: 12px;
            border: 2px solid #065f46;
        }
        .grand-total-row td {
            padding: 10px 6px;
            border: 2px solid #065f46;
            background: #047857;
            color: white;
        }
        .grand-total-row .score-cell {
            color: white;
            font-size: 12px;
        }
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .signature-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px 10px;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 5px;
            min-height: 40px;
            position: relative;
        }
        .signature-line::before {
            content: 'Signature';
            display: block;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 14px;
            color: #000;
            opacity: 0.2;
            font-weight: bold;
            letter-spacing: 1px;
            white-space: nowrap;
        }
        .signature-label {
            font-weight: bold;
            font-size: 11px;
            color: #333;
            margin-top: 5px;
        }
        .signature-date {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            @php
                $logoPath = public_path('images/agrologo.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoMime = 'image/png';
                    $logoSrc = 'data:' . $logoMime . ';base64,' . $logoData;
                } else {
                    $logoSrc = '';
                }
            @endphp
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Company Logo" class="logo">
            @endif
        </div>

        <div class="header-content">
            <div class="document-title">Staff Appraisal Report</div>
            <div class="form-name">{{ $assigned->appraisalForm->name ?? 'Performance Appraisal Form' }}</div>
        </div>
    </div>

    <div class="meta-section">
        <div class="meta-row">
            <span class="meta-label">Employee Name:</span>
            <span class="meta-value">{{ $assigned->staff->name ?? '-' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Employee ID:</span>
            <span class="meta-value">{{ $assigned->staff->emp_no ?? '-' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Supervisor:</span>
            <span class="meta-value">{{ $assigned->supervisor->name ?? '-' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Appraisal Date:</span>
            <span class="meta-value">{{ $assigned->assigned_date ?? '-' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Form ID:</span>
            <span class="meta-value">#{{ $assigned->id }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Status:</span>
            <span class="meta-value">
                <span class="status-badge {{ $assigned->status->value == 'complete' ? 'status-complete' : 'status-pending' }}">
                    {{ $assigned->status->getLabel() ?? ucfirst(str_replace('_', ' ', $assigned->status->value)) }}
                </span>
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:50%">Behavioral Indicator</th>
                <th style="width:8%">Self Score</th>
                <th style="width:8%">Supervisor Score</th>
                <th style="width:34%">Supervisor Comment</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandStaffTotal = 0;
                $grandSupervisorTotal = 0;
                $grandCount = 0;
            @endphp
            @foreach($entries->groupBy(function($e){ return $e->question->appraisalFormKeyBehavior->appraisalFormCategory->name ?? 'General'; }) as $category => $categoryEntries)
                <tr class="category-row">
                    <td colspan="4">{{ $category }}</td>
                </tr>
                @php
                    $staffTotal = 0;
                    $supervisorTotal = 0;
                    $count = 0;
                @endphp
                @foreach($categoryEntries as $entry)
                    <tr>
                        <td>
                            <div>{{ $entry->question->behavioral_indicators ?? '' }}</div>
                            @if($entry->question->dhivehi_behavioral_indicators ?? null)
                                <div class="arabic-text">{{ $entry->question->dhivehi_behavioral_indicators }}</div>
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

    <div class="comments-section">
        <div class="comment-box staff-comment">
            <div class="comment-label">Employee Comments:</div>
            <div class="comment-text">{{ $assigned->staff_comment ?? 'No comments provided.' }}</div>
        </div>

        <div class="comment-box supervisor-comment">
            <div class="comment-label">Supervisor Comments:</div>
            <div class="comment-text">{{ $assigned->supervisor_comment ?? 'No comments provided.' }}</div>
        </div>

        <div class="comment-box hr-comment">
            <div class="comment-label">HR Comments:</div>
            <div class="comment-text">{{ $assigned->hr_comment ?? 'No comments provided.' }}</div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-grid">
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-label">{{ $assigned->staff->name ?? '' }} </div>
                <div class="signature-date">Date: ______________</div>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-label">{{$assigned->supervisor->name?? '' }}</div>
                <div class="signature-date">Date: ______________</div>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-label">HR Signature</div>
                <div class="signature-date">Date: ______________</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Generated on {{ date('F d, Y \a\t h:i A') }} | Confidential Document
    </div>
</body>
</html>
