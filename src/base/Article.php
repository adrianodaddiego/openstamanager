<?php

namespace Base;

use Modules\Articoli\Articolo as Original;
use Illuminate\Database\Eloquent\Builder;

abstract class Article extends Row
{
    protected $serialRowID = 'documento';

    abstract public function movimenta($qta);

    protected static function boot()
    {
        parent::boot(true);

        static::addGlobalScope('articles', function (Builder $builder) {
            $builder->whereNotNull('idarticolo')->where('idarticolo', '<>', 0);
        });
    }

    public static function new(Original $articolo)
    {
        $model = parent::new();

        $model->articolo()->associate($articolo);

        $model->descrizione = $articolo->descrizione;
        $model->abilita_serial = $articolo->abilita_serial;
        $model->um = $articolo->um;

        return $model;
    }

    public function setSerials($serials)
    {
        database()->sync('mg_prodotti', [
            'id_riga_'.$this->serialRowID => $this->id,
            'dir' => 'entrata',
            'id_articolo' => $this->idarticolo,
        ], [
            'serial' => $serials,
        ]);
    }

    public function getSerialsAttribute()
    {
        if (empty($this->abilita_serial)) {
            return [];
        }

        // Individuazione dei seriali
        $list = database()->fetchArray('SELECT serial FROM mg_prodotti WHERE serial IS NOT NULL AND id_riga_'.$this->serialRowID.' = '.prepare($this->id));

        return array_column($list, 'serial');
    }

    public function setQtaAttribute($value)
    {
        $previous = $this->qta;

        $this->attributes['qta'] = $value;

        $diff = $value - $previous;
        $this->movimenta($diff);

        $database = database();

        // Se c'è un collegamento ad un ddt, aggiorno la quantità evasa
        if (!empty($this->idddt)) {
            $database->query('UPDATE dt_righe_ddt SET qta_evasa = qta_evasa + '.$diff.' WHERE descrizione = '.prepare($this->descrizione).' AND idarticolo = '.prepare($this->idarticolo).' AND idddt = '.prepare($this->idddt).' AND idiva = '.prepare($this->idiva));
        }

        // Se c'è un collegamento ad un ordine, aggiorno la quantità evasa
        if (!empty($this->idordine)) {
            $database->query('UPDATE or_righe_ordini SET qta_evasa = qta_evasa + '.$diff.' WHERE descrizione = '.prepare($this->descrizione).' AND idarticolo = '.prepare($this->idarticolo).' AND idordine = '.prepare($this->idordine).' AND idiva = '.prepare($this->idiva));
        }
    }

    public function articolo()
    {
        return $this->belongsTo(Original::class, 'idarticolo');
    }
}
