<?php
// app/Services/CervedXmlParser.php

namespace App\Services;

use SimpleXMLElement;

class CervedXmlParser
{
    /**
     * Parsa l'XML Cerved e restituisce array strutturato per Blade/PDF.
     * @param string $xmlString Contenuto XML
     * @return array Dati formattati [file:1]
     */
    public static function parseCervedXml(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString);
        if (!$xml) {
            throw new \Exception('XML non valido');
        }

        return [
            'company' => self::extractCompany($xml),
            'directors' => self::extractDirectors($xml),
            'subsidiaries' => self::extractSubsidiaries($xml),
            'balance_sheets' => self::extractBalanceSheets($xml),
            'address' => self::extractAddress($xml),
            'activity' => self::extractActivity($xml),
        ];
    }

    private static function extractCompany(SimpleXMLElement $xml): array
    {
        $companyInfo = $xml->ItemInformation->CompanyInformation;
        return [
            'name' => (string) $companyInfo->CompanyName,
            'official_name' => (string) $companyInfo->OfficialCompanyName ?? $companyInfo->CompanyName,
            'tax_code' => (string) $companyInfo->TaxCode,
            'vat' => (string) $companyInfo->VATRegistrationNo,
            'id' => (string) $companyInfo->IDCompany,
            'rea' => (string) $companyInfo->ReaCode->REANo ?? '',
            'activity_status' => (string) $companyInfo->ActivityStatusDescription,
            'incorporation_date' => self::formatDate($companyInfo->IncorporationDate),
        ];
    }

    private static function extractDirectors(SimpleXMLElement $xml): array
    {
        $directors = [];
        foreach ($xml->ItemInformation->OfficialDirectors->Director ?? [] as $director) {
            $individual = $director->Individual;
            $directors[] = [
                'id' => (string) $individual->IDPerson,
                'name' => trim((string) $individual->FirstName . ' ' . $individual->LastName),
                'tax_code' => (string) $individual->TaxCode,
                'birth_date' => self::formatDate($individual->BirthDate),
                'birth_place' => (string) $individual->BirthPlace,
                'positions' => array_map(fn($pos) => [
                    'type' => (string) $pos->Type,
                    'start_date' => self::formatDate($pos->StartDate),
                    'duration' => (string) $pos->Duration ?? '',
                ], $individual->IndividualPosition ?? []),
            ];
        }
        return $directors;
    }

    private static function extractSubsidiaries(SimpleXMLElement $xml): array
    {
        $subs = [];
        foreach ($xml->LightReport->Subsidiaries->Othersubsidiaries ?? [] as $sub) {
            $info = $sub->OthersubsidiarieInfos;
            $subs[] = [
                'name' => (string) $info,
                'tax_code' => (string) $info->TaxCode,
                'position_code' => (string) $info->MainPositionCode,
                'position' => (string) $info->MainPosition,
            ];
        }
        return $subs; 
    }

    private static function extractBalanceSheets(SimpleXMLElement $xml): array
    {
        $sheets = [];
        foreach ($xml->xpath('//BalanceSheetRatios') ?? [] as $sheet) {
            $sheets[] = [
                'year' => (string) $sheet->ReferenceYear,
                'closing_date' => self::formatDate($sheet->ClosingDate),
                'sales' => (string) ($sheet->Voices->Sales['Value'] ?? 'ND'),
                'operating_profit' => (string) ($sheet->Voices->OperatingProfit['Value'] ?? 'ND'),
                'roi' => (string) ($sheet->Voices->Roi['Value'] ?? 'ND'),
                'roe' => (string) ($sheet->Voices->Roe['Value'] ?? 'ND'),
            ];
        }
        return $sheets; 
    }

    private static function extractAddress(SimpleXMLElement $xml): array
    {
        $addr = $xml->ItemInformation->CompanyInformation->Address;
        return [
            'street' => (string) $addr->Street,
            'post_code' => (string) $addr->PostCode,
            'municipality' => (string) $addr->Municipality,
            'province' => (string) $addr->{'Province'}['Code'] ?? '',
        ]; 
    }

    private static function extractActivity(SimpleXMLElement $xml): array
    {
        $activity = $xml->ItemInformation->Activity;
        return [
            'description' => (string) $activity->ActivityDescription,
            'ateco' => (string) ($activity->ISTATCode['Code'] ?? ''),
            'start_date' => self::formatDate($activity->ActivityStartDate),
        ]; 
    }

    private static function formatDate(SimpleXMLElement $dateNode): string
    {
        $day = (int) ($dateNode['day'] ?? 0);
        $month = (int) ($dateNode['month'] ?? 0);
        $year = (int) ($dateNode['year'] ?? 0);
        return $day && $month && $year ? sprintf('%02d/%02d/%d', $day, $month, $year) : '';
    }

    // Aggiungi a app/Services/CervedXmlParser.php

/**
 * Estrae sedi e unità locali (8 nel tuo file).
 */
