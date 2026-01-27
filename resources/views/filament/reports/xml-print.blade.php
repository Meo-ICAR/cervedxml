{{-- resources/views/xml-print.blade.php --}}
<!DOCTYPE html>
<html>
<head><style>@media print { body { font-size: 10pt; } table { border-collapse: collapse; } }</style></head>
<body>
    <h1>{{ $data['company']['name'] }}</h1>
    <table>
        <tr><td>Partita IVA:</td><td>{{ $data['company']['tax_code'] }}</td></tr>
    </table>
    
    ## Direttori
    <table>
        <thead><tr><th>Nome</th><th>Ruolo</th></tr></thead>
        <tbody>
            @foreach($data['directors'] as $director)
            <tr><td>{{ $director['name'] }}</td><td>{{ $director['position'] }}</td></tr>
            @endforeach
        </tbody>
    </table> {{-- Adatta per Subsidiaries e Bilanci [file:1] --}}
</body>
</html>
