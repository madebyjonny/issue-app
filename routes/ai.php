<?php

use App\Mcp\Servers\Issues;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', Issues::class);
