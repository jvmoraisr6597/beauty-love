<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Clients extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            "last_name" => $this->last_name,
            "user_name" => $this->user_name,
            "phrase" => $this->phrase,
            "birth_date" => $this->birth_date,
            "gender" => $this->gender,
            "state" => $this->state,
            "city" => $this->city,
            "newsletter_email" => $this->newsletter_email,
            "terms_and_conditions" => $this->terms_and_conditions,
            "image" => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
