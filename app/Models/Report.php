<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Report extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'piva',
        'is_racese',
        'annotation',
        'id_soggetto',
        'codice_score',
        'descrizione_score',
        'valore',
        'categoria_codice',
        'categoria_descrizione',
        'status',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_racese' => 'boolean',
        'valore' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [];

    /**
     * Get the user that owns the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Register media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('xml_files')
            ->acceptsMimeTypes(['application/xml', 'text/xml'])
            ->singleFile();

        $this
            ->addMediaCollection('xml_completo')
            ->acceptsMimeTypes(['application/xml', 'text/xml'])
            ->singleFile();
    }

    /** Get the XML files associated with the report. */

    /*
     * public function getXmlFilesAttribute()
     * {
     *     return $this->getMedia('xml_files');
     * }
     */

    /** Get the complete XML file associated with the report. */

    /*
     * public function getXmlCompletoAttribute()
     * {
     *     return $this->getFirstMedia('xml_completo');
     * }
     */

    /**
     * Add an XML file to the report.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function addXmlFile($file, $piva)
    {
        try {
            $nomefile = $piva . '.xml';

            // Debug: verifica il file temporaneo
            \Log::info('File temporaneo: ' . $file->getPathname());
            \Log::info('File esiste: ' . (file_exists($file->getPathname()) ? 'Sì' : 'No'));
            \Log::info('File dimensione: ' . $file->getSize());

            // Verifica che la directory di destinazione esista
            $destinationPath = storage_path('app/public/xml_files');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $media = $this
                ->addMedia($file)
                ->usingFileName($nomefile)
                ->usingName($nomefile)
                ->toMediaCollection('xml_files');

            // Verifica che il file sia stato salvato
            $savedPath = $media->getPath();
            \Log::info('Percorso salvato: ' . $savedPath);
            \Log::info('File salvato esiste: ' . (file_exists($savedPath) ? 'Sì' : 'No'));

            if (!file_exists($savedPath)) {
                throw new \Exception('File non salvato correttamente');
            }

            return $media;
        } catch (\Exception $e) {
            \Log::error('Errore in addXmlFile: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Get the formatted (pretty-print) content of the complete XML.
     *
     * @return string|null
     */
    public function getFormattedXmlCompleto()
    {
        $media = $this->getFirstMedia('xml_completo');

        if (!$media || !file_exists($media->getPath())) {
            return null;
        }

        $xmlContent = file_get_contents($media->getPath());

        try {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmlContent);

            return $dom->saveXML();
        } catch (\Exception $e) {
            \Log::error("Errore durante la formattazione dell'XML: " . $e->getMessage());

            return $xmlContent;
        }
    }

    /**
     * Get the XML content as an HTML table structure.
     *
     * @return string|null
     */
    public function getXmlAsHtmlTable()
    {
        $media = $this->getFirstMedia('xml_completo');

        if (!$media || !file_exists($media->getPath())) {
            return null;
        }

        $xmlContent = file_get_contents($media->getPath());

        try {
            $xml = new \SimpleXMLElement($xmlContent);
            return $this->renderXmlElementAsTable($xml);
        } catch (\Exception $e) {
            \Log::error('Errore durante la conversione XML in Tabella: ' . $e->getMessage());
            return "<p>Errore nel caricamento dell'anteprima.</p>";
        }
    }

    /**
     * Recursively render XML element as HTML table.
     */
    private function renderXmlElementAsTable($element)
    {
        $html = '<table class="xml-table" style="width:100%; border-collapse: collapse; border: 1px solid #ddd; margin-bottom: 5px; font-size: 0.9rem;">';

        // Attributes
        foreach ($element->attributes() as $a => $b) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 4px; background-color: #f2f2f2; font-weight: bold; font-style: italic; width: 25%;">@' . htmlspecialchars($a) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 4px;">' . htmlspecialchars((string) $b) . '</td>';
            $html .= '</tr>';
        }

        foreach ($element->children() as $child) {
            $name = $child->getName();

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 4px; background-color: #f9f9f9; font-weight: bold; width: 25%;">' . htmlspecialchars($name) . '</td>';

            if ($child->count() > 0) {
                $html .= '<td style="border: 1px solid #ddd; padding: 4px;">' . $this->renderXmlElementAsTable($child) . '</td>';
            } else {
                $html .= '<td style="border: 1px solid #ddd; padding: 4px;">' . htmlspecialchars((string) $child) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Generate a complete XML file by adding valore and annotation to the existing XML.
     *
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function generateCompleteXml()
    {
        $originalMedia = $this->getFirstMedia('xml_files');

        if (!$originalMedia) {
            throw new \Exception('File XML originale non trovato.');
        }

        $xmlContent = file_get_contents($originalMedia->getPath());
        $this->status = 'Generated';
        $this->save();

        try {
            $xml = new \SimpleXMLElement($xmlContent);

            // Aggiunge i nuovi tag in coda (all'interno del nodo radice)
            $xml->addChild('ECOFIN-Giudizio', htmlspecialchars((string) $this->categoria_descrizione));
            $xml->addChild('ECOFIN-Indice', htmlspecialchars((string) $this->valore));
            $xml->addChild('Annotazioni', htmlspecialchars((string) $this->annotation));

            $tempFile = tempnam(sys_get_temp_dir(), 'xml_completo');
            $xml->asXML($tempFile);

            $this->clearMediaCollection('xml_completo');

            $media = $this
                ->addMedia($tempFile)
                ->usingFileName('XML_completo.xml')
                ->usingName('XML Completo')
                ->toMediaCollection('xml_completo');

            return $media;
        } catch (\Exception $e) {
            \Log::error('Errore durante la generazione del file XML completo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determina se il report è completo
     */
    public function isComplete(): bool
    {
        return !empty($this->name) && $this->hasMedia('xml_files');
    }

    /**
     * Determina se il report è compilato (ha XML completo)
     */
    public function isCompiled(): bool
    {
        return $this->hasMedia('xml_completo');  // Corretto da "this->" a "$this->"
    }

    public function getXmlUrlAttribute()
    {
        if (!$this->hasMedia('xml_files')) {
            return null;
        }

        return route('xml.download', $this->piva);
    }

    public function getXmlMediaUrlAttribute()
    {
        $media = $this->getFirstMedia('xml_files');
        if (!$media) {
            return null;
        }

        return route('media.download', [$media->id, $media->file_name]);
    }
}
