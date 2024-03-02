<?php

namespace App\Http\Controllers;

use App\Http\Resources\CallerResource;
use App\Models\Caller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexCallersController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        // We can implement paginate but mostly callers will be 3-5 items
        $callers = Caller::all();

        return CallerResource::collection($callers);
    }
}
