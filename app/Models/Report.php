<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Report extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

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
        'idsoggetto',
        'codice_score',
        'descrizione_score',
        'valore',
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
    protected $appends = ['xml_files'];

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
    }

    /**
     * Get the XML files associated with the report.
     */
    public function getXmlFilesAttribute()
    {
        return $this->getMedia('xml_files');
    }

    /**
     * Add an XML file to the report.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function addXmlFile($file)
    {
        return $this->addMedia($file)->toMediaCollection('xml_files');
    }
}
