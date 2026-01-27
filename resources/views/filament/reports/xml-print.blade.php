{{-- resources/views/xml-print.blade.php --}}
<div class="print-section">
    <div class="header">
        <h1>{{ $data['company']['name'] }}</h1>
        <p>
            <strong>P.IVA:</strong> {{ $data['company']['vat'] }} |
            <strong>CF:</strong> {{ $data['company']['tax_code'] }} |
            <strong>REA:</strong> {{ $data['company']['rea'] }}
        </p>
        <p>
            <strong>Sede Legale:</strong> {{ $data['address']['street'] }}, {{ $data['address']['post_code'] }} {{ $data['address']['municipality'] }} ({{ $data['address']['province'] }})
        </p>
        <p>
            <strong>Stato:</strong> {{ $data['company']['activity_status'] }} |
            <strong>Costituzione:</strong> {{ $data['company']['incorporation_date'] }}
        </p>
    </div>

    <hr>

    <div class="company-details" style="margin-bottom: 20px;">
        <h3>Dettagli Societari</h3>
        <p>
            <strong>Forma Giuridica:</strong> {{ $data['company_details']['legal_form'] ?? '' }}<br>
            <strong>Capitale Sociale:</strong>
            Deliberato: €{{ number_format($data['company_details']['capital_auth'] ?? 0, 0, ',', '.') }} |
            Sottoscritto: €{{ number_format($data['company_details']['capital_sub'] ?? 0, 0, ',', '.') }} |
            Versato: €{{ number_format($data['company_details']['capital_paid'] ?? 0, 0, ',', '.') }}
        </p>
        @if(!empty($data['gen']['ecofin_giudizio']))
        <div style="margin-top: 10px; background-color: #f8fafc; padding: 10px; border-radius: 5px; border: 1px solid #e2e8f0;">
            <p style="margin: 0;">
                <strong>ECOFIN Giudizio:</strong> {{ $data['gen']['ecofin_giudizio'] }}
                @if(!empty($data['gen']['ecofin_indice']))
                (Indice: {{ $data['gen']['ecofin_indice'] }})
                @endif
            </p>
            @if(!empty($data['gen']['annotazioni']))
            <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #64748b;">
                <strong>Note:</strong> {{ $data['gen']['annotazioni'] }}
            </p>
            @endif
        </div>
        @endif
    </div>

    @if(!empty($data['payline']) || !empty($data['cebi']))
    <div class="reliability-details" style="margin-bottom: 20px;">
        <h3>Affidabilità e Pagamenti</h3>
        @if(!empty($data['payline']))
        <p>
            <strong>Payline Score:</strong> {{ $data['payline']['score'] }} ({{ $data['payline']['trend_label'] }})<br>
            <strong>Esperienze di Pagamento:</strong> {{ $data['payline']['experiences_count'] }} (Totale: €{{ number_format($data['payline']['total_amount'], 2, ',', '.') }})<br>
            <strong>Ritardo Medio:</strong> {{ $data['payline']['average_days'] }} giorni
        </p>
        @endif
        @if(!empty($data['cebi']))
        <p>
            <strong>Cebi PD (Probabilità Default):</strong> {{ $data['cebi']['pd_all_geo'] }}%<br>
            <strong>Rating Settore:</strong> {{ $data['cebi']['rating_median'] }}
        </p>
        @endif
    </div>
    @endif

    @if(!empty($data['shareholders']))
    <h3>Soci</h3>
    <ul>
        @foreach($data['shareholders'] as $sh)
        <li>
            <strong>{{ $sh['name'] }}</strong> ({{ $sh['tax_code'] }})<br>
            Quote: {{ $sh['shares_percent'] }}% (Valore: €{{ number_format($sh['shares_value'], 2, ',', '.') }} {{ $sh['currency'] }})
        </li>
        @endforeach
    </ul>
    @endif

    @if(!empty($data['beneficial_owners']))
    <h3>Titolari Effettivi</h3>
    <ul>
        @foreach($data['beneficial_owners'] as $bo)
        <li>
            <strong>{{ $bo['name'] }}</strong> ({{ $bo['tax_code'] }})<br>
            Nato a {{ $bo['birth_place'] }} il {{ $bo['birth_date'] }}<br>
            Residenza: {{ $bo['address'] }}, {{ $bo['municipality'] }} ({{ $bo['province'] }})<br>
            Quota: {{ $bo['share_percent'] }}%
        </li>
        @endforeach
    </ul>
    @endif

    <hr>

    <h2>Attività</h2>
    <p>
        <strong>Descrizione:</strong> {{ $data['activity']['description'] }}<br>
        <strong>Codice ATECO:</strong> {{ $data['activity']['ateco'] }}<br>
        <strong>Inizio Attività:</strong> {{ $data['activity']['start_date'] }}
    </p>

    @if(!empty($data['directors']))
    <h2>Amministratori e Cariche</h2>
    <ul>
        @foreach($data['directors'] as $director)
        <li>
            <strong>{{ $director['name'] }}</strong> ({{ $director['tax_code'] }})
            @if(!empty($director['positions']))
            <ul>
                @foreach($director['positions'] as $pos)
                <li>{{ $pos['type'] }} (dal {{ $pos['start_date'] }})</li>
                @endforeach
            </ul>
            @endif
        </li>
        @endforeach
    </ul>
    @endif

    @if(!empty($data['subsidiaries']))
    <h2>Partecipazioni / Società Correlate</h2>
    <ul>
        @foreach($data['subsidiaries'] as $sub)
        <li>
            <strong>{{ $sub['name'] }}</strong> - {{ $sub['position'] }} ({{ $sub['position_code'] }})
        </li>
        @endforeach
    </ul>
    @endif

    <hr>

    <h2>Sedi e Unità Locali ({{ count($data['offices']) }})</h2>
    @if(!empty($data['offices']))
    <table>
        @foreach($data['offices'] as $office)
        <tr>
            <td>{{ $office['brand_name'] ?: 'Ufficio' }} ({{ $office['type'] }})</td>
            <td>{{ $office['street'] }}, {{ $office['municipality'] }} ({{ $office['post_code'] }})</td>
            <td>Apertura: {{ $office['opening_date'] }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <h2>Dipendenti</h2>
    <p>{{ $data['employees']['total'] }} nel {{ $data['employees']['year'] }} (trend {{ $data['employees']['trend'] }}%, Staff: {{ $data['employees']['staff'] }}).</p>

    @if(!empty($data['events']))
    <h2>Eventi</h2>
    <ul>
        @foreach($data['events'] as $event)
        <li>{{ $event['type'] }}: {{ $event['transferee'] }} ({{ $event['date'] }})</li>
        @endforeach
    </ul>
    @endif

    <h2>Dati di Bilancio</h2>
    <table class="balance-pivot">
        <thead>
            <tr><th>Voce</th>
                @foreach($data['voices_pivot']['years'] as $year)
                <th>{{ $year }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data['voices_pivot']['voices_list'] as $voice)
            <tr>
                <td>{{ ucfirst(str_replace(['Value', 'Profit'], '', $voice)) }}</td>
                @foreach($data['voices_pivot']['years'] as $year)
                    @php
                        $val = $data['voices_pivot']['pivot'][$voice][$year] ?? null;
                    @endphp
                    <td>{{ is_numeric($val) ? number_format($val, 0) : '--' }}</td>
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
        <li>Grading Eventi Negativi: {{ $data['indicators']['negative_events_grading'] }}</li>
    </ul>

    @if(!empty($data['special_sections']))
    <h2>Sezioni Speciali Registro</h2>
    <ul>
        @foreach($data['special_sections'] as $sec)
        <li>{{ $sec['description'] }} (dal {{ $sec['inscription_date'] }})</li>
        @endforeach
    </ul>
    @endif
</div>
