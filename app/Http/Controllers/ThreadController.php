<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Filters\ThreadFilter;

class ThreadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, ThreadFilter $filter)
    {
        return $this->getThreads($category, $filter);
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateAttributes();

        $thread = Thread::create([
            'owner_id' => auth()->user()->id,
            'category_id' => request('category_id'),
            'title' => request('title'),
            'slug' => request('slug'),
            'body' => request('body')
        ]);

        return redirect($thread->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category, Thread $thread)
    {
        return $thread->load('replies');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category, Thread $thread)
    {
        $this->authorize('update', $thread);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category, Thread $thread)
    {
        $this->authorize('update', $thread);

        $this->validateAttributes();

        $thread->update([
            'category_id' => request('category_id'),
            'title' => request('title'),
            'slug' => request('slug'),
            'body' => request('body')
        ]);

        return redirect($thread->path());
    }

    /**
     * Soft delete the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $thread = Thread::where('id', $id)
            ->first();

        $this->authorize('delete', $thread);

        $thread->delete();
    }

    /**
     * Restore the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $thread = Thread::onlyTrashed()
            ->where('id', $id)
            ->first();

        $this->authorize('restore', $thread);

        $thread->restore();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $thread = Thread::withTrashed()
            ->where('id', $id)
            ->first();

        $this->authorize('forceDelete', $thread);

        $thread->forceDelete();
    }

    /**
     * Validates the attributes given against requirements
     *
     * @return void
     */
    public function validateAttributes()
    {
        request()->validate([
            'category_id' => ['required', 'integer', 'exists:App\Models\Category,id'],
            'title' => ['required', 'string'],
            'slug' => ['required', 'alpha_dash'],
            'body' => ['required', 'string']
        ]);
    }

    protected function getThreads(Category $category, ThreadFilter $filter)
    {
        $threads = Thread::latest()->filter($filter);

        if ($category->exists) {
            $threads->where('category_id', $category->id);
        }

        return $threads->paginate(25);
    }
}
