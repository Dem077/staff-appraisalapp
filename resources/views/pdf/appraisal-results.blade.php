<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Appraisal Results</title>
    <style>
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
                <th style="width:60%">Behavioral Indicator</th>
                <th style="width:10%">Self Score</th>
                <th style="width:10%">Supervisor Score</th>
                <th style="width:20%">Supervisor Comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries->groupBy(function($e){ return $e->question->appraisalFormKeyBehavior->appraisalFormCategory->name ?? 'General'; }) as $category => $categoryEntries)
                <tr class="category-row">
                    <td colspan="4">{{ $category }}</td>
                </tr>
                @foreach($categoryEntries as $entry)
                    <tr>
                        <td>{{ $entry->question->behavioral_indicators ?? '' }}</td>
                        <td class="score-cell">{{ $entry->staff_score ?? '-' }}</td>
                        <td class="score-cell">{{ $entry->supervisor_score ?? '-' }}</td>
                        <td>{{ $entry->supervisor_comment ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
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

    <div class="footer">
        Generated on {{ date('F d, Y \a\t h:i A') }} | Confidential Document
    </div>
</body>
</html>
