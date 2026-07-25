<?php

$disableFrontend = [
    'fluidtypo3/flux/request-availability',
    'fluidtypo3/vhs/request-availability',
    'typo3/cms-frontend/authentication',
    'typo3/cms-frontend/eid',
    'typo3/cms-core/request-token-middleware',
    'typo3/cms-frontend/maintenance-mode',
    'typo3/cms-frontend/preview-simulator',
    'typo3/cms-frontend/base-redirect-resolver',
    'typo3/cms-frontend/csp-report',
    'typo3/cms-frontend/page-argument-validator',
    'typo3/cms-frontend/output-compression',
    'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
    'typo3/cms-frontend/csp-headers',
    'typo3/cms-core/cache-timeout',
    'typo3/cms-frontend/timetracker',
    'typo3/cms-core/normalized-params-attribute',
    'typo3/cms-frontend/backend-user-authentication',
    'typo3/cms-frontend/static-route-resolver',
    'typo3/cms-frontend/content-length-headers',
    'typo3/cms-core/response-propagation',
];

$disableBackend = [
    'typo3/cms-backend/locked-backend',
    'typo3/cms-backend/https-redirector',
    'typo3/cms-backend/csp-report',
    'typo3/cms-backend/csp-headers',
    'typo3/cms-backend/output-compression',
];

return [
    'frontend' => array_fill(0, count($disableFrontend), ['disabled' => true]),
    'backend' => array_fill(0, count($disableBackend), ['disabled' => true]),
];
