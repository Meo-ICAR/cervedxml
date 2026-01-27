<?php
// app/Services/CervedXmlParser.php

namespace App\Services;

use SimpleXMLElement;

class CervedXmlParser
{
    /**
     * Parsa l'XML Cerved e restituisce array strutturato per Blade/PDF.
     * @param string $xmlString Contenuto XML
     * @return array Dati formattati
     */
    public static function parseCervedXml(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString);
        if (!$xml)
            throw new \Exception('XML non valido');

        $data = [
            'company' => self::extractCompany($xml),
            'directors' => self::extractDirectors($xml),
            'subsidiaries' => self::extractSubsidiaries($xml),
            'balance_sheets' => self::extractBalanceSheets($xml),
            'address' => self::extractAddress($xml),
            'activity' => self::extractActivity($xml),
            'offices' => self::extractOffices($xml),  // NUOVO
            'employees' => self::extractEmployees($xml),  // NUOVO
            'events' => self::extractEvents($xml),  // NUOVO
            'indicators' => self::extractIndicators($xml),  // NUOVO
            'special_sections' => self::extractSpecialSections($xml),
            'voices_pivot' => self::extractBalanceVoicesPivot($xml),
            'company_details' => self::extractCompanyDetails($xml), // NUOVO
            'shareholders' => self::extractShareholders($xml), // NUOVO
            'beneficial_owners' => self::extractBeneficialOwners($xml), // NUOVO
            'payline' => self::extractPayline($xml), // NUOVO
            'cebi' => self::extractCebi($xml), // NUOVO
            'gen' => self::extractCustomGen($xml), // NUOVO: dati generati da noi
        ];
        \Log::debug('Cerved parsed FULL', ['data' => $data]);

