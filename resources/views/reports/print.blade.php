<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'portrait' }};
            margin: 20mm 15mm 25mm 15mm;
            
            @top-center {
                content: "{{ $header ?? '' }}";
                font-size: 9px;
                color: #666;
            }
            
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9px;
                color: #666;
            }
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            font-size: 10px; 
            color: #333;
        }
        
        /* Header Section */
        .report-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00A1AA;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: 700;
            color: #003D43;
            margin: 0;
            text-align: {{ $titleAlign ?? 'center' }};
        }
        
        .report-meta {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
            text-align: {{ $titleAlign ?? 'center' }};
        }
        
        /* Table Styling */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            page-break-inside: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        th, td { 
            border: 1px solid #ccc; 
            padding: 5px 8px; 
            text-align: left; 
            font-size: 9px;
        }
        
        th { 
            background: #00A1AA; 
            color: white; 
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }
        
        tr:nth-child(even) { 
            background: #f8f9fa; 
        }
        
        .text-right { 
            text-align: right; 
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Print-specific */
        @media print {
            body { 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                max-width: 1000px;
                margin: 20px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            
            .print-container {
                background: white;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .screen-footer {
                margin-top: 20px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                font-size: 10px;
                color: #666;
                text-align: center;
            }
        }
        
        @media print {
            .screen-footer {
                display: none;
            }
            .print-container {
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1 class="report-title">{{ $title }}</h1>
            <div class="report-meta">
                Generated: {{ now()->format('d/m/Y H:i') }} | Total: {{ $data->count() }} records
                @if(!empty($appliedFilters))
                | Filters: {{ $appliedFilters }}
                @endif
            </div>
        </div>
        
        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">#</th>
                    @foreach($columns as $col)
                    <th>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @foreach($columns as $key => $col)
                        @php
                            $value = $row->{$key};
                            $isNumber = isset($col['type']) && $col['type'] === 'number';
                            if (isset($col['type'])) {
                                if ($col['type'] === 'date' && $value) $value = $value->format('d/m/Y');
                                elseif ($col['type'] === 'datetime' && $value) $value = $value->format('d/m/Y H:i');
                                elseif ($col['type'] === 'number' && $value) $value = number_format($value, 0, ',', '.');
                                elseif ($col['type'] === 'boolean') $value = $value ? 'Yes' : 'No';
                            }
                        @endphp
                    <td class="{{ $isNumber ? 'text-right' : '' }}">{{ $value ?? '-' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Screen footer (only visible before printing) -->
        <div class="screen-footer">
            <em>Press Ctrl+P (or Cmd+P on Mac) to print or save as PDF. Page numbers will appear automatically when printing.</em>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
