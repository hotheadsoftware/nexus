<?php

/**
 * This ensures that the list of colors we receive from the ColorManager
 * class doesn't break our custom branding scheme.
 *
 * If this test sequence fails, it means that the ColorManager class
 * has been updated and we need to update our custom branding scheme
 * to match.
 */

use App\Services\Colors;

it('has colors that are compatible with custom branding', function () {

    $colors = new Colors();

    $colorManagerConditions   = $colors->getColorConditions()->sort();
    $customBrandingConditions = [
        'danger',
        'gray',
        'info',
        'primary',
        'success',
        'warning',
    ];

    expect($customBrandingConditions)->toBe($colorManagerConditions->toArray());
});