private static function extractOffices(SimpleXMLElement $xml): array
{
    $offices = [];
    foreach ($xml->ItemInformation->Offices->BusinessUnit ?? [] as $unit) {
        $officeAddr = $unit->OfficeAddress;
        $offices[] = [
            'local_unit' => (string) $unit->LocalUnit,
            'type' => (string) $unit->Type,
            'brand_name' => (string) $unit->BrandName ?? '',
            'street' => (string) $officeAddr->Street,
            'post_code' => (string) $officeAddr->PostCode,
            'municipality' => (string) $officeAddr->Municipality,
            'opening_date' => self::formatDate($unit->BranchOpeningDate),
        ];
    }
    return $offices; 
}

/**
 * Estrae dati dipendenti (138 nel 2024, trend +34.78%).
 */
private static function extractEmployees(SimpleXMLElement $xml): array
{
    $emp = $xml->ItemInformation->CompanyEmployee;
    return [
        'year' => (string) $emp->Year,
        'total' => (int) ($emp->NumberOfEmployees ?? 0),
        'staff' => (int) ($emp->StaffEmployeesNumber ?? 0),
        'trend' => (float) ($emp->EmployeesTrend ?? 0),
        'last_quarter' => (string) $emp->LastQuarter,
    ]; 
}

/**
 * Estrae eventi straordinari (1 trasferimento 2014).
 */
private static function extractEvents(SimpleXMLElement $xml): array
{
    $events = [];
    foreach ($xml->ItemInformation->ExtraordinaryEvents->ExtraordinaryEventsList->EventItem ?? [] as $event) {
        $events[] = [
            'type' => (string) $event->Type,
            'transfer_type' => (string) $event->TransferType,
            'date' => self::formatDate($event->DeedDate),
            'register_date' => self::formatDate($event->RegisterDate),
            'notary' => (string) $event->Notary,
            'transferee' => (string) $event->TransfereeName ?? '',
        ];
    }
    return $events; 
}

/**
 * Estrae indicatori Cerved (score 74, Cebi 4).
 */
private static function extractIndicators(SimpleXMLElement $xml): array
{
    return [
        'cerved_group_score' => (int) ($xml->xpath('//CervedGroupScore/Score')[0] ?? 0),
        'negative_events_grading' => (int) ($xml->xpath('//NegativeEventsGrading/Grading')[0] ?? 0),
        'credit_requests_12m' => (int) ($xml->xpath('//CreditRequestsLastTwelveMonths')[0] ?? 0),
        'grantable_credit' => (float) ($xml->xpath('//CreditLimit/Value')[0] ?? 0),
    ]; 
}

/**
 * Aggiorna parseCervedXml() per includere tutto.
 */
public static function parseCervedXml2(string $xmlString): array
{
    $xml = simplexml_load_string($xmlString);
    if (!$xml) throw new \Exception('XML non valido');

    return [
        'company' => self::extractCompany($xml),
        'directors' => self::extractDirectors($xml),
        'subsidiaries' => self::extractSubsidiaries($xml),
        'balance_sheets' => self::extractBalanceSheets($xml),
        'address' => self::extractAddress($xml),
        'activity' => self::extractActivity($xml),
        'offices' => self::extractOffices($xml),      // NUOVO
        'employees' => self::extractEmployees($xml),   // NUOVO
        'events' => self::extractEvents($xml),         // NUOVO
        'indicators' => self::extractIndicators($xml), // NUOVO
        'special_sections' => self::extractSpecialSections($xml),
    ];
}

private static function extractSpecialSections(SimpleXMLElement $xml): array
{
    $sections = [];
    foreach ($xml->xpath('//SpecialSection') ?? [] as $section) {
        $sections[] = [
            'code' => (string) $section->Code,
            'description' => (string) $section->description,
            'inscription_date' => self::formatDate($section->FirstInscriptionInSection),
        ];
    }
    return $sections; 
}

// Aggiungi a CervedXmlParser.php

/**
 * Estrae Voices da BalanceSheetRatios in pivot table (anni colonne).
 * Voce | 2021 | 2022 | 2023
 */
public static function extractBalanceVoicesPivot(SimpleXMLElement $xml): array
{
    $ratios = $xml->xpath('//CompanyDevelopmentIndicators/BalanceSheetRatios') ?? [];
    $pivot = [];
    $years = [];

    foreach ($ratios as $ratio) {
        $year = (string) $ratio->ReferenceYear;
        $years[$year] = true;
        $voices = $ratio->Voices;

        // Mappa voices chiave-valore (solo quelle con Value numerico)
        foreach ($voices->children() as $voice) {
            if (isset($voice['Value'])) {
                $key = (string) $voice->getName();
                $pivot[$key][$year] = (float) ($voice['Value'] ?? 0);
            }
        }
    }

    // Ordina anni
    ksort($years);
    $sortedYears = array_keys($years);

    return [
        'pivot' => $pivot,
        'years' => $sortedYears,
        'voices_list' => array_keys($pivot),
    ]; 
}

}
