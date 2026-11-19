<?php

namespace App\Libs\DataBinds;

use App\Helper\Model;
use Carbon\Carbon;

trait HandleDataValue {

    use HandlesDataBoundValues;

    private function setData(string $name, ?Model $bind = null)
    {
        if ($bind) {
            $bind = $bind ?: $this->getBoundTarget();
        }

        $boundValue = $this->getBoundValue($bind, $name);
        $default = is_null($boundValue) ? '' : $boundValue;

        if($this->translate) {
            $default = __($default);
        }

        if($this->append) {
            if(is_array($this->append)) {
                $arr = [];
                foreach ($this->append as $field) {
                    $arr[] = $this->getBoundValue($bind, $field);
                }
                $seperator = $this->seperator ?? ' ';
                $default .= $seperator . implode($seperator, $arr);
            } elseif (is_string($this->append)) {
                $seperator = $this->seperator ?? ' ';
                $default .= $seperator . $this->append;
            }
        }

        if($this->fon) {
            $this->link = 'tel:' . $default;
        }

        if($this->email) {
            $emailValue = $this->getBoundValue($bind, $this->email === '1' ? $name : $this->email);
            $this->link = 'mailto:' . $emailValue;
        }

        if($this->dateformat) {
            if($default instanceof Carbon) {
                $default = $default->format($this->dateformat);
            } else {
                $default = $default ? Carbon::make($default)->format($this->dateformat) : null;
            }
        }

		if($default && $this->dateisoformat) {
			if($default instanceof Carbon) {
				$default = $default->isoFormat($this->dateisoformat);
			} else {
				$default = Carbon::make($default)->isoFormat($this->dateisoformat);
			}
		}

        $this->data =  old($name, $default);
    }
}
