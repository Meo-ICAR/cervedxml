{{-- resources/views/xml-print.blade.php --}}
<div class="print-section">
    <h2>Sedi ({{ count($data['offices']) }})</h2>
    <table>
        @foreach($data['offices'] as $office)
        <tr>
            <td>{{ $office['brand_name'] ?: 'Ufficio' }}</td>
            <td>{{ $office['street'] }}, {{ $office['municipality'] }}</td>
            <td>Apertura: {{ $office['opening_date'] }}</td>
        </tr>
        @endforeach
    </table>

    <h2>Dipendenti</h2>
    <p>{{ $data['employees']['total'] }} nel {{ $data['employees']['year'] }} (trend {{ $data['employees']['trend'] }}%).</p> [file:1]

    <h2>Eventi</h2>
    @if(!empty($data['events']))
    <ul>
        @foreach($data['events'] as $event)
        <li>{{ $event['type'] }}: {{ $event['transferee'] }} ({{ $event['date'] }})</li>
        @endforeach
    </ul>
    @endif

<table class="balance-pivot">
    <thead>
        <tr><th>Voce</th>
            @foreach($voicesPivot['years'] as $year)
            <th>{{ $year }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($voicesPivot['voices_list'] as $voice)
        <tr>
            <td>{{ ucfirst(str_replace(['Value', 'Profit'], '', $voice)) }}</td>
            @foreach($voicesPivot['years'] as $year)
            <td>{{ number_format($voicesPivot['pivot'][$voice][$year] ?? 'ND', 0) }}k</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>



    <h2>Indicatori Cerved</h2>
    <ul>
        <li>Score Gruppo: {{ $data['indicators']['cerved_group_score'] }}</li>
        <li>Richieste credito 12m: {{ $data['indicators']['credit_requests_12m'] }}</li>
        <li>Credito concedibile: €{{ number_format($data['indicators']['grantable_credit']) }}</li>
    </ul>

    <h2>Sezioni Speciali Registro</h2>
    <ul>
        @foreach($data['special_sections'] as $sec)
        <li>{{ $sec['description'] }} (dal {{ $sec['inscription_date'] }})</li>
        @endforeach
    </ul> [file:1]
</div>
