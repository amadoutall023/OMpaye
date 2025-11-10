<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'solde' => $this->solde,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'telephone' => $this->user->telephone,
            ]
        ];
    }
}
