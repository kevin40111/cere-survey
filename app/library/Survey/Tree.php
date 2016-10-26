<?php

namespace Plat\Survey;

trait Tree {

    public function getPaths()
    {
        return [$this->book];
    }

}
