<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InputRequest;
use App\Http\Resources\Book as ResourcesBook;
use App\Models\Author;
use App\Models\Book as ModelsBook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Book extends BaseController
{
    //new authorcontroller
    //list of all authors info
    //search authors

    //new route search using wildcard Title Or Author Or Year published
    public function search($titleOrAuthor)
    {
        //using orwherehas to query using eagerloading
        $book = ModelsBook::with('authors')->where('title', 'like', '%'.$titleOrAuthor.'%')
                                            ->orWhere('year_published', $titleOrAuthor)
                                            ->orWhereHas('authors', function(Builder $query) use ($titleOrAuthor) {
                                                $query->where('name', 'like', '%'.$titleOrAuthor.'%');
                                            })->get();

        if($book->isEmpty()){
            return $this->sendError("BOOK NOT EXIST");
        }
        return $this->sendResponse(ResourcesBook::collection($book), "FETCH ALL LIKE BOOKS BY TITLE OR BY AUTHOR SUCCESS");
    }

    //new route search using wildcard title
    public function title($title)
    {
        $book = ModelsBook::with('authors')->where('title', 'like', '%'.$title.'%')->get();
        if($book->isEmpty()){
            return $this->sendError("BOOK NOT EXIST");
        }
        return $this->sendResponse(ResourcesBook::collection($book), "FETCH ALL LIKE BOOKS BY TITLE SUCCESS");
    }

    //new route search using wildcard author
    public function author($author){
        //using wherehas to query using eagerloading
        //sample of a query on the secondary table on the relationship many to many
        $book = ModelsBook::with('authors')->whereHas('authors', function(Builder $query) use ($author) {
            $query->where('name', 'like', '%'.$author.'%');
        })->get();

        if($book->isEmpty()){
            return $this->sendError("AUTHOR NOT EXIST");
        }
  
        return $this->sendResponse(ResourcesBook::collection($book), "FETCH ALL LIKE BOOKS BY AUTHOR SUCCESS");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //eager loading using with()
        $book = ModelsBook::with('authors')->get();

        return $this->sendResponse(ResourcesBook::collection($book), "FETCH ALL BOOKS SUCCESS");
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
    public function store(InputRequest $request)
    {
        $validated = $request->validated();
        $book = ModelsBook::create($validated);
        $author_id = [];
        foreach($validated['authors'] as $author){
            $author_id[]= Author::updateOrCreate($author)->id;
        }
        $book->authors()->attach($author_id);

        return $this->sendResponse(new ResourcesBook($book), "BOOK INSERTED SUCCESSFULLY");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(ctype_digit($id)){
            $book = ModelsBook::with('authors')->find($id);
        }else{
            $book = ModelsBook::with('authors')->where('title',$id)->first();
        }

        if($book){
            return $this->sendResponse(new ResourcesBook($book), "BOOK FETCH");
        }

        return $this->sendError("No Book found");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $book = ModelsBook::find($id);
        if(!$book){
            return $this->sendError("BOOK DOES NOT EXIST");
        }
        // $validated = $request->validated();
        $book->update($request->all());
        $author_id = [];
        if($request->has('authors')){
            foreach($request['authors'] as $author){
                $author_id[] = Author::updateOrCreate($author)->id;
            }
            $book->authors()->sync($author_id);
        }
        
        return $this->sendResponse(new ResourcesBook($book), "BOOK SUCCESSFULLY UPDATED");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
