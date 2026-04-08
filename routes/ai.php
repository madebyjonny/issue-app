<?php

use App\Mcp\Servers\Issues;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', Issues::class)
    ->middleware('auth:api');
