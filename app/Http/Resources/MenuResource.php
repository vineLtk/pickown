<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * @property mixed id
 * @property mixed key
 * @property mixed name
 */
class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
        ];
    }

    public function with($request)
    {
        return ['code' => Response::HTTP_OK, 'message' => ''];
    }
}
