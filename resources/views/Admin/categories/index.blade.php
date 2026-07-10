@extends('layouts.admin')
@section('content')
<div class="side-app">
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title mb-0">Category List</h3>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-icon text-white me-2">
                            <span>
                                <i class="fe fe-plus"></i>
                            </span> Add Category    
                        </a>
                        
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3 d-flex justify-content-end">
                        <div class="input-group" style="max-width: 350px;">
                            <input type="text" name="search" class="form-control" 
                                placeholder="Search By Category | Name..." value="{{ $search }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table border text-nowrap text-md-nowrap table-bordered mg-b-0">
                            <thead class="border-top">
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categories as $category)
                                <tr>

                                <td>{{ $categories->firstItem() + $loop->index }}</td>

                                <td>{{ $category->name }}</td>

                                <td>{{ $category->description }}</td>

                                <td>

                                   <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm rounded-11 me-2" data-bs-toggle="tooltip" data-bs-original-title="Edit"><i><svg class="table-edit" xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="16"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM5.92 19H5v-.92l9.06-9.06.92.92L5.92 19zM20.71 5.63l-2.34-2.34c-.2-.2-.45-.29-.71-.29s-.51.1-.7.29l-1.83 1.83 3.75 3.75 1.83-1.83c.39-.39.39-1.02 0-1.41z"/></svg></i></a>
                                      <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline delete-form" data-type="Menu Item">
                                         @csrf
                                         @method('DELETE')
                                         <button type="submit" class="btn btn-danger btn-sm rounded-11" data-bs-toggle="tooltip" data-bs-original-title="Delete"><i><svg class="table-delete" xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="16"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-4.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/></svg></i></button>
                                        </form>                                

                                </td>

                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if($categories->hasPages())
                            <div class="mt-3">
                                {{ $categories->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection