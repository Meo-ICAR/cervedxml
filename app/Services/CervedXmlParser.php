// app/Services/CervedXmlParser.php
class CervedXmlParser
{
    public static function parse($xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        return [
            'company' => [
                'name' => (string) $xml->ItemInformation->CompanyInformation->CompanyName,
                'tax_code' => (string) $xml->ItemInformation->CompanyInformation->TaxCode,
                // Estrai indirizzi, date, ecc. [file:1]
            ],
            'directors' => self::extractDirectors($xml),
            'subsidiaries' => self::extractSubsidiaries($xml),
            'balance_sheets' => self::extractBalanceSheets($xml),
        ];
    }
    private static function extractDirectors($xml) { /* Logica per OfficialDirectors */ }
}
