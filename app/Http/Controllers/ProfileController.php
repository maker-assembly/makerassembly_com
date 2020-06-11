<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['edit', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Profile::all();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user->load('profile');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'preferred_name' => ['string', 'nullable'],
            'first_name' => ['string', 'nullable'],
            'last_name' => ['string', 'nullable'],
            'bio' => ['string', 'nullable'],
        ]);

        $user->profile->update([
            'preferred_name' => $request->preferred_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'bio' => $request->bio,
        ]);
    }
}
