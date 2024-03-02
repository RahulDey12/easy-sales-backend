<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organization\UpdateRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function show(): OrganizationResource
    {
        return new OrganizationResource(Auth::user()->organization);
    }

    public function update(UpdateRequest $request): OrganizationResource
    {
        $organization = Organization::updateOrCreate(
            ['user_id' => Auth::user()->getAuthIdentifier()],
            $request->only(['name', 'description']),
        );

        return new OrganizationResource($organization);
    }
}
