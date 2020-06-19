<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\Category;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Category $category, Thread $thread)
    {
        $this->validateAttributes();

        $reply = $thread->replies()->create([
            'owner_id' => auth()->id(),
            'body' => request('body'),
        ]);

        return redirect($reply->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category, Thread $thread, Reply $reply)
    {
        return $reply;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category, Thread $thread, Reply $reply)
    {
        $this->authorize('update', $reply);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @param  \App\Models\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category, Thread $thread, Reply $reply)
    {
        $this->authorize('update', $reply);

        $this->validateAttributes();

        $reply->update([
            'body' => request('body'),
        ]);

        return redirect($reply->path());
    }

    /**
     * Soft delete the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $reply = Reply::where('id', $id)
            ->first();

        $this->authorize('delete', $reply);

        $reply->delete();
    }

    /**
     * Restore the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $reply = Reply::onlyTrashed()
            ->where('id', $id)
            ->first();

        $this->authorize('restore', $reply);

        $reply->restore();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reply = Reply::withTrashed()
            ->where('id', $id)
            ->first();
        $this->authorize('forceDelete', $reply);

        $reply->forceDelete();
    }

    /**
     * Validates the attributes given against requirements
     *
     * @return void
     */
    public function validateAttributes()
    {
        request()->validate([
            'body' => ['required', 'string']
        ]);
    }
}
