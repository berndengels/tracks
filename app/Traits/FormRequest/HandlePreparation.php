<?php

namespace App\Traits\FormRequest;

trait HandlePreparation
{
	protected $floats;

	protected function handleFloats()
	{
		if($this->floats && count($this->floats) > 0) {
			foreach($this->floats as $item) {
				$this->$item = str_replace(',', '.', $item);
			}
		}
	}
}