<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Document extends Model
{

    protected $casts =
        [
            'before' => 'array',
            'after'  => 'array',
        ];

    public static function boot()
    {
        parent::boot();

        static::updating(function ($document) {

            $document->adjust();
            //            $document->adjustments()->attach(auth()->id(),
            //                [
            //                    'before' => json_encode(array_intersect_key($document->fresh()->toArray(), $document->getDirty())),
            ////gets only the attributes that are changed
            //                    'after' => json_encode($document->getDirty()),
            //                ]);
        });
    }

    public function adjustments()
    {
        return $this->belongsToMany(User::class, 'adjustments')
            ->withTimestamps()
            ->withPivot(['before', 'after'])
            //leaving just latest() will refer to Users table created_at
            ->latest('pivot_updated_at');
    }

    public function adjust($userId = null, $diff = null)
    {
        $userId = $userId ?: auth()->id();

        $diff = $diff ?: $this->getDiff();

        $changed = $this->getDirty();

        return $this->adjustments()->attach($userId, $diff);
    }

    protected function getDiff()
    {
        $changed = $this->getDirty();

        $before = json_encode(array_intersect_key($this->fresh()->toArray(), $changed));
        $after  = json_encode($changed);

        return compact('before', 'after');

    }

}
