<?php

arch()->preset()->php();
arch('tests')
    ->expect('App\Tests\UseCase')
    ->toHaveSuffix('Test'); // To be sure it's executed


arch('Domain Entity extend to Entity')
    ->expect('App\Domain\Entity')
    ->toBeInterface()
    ->toExtend('App\Domain\Entity\EntityInterface')
    ->ignoring('App\Domain\Entity\EntityInterface')
    ->ignoring('App\Domain\Entity\FileInterface')
;