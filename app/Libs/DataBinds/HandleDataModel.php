<?php

namespace App\Libs\DataBinds;

trait HandleDataModel {

    use HandlesDataBoundValues;

    private function setModelInstance($bind = null)
    {
        if ($bind) {
            $this->modelInstace = $bind ?: $this->getBoundTarget();
        }
    }
}