        return $data;
    }

    public static function extractCompany(SimpleXMLElement $xml): array
    {
        $itemInfo = $xml->ItemInformation;
        $info = $itemInfo->CompanyInformation ?? null;

        if (!$info) {
            return [];
        }

        return [
            'name' => (string) ($info->CompanyName ?? ''),
            'official_name' => (string) ($itemInfo->OfficialCompanyName ?? ''),
            'tax_code' => (string) ($info->TaxCode ?? ''),
            'vat' => (string) ($info->VATRegistrationNo ?? ''),
            'id' => (string) ($info->IDCompany ?? ''),
            'rea' => (string) ($info->ReaCode->REANo ?? ''),
            'activity_status' => (string) ($itemInfo->ActivityStatusDescription ?? ''),
            'incorporation_date' => self::formatDate($itemInfo->IncorporationDate ?? null),
        ];
    }

    private static function extractDirectors(SimpleXMLElement $xml): array
    {
        $directors = [];
        // Path adjusted: OfficialDirectors is at root (LightReport -> OfficialDirectors)
        foreach ($xml->OfficialDirectors->Director ?? [] as $director) {
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
                ], $individual->IndividualPosition ? ((is_array($individual->IndividualPosition) ? $individual->IndividualPosition : [$individual->IndividualPosition])) : []), // Handle single or multiple gracefully if SimpleXML behaves oddly, though foreach matches array access usually. Wait, SimpleXML element isn't an array. We access children.
            ];

            // Fix loop for positions. $individual->IndividualPosition returns an object that is iterable if multiple, or single object.
            // array_map on SimpleXMLElement might not work as expected if it's not converted to array properly.
            // Let's rewrite the positions extraction for safety.
        }

        // Re-implementing loop for safety and clarity
        $directors = [];
        foreach ($xml->OfficialDirectors->Director ?? [] as $director) {
             $individual = $director->Individual;
             $positions = [];
             // IndividualPosition is sibling of Individual, child of Director
             foreach ($director->IndividualPosition ?? [] as $pos) {
                 $positions[] = [
                     'type' => (string) $pos->Type,
                     'start_date' => self::formatDate($pos->StartDate),
                     'duration' => (string) $pos->Duration ?? '',
                 ];
             }

             $directors[] = [
                'id' => (string) $individual->IDPerson,
                'name' => trim((string) $individual->FirstName . ' ' . $individual->LastName),
                'tax_code' => (string) $individual->TaxCode,
                'birth_date' => self::formatDate($individual->BirthDate),
                'birth_place' => (string) $individual->BirthPlace,
                'positions' => $positions,
            ];
        }
        return $directors;
    }

    private static function extractSubsidiaries(SimpleXMLElement $xml): array
    {
        $subs = [];
        // Path adjusted: Subsidiaries is at root
        foreach ($xml->Subsidiaries->Othersubsidiaries ?? [] as $sub) {
            $info = $sub->OthersubsidiarieInfos;
            // $info might be just a string in the provided XML: <OthersubsidiarieInfos>LAPREZIOSA ROSA</OthersubsidiarieInfos>
            // Check provided XML. <OthersubsidiarieInfos>LAPREZIOSA ROSA</OthersubsidiarieInfos>.
            // It seems name is the value of the node.

            $name = (string)$info;

            $subs[] = [
                'name' => $name,
                'tax_code' => (string) $sub->TaxCode, // TaxCode is sibling of Infos
                'position_code' => (string) $sub->MainPositionCode,
                'position' => (string) $sub->MainPosition,
            ];
        }
        return $subs;
    }

    private static function extractBalanceSheets(SimpleXMLElement $xml): array
    {
        $sheets = [];
        foreach ($xml->xpath('//BalanceSheetRatios') ?? [] as $sheet) {
            // Values are elements, e.g. <Sales><Value>235</Value></Sales>
            $sheets[] = [
                'year' => (string) $sheet->ReferenceYear,
                'closing_date' => self::formatDate($sheet->ClosingDate),
                'sales' => (string) ($sheet->Voices->Sales->Value ?? 'ND'),
                'operating_profit' => (string) ($sheet->Voices->OperatingProfit->Value ?? 'ND'),
                'roi' => (string) ($sheet->Voices->Roi->Value ?? 'ND'),
                'roe' => (string) ($sheet->Voices->Roe->Value ?? 'ND'),
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
        // Path adjusted: Activity is at root
        $activity = $xml->Activity;
        return [
            'description' => (string) $activity->ActivityDescription,
            'ateco' => (string) ($xml->ItemInformation->ISTATCode['Code'] ?? ''), // ISTATCode is child of ItemInformation
            'start_date' => self::formatDate($xml->ItemInformation->ActivityStartDate),
        ];
    }

    // Sostituisci la funzione formatDate() in CervedXmlParser.php

    private static function formatDate($dateNode): string
    {
        // Controllo null + cast a stringa per sicurezza
        if (!$dateNode || !($dateNode instanceof \SimpleXMLElement)) {
            return '';
        }

        $day = (int) ($dateNode['day'] ?? 0);
        $month = (int) ($dateNode['month'] ?? 0);
        $year = (int) ($dateNode['year'] ?? 0);

        // Verifica che tutti i campi data siano validi
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < 1900) {
            return '';
        }

        return sprintf('%02d/%02d/%04d', $day, $month, $year);
    }

    /**
     * Estrae sedi e unità locali (8 nel tuo file).
     */
    private static function extractOffices(SimpleXMLElement $xml): array
    {
        $offices = [];
        // Path adjusted: Offices is at root
        // Handle RegisteredOffice
        $registered = $xml->Offices->RegisteredOffice;
        if ($registered) {
             $addr = $registered->RegisteredOfficeAddress;
             $offices[] = [
                'local_unit' => 'Sede Legale',
                'type' => 'Sede Legale',
                'brand_name' => '',
                'street' => (string) $addr->Street,
                'post_code' => (string) $addr->PostCode,
                'municipality' => (string) $addr->Municipality,
                'opening_date' => self::formatDate($registered->ActivityStartDate),
             ];
        }

        // Handle BusinessUnit if present
        foreach ($xml->Offices->BusinessUnit ?? [] as $unit) {
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
        // Path adjusted: CompanyEmployee is at root
        $emp = $xml->CompanyEmployee;
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

            // Mappa voices chiave-valore (solo quelle con Value child)
            foreach ($voices->children() as $voice) {
                // Use ->Value accessor
                if (isset($voice->Value)) {
                    $key = (string) $voice->getName();
                    $pivot[$key][$year] = (float) ($voice->Value ?? 0);
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
    public static function extractCompanyDetails(SimpleXMLElement $xml): array
    {
        $info = $xml->ItemInformation->CompanyInformation ?? null;
        $itemInfo = $xml->ItemInformation ?? null;
        return [
            'capital_auth' => (float) ($itemInfo->AuthorizedEquityCapital ?? 0),
            'capital_sub' => (float) ($itemInfo->SubscribedEquityCapital ?? 0),
            'capital_paid' => (float) ($itemInfo->PaidUpEquityCapital ?? 0),
            'legal_form' => (string) ($info->CompanyForm['LegalFormDescription'] ?? ''),
        ];
    }

    public static function extractShareholders(SimpleXMLElement $xml): array
    {
        $shareholders = [];
        // LightReport -> Shareholders -> ShareholdersList -> Shareholder
        foreach ($xml->Shareholders->ShareholdersList->Shareholder ?? [] as $sh) {
            $item = $sh->ShareholderItem->IndividualInformation ?? $sh->ShareholderItem->CompanyInformation ?? null;
            if (!$item) continue;

            $shareholders[] = [
                'name' => trim((string)($item->Name ?? $item->CompanyName)),
                'tax_code' => (string)($item->TaxCode ?? ''),
                'shares_percent' => (float) ($sh->PercentageShares ?? 0),
                'shares_value' => (float) ($sh->ValueOfPart ?? 0),
                'currency' => (string) ($sh->CurrencyValueOfPart ?? 'EURO'),
            ];
        }
        return $shareholders;
    }

    public static function extractBeneficialOwners(SimpleXMLElement $xml): array
    {
        $owners = [];
        // LightReport -> BeneficialOwnersV2 -> BeneficialOwner
        foreach ($xml->BeneficialOwnersV2->BeneficialOwner ?? [] as $bo) {
            $owners[] = [
                'name' => trim((string)$bo->FirstName . ' ' . (string)$bo->LastName),
                'tax_code' => (string)$bo->TaxCode,
                'birth_date' => self::formatDate($bo->BirthDate),
                'birth_place' => (string)$bo->BirthPlace,
                'address' => trim((string)$bo->Toponym . ' ' . (string)$bo->Address . ' ' . (string)$bo->StreetNumber),
                'municipality' => (string)$bo->Municipality,
                'province' => (string)($bo->Province['Code'] ?? ''),
                'share_percent' => (float) ($bo->CumulativeSharePercentage ?? 0),
            ];
        }
        return $owners;
    }

    public static function extractPayline(SimpleXMLElement $xml): array
    {
        $payline = $xml->PaymentExperiencePayline ?? null;
        if (!$payline) return [];

        return [
            'score' => (int) ($payline->Score ?? 0),
            'trend_label' => (string) ($payline->TrendLabel ?? ''),
            'experiences_count' => (int) ($payline->Summary->Synthesis->NumberExperiences ?? 0),
            'total_amount' => (float) ($payline->Summary->Synthesis->TotalAmount ?? 0),
            'average_days' => (int) ($payline->Summary->Synthesis->EffectivePaymentDays ?? 0),
            'grading' => (int) ($xml->ItemInformation->PaylineGrading->Grading ?? 0), // Sometimes in ItemInfo or separate
        ];
    }

    public static function extractCebi(SimpleXMLElement $xml): array
    {
        $cebi = $xml->OverallAssessment->Cebi4PD ?? null;
        return [
             'pd_all_geo' => (float) ($cebi->PdAllGeoEiOverL ?? 0),
             'rating_median' => (string) ($cebi->RatingMedianGeoSector ?? ''),
        ];
    }


    public static function extractCustomGen(SimpleXMLElement $xml): array
    {
        return [
            'ecofin_giudizio' => (string) ($xml->{'ECOFIN-Giudizio'} ?? ''),
            'ecofin_indice' => (string) ($xml->{'ECOFIN-Indice'} ?? ''),
            'annotazioni' => (string) ($xml->Annotazioni ?? ''),
        ];
    }
}
