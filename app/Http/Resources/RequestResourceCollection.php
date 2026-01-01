<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RequestResourceCollection extends ResourceCollection
{
    public $collects = RequestResource::class;

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'perPage' => $this->perPage(),
                'currentPage' => $this->currentPage(),
                'lastPage' => $this->lastPage(),
            ],
        ];
    }
}
