
@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')



<div class="row">
                        
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Add Category
                                    </div>
                                    
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row mb-3">
                                            <label for="inputEmail1" class="col-sm-2 col-form-label">Category Name *</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    
                                                    <input type="email" class="form-control" id="inputEmail1">
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </form>
                                </div>
                                
                            </div>
                        </div>
						<div class="col-xl-6">
    <div class="card">
        <div class="card-header justify-content-between d-flex">
            <div class="card-title mb-0">
                Category List
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Electronics</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" title="Edit">
                                ✏️
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Fashion</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" title="Edit">
                                ✏️
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Books</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" title="Edit">
                                ✏️
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                🗑️
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

                    </div>

                    @endsection